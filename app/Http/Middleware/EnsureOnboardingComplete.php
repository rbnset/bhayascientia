<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya untuk guest
        if (! auth()->check()) {

            $shouldRedirect =
                ! session()->has('has_seen_onboarding') &&
                ! $request->routeIs('onboarding.*') &&
                ! $request->routeIs('login') &&
                ! $request->routeIs('login.post') &&
                ! $request->routeIs('register') &&
                ! $request->routeIs('register.post') &&
                ! $request->routeIs('auth.*') &&
                ! $request->routeIs('placeholder.*') &&
                ! $request->is('_ignition/*') &&
                ! $request->is('livewire/*');

            if ($shouldRedirect) {
                session(['onboarding_intended' => $request->fullUrl()]);
                return redirect()->route('onboarding.show');
            }
        }

        return $next($request);
    }
}
