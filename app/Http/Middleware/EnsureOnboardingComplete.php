<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Skip semua route yang tidak perlu dicek
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        // ✅ USER SUDAH LOGIN → cek database
        if (auth()->check()) {
            if (! auth()->user()->has_seen_onboarding) {
                session(['onboarding_intended' => $request->fullUrl()]);
                return redirect()->route('onboarding.show');
            }

            // ✅ Selalu sync session saat user login & sudah seen onboarding
            // Ini kunci utama: saat logout nanti, session sudah punya
            // has_seen_onboarding = true SEBELUM session di-invalidate
            // Tapi karena invalidate menghapus semua, kita set ulang di logout()
            if (! session()->has('has_seen_onboarding')) {
                session(['has_seen_onboarding' => true]);
            }

            return $next($request);
        }

        // ✅ GUEST → cek session
        if (! session()->has('has_seen_onboarding')) {
            session(['onboarding_intended' => $request->fullUrl()]);
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }

    private function shouldSkip(Request $request): bool
    {
        return $request->routeIs('onboarding.*')
            || $request->routeIs('login')
            || $request->routeIs('login.post')
            || $request->routeIs('register')
            || $request->routeIs('register.post')
            || $request->routeIs('logout')
            || $request->routeIs('auth.*')
            || $request->routeIs('otp.*')
            || $request->routeIs('password.*')
            || $request->routeIs('placeholder.*')
            || $request->is('_ignition/*')
            || $request->is('livewire/*')
            || $request->is('api/*');
    }
}
