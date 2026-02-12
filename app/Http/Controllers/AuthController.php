<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Exception; // ✅ TAMBAHKAN INI
use Laravel\Socialite\Socialite;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('publikasi.library');
        }

        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('publikasi.library'))
                ->with('success', 'Selamat datang kembali, ' . Auth::user()->name . '! 👋');
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah. Silakan coba lagi.'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('publikasi.library');
        }

        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 3 karakter.',
            'name.max' => 'Nama tidak boleh lebih dari 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar. Silakan gunakan email lain atau login.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui Terms & Privacy Policy untuk melanjutkan.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('name', 'email'));
        }

        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'provider' => 'manual',
        ]);

        try {
            $user->assignRole('Author');
        } catch (Exception $e) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Author']);
            $user->assignRole('Author');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('publikasi.library')
            ->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $user->name . '! 🎉');
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Anda telah logout. Sampai jumpa! 👋');
    }

    // ========================================
    // ✅ SOCIAL LOGIN METHODS
    // ========================================

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gagal menghubungkan ke Google. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Handle Google OAuth Callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()->intended(route('publikasi.library'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '! 👋');
            }

            $existingUser = User::where('email', $googleUser->email)->first();

            if ($existingUser) {
                $existingUser->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'provider' => 'google',
                ]);

                Auth::login($existingUser, true);
                $request->session()->regenerate();

                return redirect()->intended(route('publikasi.library'))
                    ->with('success', 'Akun Google berhasil ditautkan! Selamat datang, ' . $existingUser->name . '! 👋');
            }

            $newUser = User::create([
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_id' => $googleUser->id,
                'avatar' => $googleUser->avatar,
                'provider' => 'google',
                'email_verified_at' => now(),
                'password' => null,
            ]);

            try {
                $newUser->assignRole('Author');
            } catch (Exception $e) {
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Author']);
                $newUser->assignRole('Author');
            }

            Auth::login($newUser, true);
            $request->session()->regenerate();

            return redirect()->intended(route('publikasi.library'))
                ->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $newUser->name . '! 🎉');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'google' => 'Gagal login dengan Google: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        try {
            return Socialite::driver('facebook')->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'facebook' => 'Gagal menghubungkan ke Facebook. Silakan coba lagi.'
            ]);
        }
    }

    /**
     * Handle Facebook OAuth Callback
     */
    public function handleFacebookCallback(Request $request)
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();

            $user = User::where('facebook_id', $facebookUser->id)->first();

            if ($user) {
                Auth::login($user, true);
                $request->session()->regenerate();

                return redirect()->intended(route('publikasi.library'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . '! 👋');
            }

            $existingUser = User::where('email', $facebookUser->email)->first();

            if ($existingUser) {
                $existingUser->update([
                    'facebook_id' => $facebookUser->id,
                    'avatar' => $facebookUser->avatar,
                    'provider' => 'facebook',
                ]);

                Auth::login($existingUser, true);
                $request->session()->regenerate();

                return redirect()->intended(route('publikasi.library'))
                    ->with('success', 'Akun Facebook berhasil ditautkan! Selamat datang, ' . $existingUser->name . '! 👋');
            }

            $newUser = User::create([
                'name' => $facebookUser->name,
                'email' => $facebookUser->email,
                'facebook_id' => $facebookUser->id,
                'avatar' => $facebookUser->avatar,
                'provider' => 'facebook',
                'email_verified_at' => now(),
                'password' => null,
            ]);

            try {
                $newUser->assignRole('Author');
            } catch (Exception $e) {
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Author']);
                $newUser->assignRole('Author');
            }

            Auth::login($newUser, true);
            $request->session()->regenerate();

            return redirect()->intended(route('publikasi.library'))
                ->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $newUser->name . '! 🎉');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'facebook' => 'Gagal login dengan Facebook: ' . $e->getMessage()
            ]);
        }
    }
}
