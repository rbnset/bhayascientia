<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Route yang diizinkan meski belum verified
     */
    protected array $except = [
        'otp.*',
        'logout',
        'home',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Tidak login → ke halaman login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Sudah verified → lanjut normal
        if ($request->user()->isEmailVerified()) {
            return $next($request);
        }

        // Kalau route ada di daftar pengecualian → biarkan lewat
        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Belum verified → redirect ke OTP
        return redirect()->route('otp.show')
            ->with('info', '📧 Verifikasi email Anda terlebih dahulu untuk mengakses halaman ini.');
    }
}
