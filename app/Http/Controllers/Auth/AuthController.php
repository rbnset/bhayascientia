<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // =========================================================================
    // REGISTER
    // =========================================================================

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(6)],
            'terms'    => ['accepted'],
        ], [
            'name.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 6 karakter.',
            'terms.accepted'     => 'Anda harus menyetujui Terms & Privacy Policy.',
        ]);

        $user = User::create([
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'password'            => Hash::make($validated['password']),
            'provider'            => 'manual',
            'email_verified_at'   => null,
            'has_seen_onboarding' => false, // ✅ eksplisit false — user baru wajib lihat onboarding
        ]);

        $user->assignRole('Author');

        Auth::login($user);

        $otp = $user->generateOtp();

        try {
            Mail::to($user->email)->send(new OtpVerificationMail(
                otpCode: $otp,
                userName: $user->name,
                userEmail: $user->email,
            ));
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP saat register', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // ✅ Setelah register → OTP dulu, bukan onboarding langsung
        // Onboarding akan muncul SETELAH OTP verified karena middleware akan redirect
        return redirect()->route('otp.show')
            ->with('info', '📧 Kode verifikasi telah dikirim ke ' . $user->email . '. Silakan cek inbox Anda.');
    }

    // =========================================================================
    // LOGIN
    // =========================================================================

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Cek OTP
            if (! $user->isEmailVerified()) {
                $otp = $user->generateOtp();

                try {
                    Mail::to($user->email)->send(new OtpVerificationMail(
                        otpCode: $otp,
                        userName: $user->name,
                        userEmail: $user->email,
                    ));
                } catch (\Exception $e) {
                    Log::error('Gagal kirim OTP saat login', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }

                return redirect()->route('otp.show')
                    ->with('info', '📧 Akun Anda belum diverifikasi. Kode OTP baru telah dikirim ke ' . $user->email);
            }

            // ✅ Cek onboarding — user lama yang belum pernah lihat onboarding
            // (misal: existing user sebelum fitur onboarding ada)
            if (! $user->has_seen_onboarding) {
                // Simpan intended redirect jika ada
                $redirectTo = $request->input('_redirect_to') ?? $request->query('redirect') ?? null;
                if ($redirectTo) {
                    session(['onboarding_intended' => urldecode(urldecode($redirectTo))]);
                }

                return redirect()->route('onboarding.show');
            }

            // ✅ Baca intended redirect (untuk after_login flow dari show.blade.php)
            $redirectTo = $request->input('_redirect_to')
                ?? $request->query('redirect')
                ?? null;

            if ($redirectTo) {
                $decoded    = urldecode(urldecode($redirectTo));
                $parsedHost = parse_url($decoded, PHP_URL_HOST);
                $appHost    = parse_url(config('app.url'), PHP_URL_HOST);

                if (! $parsedHost || $parsedHost !== $appHost) {
                    $redirectTo = null;
                } else {
                    $redirectTo = $decoded;
                }
            }

            return redirect($redirectTo ?? route('publikasi.library'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    // =========================================================================
    // LOGOUT
    // =========================================================================

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ✅ Set has_seen_onboarding ke session SETELAH invalidate
        // agar guest (setelah logout) tidak diarahkan ke onboarding lagi
        // User yang logout sudah pasti pernah selesai onboarding
        session(['has_seen_onboarding' => true]);

        return redirect()->route('home')
            ->with('success', 'Anda telah logout.');
    }
}
