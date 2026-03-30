<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class OrcidController extends Controller
{
    // ══════════════════════════════════════════
    // LOGIN / REGISTER VIA ORCID
    // ══════════════════════════════════════════

    /**
     * Redirect ke ORCID untuk login/register
     */
    public function redirectForLogin()
    {
        if (request()->query('redirect')) {
            Session::put('oauth_redirect', urldecode(request()->query('redirect')));
        }

        Session::put('orcid_mode', 'login');

        // ✅ Paksa pakai production ORCID
        config(['services.orcid.base_uri' => 'https://orcid.org']);

        return Socialite::driver('orcid')
            ->scopes(['/authenticate'])
            ->redirect();
    }

    /**
     * Handle callback ORCID untuk login/register
     */
    public function handleLoginCallback()
    {
        try {
            $orcidUser = Socialite::driver('orcid')->user();

            $orcidId   = $orcidUser->getId();
            $orcidName = $orcidUser->getName();

            // Cari user berdasarkan ORCID iD
            $user      = User::where('orcid_id', $orcidId)->first();
            $isNewUser = false;

            if ($user) {
                // User lama → perbarui verifikasi
                $user->update(['orcid_verified_at' => now()]);
            } else {
                // User baru via ORCID → email belum ada, minta nanti
                $user = User::create([
                    'name'                    => $orcidName ?? 'Peneliti',
                    'email'                   => $this->generateTempEmail($orcidId),
                    'orcid_id'                => $orcidId,
                    'orcid_verified_at'       => now(),
                    'provider'                => 'orcid',
                    'email_verified_at'       => null,
                    'password'                => Hash::make(Str::random(32)),
                    'has_seen_onboarding'     => false,
                    'needs_email_completion'  => true,  // ← wajib isi email dulu
                ]);

                $user->assignRole('Author');
                $isNewUser = true;
            }

            Auth::login($user, true);

            // User baru → minta lengkapi email dulu sebelum onboarding
            if ($isNewUser) {
                return redirect()->route('orcid.complete-email.show')
                    ->with('info', 'Satu langkah lagi! Masukkan email Anda untuk melanjutkan.');
            }

            // User lama tapi email belum dilengkapi
            if ($user->needs_email_completion) {
                return redirect()->route('orcid.complete-email.show');
            }

            // User lama yang belum onboarding
            if (!$user->has_seen_onboarding) {
                $redirectTo = Session::pull('oauth_redirect');
                if ($redirectTo) {
                    session(['onboarding_intended' => $redirectTo]);
                }
                return redirect()->route('onboarding.show')
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
            }

            $redirectTo = Session::pull('oauth_redirect');
            return redirect($redirectTo ?? route('home'))
                ->with('success', 'Berhasil login dengan ORCID! Selamat datang, ' . $user->name);
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Gagal login dengan ORCID. Silakan coba lagi.']);
        }
    }

    // ══════════════════════════════════════════
    // EMAIL COMPLETION — Setelah login ORCID
    // ══════════════════════════════════════════

    /**
     * Tampilkan form minta email
     */
    public function showCompleteEmail()
    {
        abort_unless(
            auth()->check() && auth()->user()->needs_email_completion,
            403
        );

        return view('auth.orcid-complete-email');
    }

    /**
     * Simpan email yang diisi user
     */
    public function completeEmail(Request $request)
    {
        abort_unless(
            auth()->check() && auth()->user()->needs_email_completion,
            403
        );

        $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                // Pastikan email belum dipakai user lain
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->ignore(auth()->id()),
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email ini sudah digunakan akun lain.',
        ]);

        $user = auth()->user();

        $user->update([
            'email'                  => $request->email,
            'needs_email_completion' => false,
            // Email belum verified → arahkan ke OTP
            'email_verified_at'      => null,
        ]);

        // Redirect ke onboarding jika belum, atau ke OTP verify
        if (!$user->has_seen_onboarding) {
            return redirect()->route('onboarding.show')
                ->with('success', 'Email berhasil disimpan! Selamat datang, ' . $user->name . '! 🎉');
        }

        return redirect()->route('otp.show')
            ->with('info', 'Silakan verifikasi email Anda.');
    }

    // ══════════════════════════════════════════
    // CONNECT / DISCONNECT — Dari halaman profil
    // ══════════════════════════════════════════

    /**
     * Redirect ke ORCID untuk verifikasi dari halaman profil
     */
    public function redirectForConnect()
    {
        Session::put('orcid_mode', 'connect');

        return Socialite::driver('orcid')
            ->scopes(['/authenticate'])
            ->redirect();
    }

    /**
     * Handle callback ORCID untuk connect dari profil
     */
    public function handleConnectCallback()
    {
        try {
            $orcidUser = Socialite::driver('orcid')->user();
            $orcidId   = $orcidUser->getId();

            $user = Auth::user();

            // Cek konflik dengan user lain
            $conflict = User::where('orcid_id', $orcidId)
                ->where('id', '!=', $user->id)
                ->first();

            if ($conflict) {
                return redirect()->route('profil.saya')
                    ->withErrors(['orcid' => 'ORCID iD ini sudah terhubung ke akun lain.']);
            }

            $user->update([
                'orcid_id'          => $orcidId,
                'orcid_verified_at' => now(),
            ]);

            // Sync author profile — null agar accessor baca dari user
            $user->authorProfile?->update(['orcid_id' => null]);

            return redirect()->route('profil.saya')
                ->with('success', 'ORCID iD berhasil diverifikasi! ✅');
        } catch (Exception $e) {
            return redirect()->route('profil.saya')
                ->withErrors(['orcid' => 'Gagal menghubungkan ORCID. Silakan coba lagi.']);
        }
    }

    /**
     * Lepas koneksi ORCID dari profil
     */
    public function disconnect()
    {
        $user = Auth::user();

        // Jangan boleh disconnect jika ORCID adalah satu-satunya cara login
        if ($user->provider === 'orcid' && !$user->google_id && !$user->hasPassword()) {
            return redirect()->route('profil.saya')
                ->withErrors(['orcid' => 'Tidak bisa melepas ORCID karena ini satu-satunya metode login Anda.']);
        }

        $user->update([
            'orcid_id'          => null,
            'orcid_verified_at' => null,
        ]);

        return redirect()->route('profil.saya')
            ->with('success', 'Koneksi ORCID berhasil dilepas.');
    }

    // ══════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════

    private function generateTempEmail(string $orcidId): string
    {
        $clean = str_replace('-', '', $orcidId);
        return "orcid-{$clean}@pending.dabraka.org";
    }
}
