<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    // =========================================================================
    // SHOW — Halaman verifikasi OTP
    // =========================================================================

    public function show(Request $request)
    {
        if ($request->user()->isEmailVerified()) {
            return redirect()->route('publikasi.library');
        }

        return view('auth.otp-verify');
    }

    // =========================================================================
    // VERIFY — Proses verifikasi kode OTP
    // =========================================================================

    public function verify(Request $request)
    {
        $user       = $request->user();
        $attemptKey = 'otp_attempts_' . $user->id;
        $attempts   = Cache::get($attemptKey, 0);

        // Cek sudah verified
        if ($user->isEmailVerified()) {
            return redirect()->route('publikasi.library');
        }

        // Cek max attempt (5x) sebelum validasi — cegah bypass via invalid input
        if ($attempts >= 5) {
            Cache::forget($attemptKey);
            Log::warning('OTP max attempts reached', ['user_id' => $user->id, 'ip' => $request->ip()]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Terlalu banyak percobaan OTP. Silakan login ulang.']);
        }

        // Validasi input
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Kode OTP wajib diisi.',
            'code.digits'   => 'Kode OTP harus 6 digit angka.',
        ]);

        // Verifikasi OTP
        if (!$user->verifyOtp($request->code)) {
            $newAttempts = $attempts + 1;
            Cache::put($attemptKey, $newAttempts, now()->addMinutes(10));

            $remaining = 5 - $newAttempts;

            if ($remaining <= 0) {
                Cache::forget($attemptKey);
                Log::warning('OTP attempts exhausted', ['user_id' => $user->id, 'ip' => $request->ip()]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Terlalu banyak percobaan OTP. Silakan login ulang.']);
            }

            return back()->withErrors([
                'code' => "Kode OTP salah atau sudah kadaluarsa. Sisa percobaan: {$remaining}x.",
            ]);
        }

        // Berhasil — bersihkan semua cache OTP
        Cache::forget($attemptKey);
        Cache::forget('otp_resend_count_' . $user->id);
        Cache::forget('otp_cooldown_' . $user->id);

        // Set email verified
        $user->update(['email_verified_at' => now()]);

        Log::info('Email verified successfully', ['user_id' => $user->id]);

        return redirect()->route('publikasi.library')
            ->with('success', 'Email berhasil diverifikasi! Selamat datang, ' . $user->name . ' 🎉');
    }

    // =========================================================================
    // RESEND — Kirim ulang OTP
    // =========================================================================

    public function resend(Request $request)
    {
        $user        = $request->user();
        $cooldownKey = 'otp_cooldown_' . $user->id;
        $resendKey   = 'otp_resend_count_' . $user->id;

        // Cek sudah verified
        if ($user->isEmailVerified()) {
            return redirect()->route('publikasi.library');
        }

        // Cek cooldown server-side (60 detik)
        if (Cache::has($cooldownKey)) {
            return back()->withErrors([
                'resend' => 'Tunggu 60 detik sebelum kirim ulang kode.',
            ]);
        }

        // Cek max resend (3x per 10 menit)
        $resendCount = Cache::get($resendKey, 0);

        if ($resendCount >= 3) {
            Log::warning('OTP resend limit reached', ['user_id' => $user->id, 'ip' => $request->ip()]);

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Batas kirim ulang OTP habis. Silakan login ulang.']);
        }

        // Generate & kirim OTP baru
        $otp = $user->generateOtp();

        try {
            Mail::to($user->email)->send(new OtpVerificationMail(
                otpCode: $otp,
                userName: $user->name,
                userEmail: $user->email,
            ));

            // Set cooldown & increment counter
            Cache::put($cooldownKey, true, now()->addSeconds(60));
            Cache::put($resendKey, $resendCount + 1, now()->addMinutes(10));

            Log::info('OTP resent', ['user_id' => $user->id, 'attempt' => $resendCount + 1]);

            return back()->with('success', 'Kode OTP baru telah dikirim ke ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Gagal kirim OTP resend', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return back()->withErrors([
                'resend' => 'Gagal mengirim email. Coba beberapa saat lagi.',
            ]);
        }
    }
}
