<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    /**
     * Redirect user ke Google OAuth page
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle callback dari Google
     */
    public function handleGoogleCallback()
    {
        try {
            // Ambil data user dari Google
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah user dengan email ini sudah ada
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User sudah ada, update data Google jika belum ada
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar,
                        'provider' => 'google',
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);
                }
            } else {
                // Buat user baru
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'provider' => 'google',
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(24)), // Random password
                ]);

                // ✅ Assign role "Author" otomatis untuk registrasi via Google
                $user->assignRole('Author');
            }

            // Login user
            Auth::login($user, true);

            // Redirect ke halaman yang sesuai
            return redirect()->intended(route('home'))
                ->with('success', 'Berhasil login dengan Google! Selamat datang, ' . $user->name);
        } catch (Exception $e) {
            // Handle error
            return redirect()->route('login')
                ->withErrors(['error' => 'Gagal login dengan Google. Silakan coba lagi.']);
        }
    }
}
