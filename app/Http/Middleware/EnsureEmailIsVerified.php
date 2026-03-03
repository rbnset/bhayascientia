<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if (!$request->user()->isEmailVerified()) {
            // Kalau sudah di halaman OTP, biarkan lewat
            if ($request->routeIs('otp.*')) {
                return $next($request);
            }

            return redirect()->route('otp.show')
                ->with('info', 'Verifikasi email Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
