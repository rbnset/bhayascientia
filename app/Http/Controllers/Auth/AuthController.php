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
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 6 karakter.',
            'terms.accepted'    => 'Anda harus menyetujui Terms & Privacy Policy.',
        ]);

        // Buat user baru — email_verified_at null dulu (belum verified)
        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'password'           => Hash::make($validated['password']),
            'provider'           => 'manual',
            'email_verified_at'  => null, // ← wajib null, nanti diisi setelah OTP
        ]);

        // Assign role "Author" otomatis
        $user->assignRole('Author');

        // Login otomatis
        Auth::login($user);

        // Generate & kirim OTP
        $otp = $user->generateOtp();

        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp));
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP saat register', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // Redirect ke halaman verifikasi OTP
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

            // Cek apakah email sudah diverifikasi
            if (!$user->isEmailVerified()) {
                // Generate OTP baru & kirim ulang
                $otp = $user->generateOtp();

                try {
                    Mail::to($user->email)->send(new OtpVerificationMail($otp));
                } catch (\Exception $e) {
                    Log::error('Gagal kirim OTP saat login', [
                        'user_id' => $user->id,
                        'error'   => $e->getMessage(),
                    ]);
                }

                return redirect()->route('otp.show')
                    ->with('info', '📧 Akun Anda belum diverifikasi. Kode OTP baru telah dikirim ke ' . $user->email);
            }

            return redirect()->intended(route('publikasi.library'))
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

        return redirect()->route('home')
            ->with('success', 'Anda telah logout.');
    }
}
