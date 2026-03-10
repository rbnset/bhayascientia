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

        // ✅ Guest & sudah pernah lihat onboarding → langsung home
        if (! auth()->check() && session()->has('has_seen_onboarding')) {
            return redirect()->route('home');
        }

        return view('onboarding.index');
    }

    public function complete(Request $request): RedirectResponse
    {
        // ✅ User login → tandai permanen di database
        if (auth()->check()) {
            auth()->user()->update(['has_seen_onboarding' => true]);
        }

        // ✅ Selalu set session juga (fallback untuk guest)
        session(['has_seen_onboarding' => true]);

        // Ambil URL intended
        $intended = session()->pull('onboarding_intended', null);

        // Validasi: jangan redirect ke halaman auth
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

        return redirect($isSafe && $intended ? $intended : route('home'));
    }
}
