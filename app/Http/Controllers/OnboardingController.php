<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        // ✅ User login & sudah selesai onboarding → langsung home
        if (auth()->check() && auth()->user()->has_seen_onboarding) {
            return redirect()->route('home');
        }

        // ✅ Guest & sudah pernah lihat onboarding → cek cookie
        if (! auth()->check() && request()->cookie('has_seen_onboarding')) {
            return redirect()->route('home');
        }

        return view('onboarding.index');
    }

    public function complete(Request $request): RedirectResponse
    {
        // ✅ User login → simpan permanen ke database
        if (auth()->check()) {
            auth()->user()->update(['has_seen_onboarding' => true]);
        }

        // ✅ Set cookie untuk semua user (login maupun guest)
        // Sehingga setelah logout pun, onboarding tidak muncul lagi
        $cookie = cookie(
            name: 'has_seen_onboarding',
            value: '1',
            minutes: 60 * 24 * 365, // 1 tahun
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            sameSite: 'Lax',
        );

        // ✅ Ambil & validasi intended URL
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
                    // route tidak ada, skip
                }
            }
        }

        return redirect($isSafe && $intended ? $intended : route('home'))
            ->withCookie($cookie);
    }
}
