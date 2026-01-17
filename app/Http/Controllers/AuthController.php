<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email', 'remember'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Redirect ke intended URL atau library
            return redirect()->intended(route('publikasi.library'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah'])
            ->withInput($request->only('email', 'remember'));
    }

    /**
     * Show register form
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle register request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'terms' => 'accepted',
        ], [
            'firstName.required' => 'Nama depan wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'terms.accepted' => 'Anda harus menyetujui Terms & Privacy Policy',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('firstName', 'lastName', 'email'));
        }

        // Gabungkan firstName dan lastName
        $fullName = trim($request->firstName . ' ' . ($request->lastName ?? ''));

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ Assign default role "reader"
        $user->assignRole('reader');

        // Auto login after register
        Auth::login($user);

        return redirect()->route('publikasi.library')
            ->with('success', 'Registrasi berhasil! Selamat datang, ' . $user->name . '!');
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
            ->with('success', 'Anda telah logout');
    }
}
