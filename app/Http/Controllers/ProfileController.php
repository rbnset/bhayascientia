<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil user
     */
    public function index()
    {
        $user = Auth::user();

        // Load relationships dengan error handling
        try {
            $user->load(['authorProfile', 'publications', 'savedPublications', 'favoritePublications']);

            $publicationsCount = $user->publications()->count();
            $savedCount = $user->savedPublications()->count();
            $favoritesCount = $user->favoritePublications()->count();
        } catch (\Exception $e) {
            // Jika ada error saat load relationships, set default 0
            $publicationsCount = 0;
            $savedCount = 0;
            $favoritesCount = 0;
        }

        return view('profilesaya', [
            'user' => $user,
            'publicationsCount' => $publicationsCount,
            'savedCount' => $savedCount,
            'favoritesCount' => $favoritesCount,
        ]);
    }

    /**
     * Update profil user
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $user->update($validated);
            return redirect()->route('profil.saya')->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }

    /**
     * Update foto profil
     */
    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        try {
            // Hapus foto lama jika ada (hanya foto upload, bukan avatar social login)
            if ($user->profile_photo) {
                $cleanPath = str_starts_with($user->profile_photo, 'public/')
                    ? substr($user->profile_photo, 7)
                    : $user->profile_photo;

                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            }

            // Upload foto baru
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            $user->update([
                'profile_photo' => $path,
            ]);

            return redirect()->route('profil.saya')->with('success', 'Foto profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

    /**
     * Hapus foto profil
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        try {
            if ($user->profile_photo) {
                $cleanPath = str_starts_with($user->profile_photo, 'public/')
                    ? substr($user->profile_photo, 7)
                    : $user->profile_photo;

                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }

                $user->update(['profile_photo' => null]);
            }

            return redirect()->route('profil.saya')->with('success', 'Foto profil berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Cek apakah user login via social media
        if (!$user->hasPassword()) {
            return redirect()->route('profil.saya')->with('error', 'Anda login menggunakan ' . $user->provider_name . ', tidak dapat mengubah password.');
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return redirect()->route('profil.saya')->with('success', 'Password berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }
}
