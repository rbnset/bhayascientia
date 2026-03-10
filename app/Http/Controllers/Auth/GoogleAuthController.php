<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user ke Google OAuth page
     * ✅ Simpan intended redirect ke session sebelum ke Google
     */
    public function redirectToGoogle()
    {
        // Simpan ?redirect= ke session agar tidak hilang saat OAuth roundtrip
        if (request()->query('redirect')) {
            Session::put('oauth_redirect', urldecode(request()->query('redirect')));
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update([
                        'google_id'          => $googleUser->id,
                        'avatar'             => $googleUser->avatar,
                        'provider'           => 'google',
                        'email_verified_at'  => $user->email_verified_at ?? now(),
                    ]);
                }
            } else {
                $user = User::create([
                    'name'               => $googleUser->name,
                    'email'              => $googleUser->email,
                    'google_id'          => $googleUser->id,
                    'avatar'             => $googleUser->avatar,
                    'provider'           => 'google',
                    'email_verified_at'  => now(),
                    'password'           => Hash::make(Str::random(24)),
                ]);

                $user->assignRole('Author');
            }

            Auth::login($user, true);

            // ✅ Ambil intended redirect dari session
            $redirectTo = Session::pull('oauth_redirect');

            // ✅ Validasi keamanan — hanya izinkan URL dari domain sendiri
            if ($redirectTo) {
                $parsedHost = parse_url($redirectTo, PHP_URL_HOST);
                $appHost    = parse_url(config('app.url'), PHP_URL_HOST);

                if (!$parsedHost || $parsedHost !== $appHost) {
                    $redirectTo = null;
                }
            }

            return redirect($redirectTo ?? route('publikasi.library'))
                ->with('success', 'Berhasil login dengan Google! Selamat datang, ' . $user->name);
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }

    /**
     * Redirect user ke Facebook OAuth page
     * ✅ Simpan intended redirect ke session sebelum ke Facebook
     */
    public function redirectToFacebook()
    {
        if (request()->query('redirect')) {
            Session::put('oauth_redirect', urldecode(request()->query('redirect')));
        }

        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle callback dari Facebook
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $user = User::where('email', $facebookUser->email)->first();

            if ($user) {
                if (!$user->facebook_id) {
                    $user->update([
                        'facebook_id'        => $facebookUser->id,
                        'avatar'             => $facebookUser->avatar,
                        'provider'           => 'facebook',
                        'email_verified_at'  => $user->email_verified_at ?? now(),
                    ]);
                }
            } else {
                $user = User::create([
                    'name'               => $facebookUser->name,
                    'email'              => $facebookUser->email,
                    'facebook_id'        => $facebookUser->id,
                    'avatar'             => $facebookUser->avatar,
                    'provider'           => 'facebook',
                    'email_verified_at'  => now(),
                    'password'           => Hash::make(Str::random(24)),
                ]);

                $user->assignRole('Author');
            }

            Auth::login($user, true);

            // ✅ Ambil intended redirect dari session
            $redirectTo = Session::pull('oauth_redirect');

            if ($redirectTo) {
                $parsedHost = parse_url($redirectTo, PHP_URL_HOST);
                $appHost    = parse_url(config('app.url'), PHP_URL_HOST);

                if (!$parsedHost || $parsedHost !== $appHost) {
                    $redirectTo = null;
                }
            }

            return redirect($redirectTo ?? route('publikasi.library'))
                ->with('success', 'Berhasil login dengan Facebook! Selamat datang, ' . $user->name);
        } catch (Exception $e) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Gagal login dengan Facebook. Silakan coba lagi.']);
        }
    }
}
