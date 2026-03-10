<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        // User login → cek database (tidak berubah)
        if (auth()->check() && auth()->user()->has_seen_onboarding) {
            return redirect()->route('home');
        }

        // Guest → cek cookie (lebih persisten dari session)
        if (! auth()->check() && request()->cookie('has_seen_onboarding')) {
            return redirect()->route('home');
        }

        return view('onboarding.index');
    }

    public function complete(Request $request): RedirectResponse
    {
        // User login → simpan ke database (tidak berubah)
        if (auth()->check()) {
            auth()->user()->update(['has_seen_onboarding' => true]);
        }

        // Selalu set session sebagai backup
        session(['has_seen_onboarding' => true]);

        // ✅ Tambahan: set cookie untuk guest agar persisten
        $cookie = cookie(
            name: 'has_seen_onboarding',
            value: '1',
            minutes: 60 * 24 * 365, // 1 tahun
            httpOnly: true,
            secure: $request->isSecure(),
            sameSite: 'Lax',
        );

        $intended   = session()->pull('onboarding_intended', null);
        $safeRoutes = ['login', 'register', 'otp.show', 'otp.verify'];
        $isSafe     = true;

        if ($intended) {
            foreach ($safeRoutes as $routeName) {
                try {
                    if ($intended === route($routeName)) {
                        $isSafe = false;
                        break;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return redirect($isSafe && $intended ? $intended : route('home'))
            ->withCookie($cookie); // ✅ kirim cookie ke browser
    }
}
