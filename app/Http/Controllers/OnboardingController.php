<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View|RedirectResponse
    {
        // Sudah pernah lihat onboarding → langsung ke home
        if (session()->has('has_seen_onboarding')) {
            return redirect()->route('home');
        }

        return view('onboarding.index');
    }

    public function complete(Request $request): RedirectResponse
    {
        // Tandai sudah selesai onboarding
        session(['has_seen_onboarding' => true]);

        // Ambil URL intended, default ke home
        // Pastikan tidak redirect ke route auth (login/register/otp)
        $intended = session()->pull('onboarding_intended', null);

        // Validasi: jangan redirect ke halaman auth
        $safeRoutes = ['login', 'register', 'otp.show', 'otp.verify'];
        $isSafe = true;

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
