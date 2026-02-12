<?php
// app/Http/Controllers/Auth/SocialAuthController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth Callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // User exists, login
                Auth::login($user, true);
                return redirect()->intended(route('home'))->with('success', 'Berhasil login dengan Google!');
            }

            // Check if email already exists (user registered manually)
            $existingUser = User::where('email', $googleUser->email)->first();

            if ($existingUser) {
                // Link Google account to existing user
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'provider' => 'google',
                ]);

                Auth::login($existingUser, true);
                return redirect()->intended(route('home'))->with('success', 'Akun Google berhasil ditautkan!');
            }

            // Create new user
            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'provider' => 'google',
                'email_verified_at' => now(), // Auto verify email for social login
                'password' => null, // No password for social login
            ]);

            Auth::login($newUser, true);

            return redirect()->intended(route('home'))->with('success', 'Akun berhasil dibuat dengan Google!');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gagal login dengan Google. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth Callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            // Check if user exists by facebook_id
            $user = User::where('facebook_id', $facebookUser->id)->first();

            if ($user) {
                Auth::login($user, true);
                return redirect()->intended(route('home'))->with('success', 'Berhasil login dengan Facebook!');
            }

            // Check if email already exists
            $existingUser = User::where('email', $facebookUser->email)->first();

            if ($existingUser) {
                $existingUser->update([
                    'facebook_id' => $facebookUser->id,
                    'avatar' => $facebookUser->avatar,
                    'provider' => 'facebook',
                ]);

                Auth::login($existingUser, true);
                return redirect()->intended(route('home'))->with('success', 'Akun Facebook berhasil ditautkan!');
            }

            // Create new user
            $newUser = User::create([
                'name' => $facebookUser->name,
                'email' => $facebookUser->email,
                'facebook_id' => $facebookUser->id,
                'avatar' => $facebookUser->avatar,
                'provider' => 'facebook',
                'email_verified_at' => now(),
                'password' => null,
            ]);

            Auth::login($newUser, true);

            return redirect()->intended(route('home'))->with('success', 'Akun berhasil dibuat dengan Facebook!');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'facebook' => 'Gagal login dengan Facebook. Silakan coba lagi.'
            ]);
        }
    }
}
