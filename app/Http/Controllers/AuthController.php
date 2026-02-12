<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Redirect jika sudah login
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

            // Redirect ke intended URL atau library
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
        // Redirect jika sudah login
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

        // Create user
        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
        ]);

        // ✅ Assign role "Author" otomatis
        try {
            $user->assignRole('Author');
        } catch (\Exception $e) {
            // Fallback jika role belum ada, buat role Author
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Author']);
            $user->assignRole('Author');
        }

        // Auto login after register
        Auth::login($user);

        // Regenerate session untuk keamanan
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
}
