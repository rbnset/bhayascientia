<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    // Tampilkan halaman input OTP
    public function show()
    {
        if (Auth::user()->isEmailVerified()) {
            return redirect()->route('home');
        }

        return view('auth.otp-verify');
    }

    // Verifikasi kode OTP
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Masukkan kode OTP.',
            'code.size'     => 'Kode OTP harus 6 digit.',
        ]);

        $user = Auth::user();

        $otp = $user->otpCodes()
            ->where('code', $request->code)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'Kode OTP tidak valid.']);
        }

        if ($otp->isExpired()) {
            return back()->withErrors(['code' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.']);
        }

        // Tandai OTP sudah dipakai
        $otp->update(['is_used' => true]);

        // Verifikasi email user
        $user->update(['email_verified_at' => now()]);

        return redirect()->route('home')
            ->with('success', '🎉 Akun Anda berhasil diverifikasi! Selamat datang di DABRAKA.');
    }

    // Kirim ulang OTP
    public function resend()
    {
        $user = Auth::user();

        if ($user->isEmailVerified()) {
            return redirect()->route('home');
        }

        // Cek OTP terakhir
        $lastOtp = $user->otpCodes()
            ->where('is_used', false)
            ->latest()
            ->first();

        // Cek interval resend (60 detik)
        if ($lastOtp && $lastOtp->last_resend_at) {
            $secondsAgo = now()->diffInSeconds($lastOtp->last_resend_at);
            if ($secondsAgo < 60) {
                $waitSeconds = 60 - $secondsAgo;
                return back()->withErrors([
                    'code' => "Tunggu {$waitSeconds} detik sebelum meminta kode baru."
                ]);
            }
        }

        // Cek batas resend
        if ($lastOtp && $lastOtp->resend_count >= 3) {
            return back()->withErrors([
                'code' => 'Batas pengiriman ulang tercapai (3x). Silakan hubungi support.'
            ]);
        }

        // Generate OTP baru
        $otp = $user->generateOtp();

        // Update resend count dari OTP lama atau set ke OTP baru
        $otp->update([
            'resend_count'   => ($lastOtp?->resend_count ?? 0) + 1,
            'last_resend_at' => now(),
        ]);

        // Kirim email
        try {
            Mail::to($user->email)->send(new OtpVerificationMail($otp));
        } catch (\Exception $e) {
            return back()->withErrors([
                'code' => 'Gagal mengirim email. Coba lagi beberapa saat.'
            ]);
        }

        return back()->with('success', '✅ Kode OTP baru telah dikirim ke ' . $user->email);
    }
}
