<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        try {
            $user->load(['authorProfile']);

            // ✅ FIXED: Query count pakai filter yang sama dengan PublicationLibraryController
            // agar angka konsisten dan tidak 0

            $publicationsCount = 0;
            try {
                // Coba lewat authorProfile dulu (jika user adalah author)
                if ($user->authorProfile) {
                    $publicationsCount = $user->authorProfile
                        ->publications()
                        ->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())
                        ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                        ->count();
                } else {
                    // Fallback: lewat relasi publications langsung di user
                    $publicationsCount = $user->publications()
                        ->where('status', 'published')
                        ->whereNotNull('published_at')
                        ->where('published_at', '<=', now())
                        ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                        ->count();
                }
            } catch (\Exception $e) {
                $publicationsCount = 0;
            }

            // ✅ FIXED: savedPublications dengan filter published + active type
            $savedCount = 0;
            try {
                $savedCount = $user->savedPublications()
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                    ->count();
            } catch (\Exception $e) {
                // Fallback tanpa filter jika relasi belum ada
                try {
                    $savedCount = $user->savedPublications()->count();
                } catch (\Exception $e2) {
                    $savedCount = 0;
                }
            }

            // ✅ FIXED: favoritePublications dengan filter published + active type
            $favoritesCount = 0;
            try {
                $favoritesCount = $user->favoritePublications()
                    ->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->whereHas('publicationType', fn($q) => $q->where('is_active', true))
                    ->count();
            } catch (\Exception $e) {
                // Fallback tanpa filter jika relasi belum ada
                try {
                    $favoritesCount = $user->favoritePublications()->count();
                } catch (\Exception $e2) {
                    $favoritesCount = 0;
                }
            }
        } catch (\Exception $e) {
            $publicationsCount = 0;
            $savedCount        = 0;
            $favoritesCount    = 0;
        }

        return view('profilesaya', [
            'user'              => $user,
            'publicationsCount' => $publicationsCount,
            'savedCount'        => $savedCount,
            'favoritesCount'    => $favoritesCount,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'whatsapp_number'  => ['nullable', 'string', 'max:20'],
            'job_title'        => ['nullable', 'string', 'max:100'],
            'affiliation'      => ['nullable', 'string', 'max:255'],
            'bio'              => ['nullable', 'string', 'max:500'],

            // ✅ ORCID iD — opsional, validasi format jika diisi
            'orcid_id'         => [
                'nullable',
                'string',
                'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dXx]$/',
                'max:19',
            ],
        ], [
            'orcid_id.regex' => 'Format ORCID tidak valid. Gunakan format: 0000-0000-0000-0000 (digit terakhir bisa huruf X).',
        ]);

        // Normalisasi ORCID: uppercase digit terakhir jika X, hapus jika kosong
        if (!empty($validated['orcid_id'])) {
            $validated['orcid_id'] = strtoupper($validated['orcid_id']);
        } else {
            $validated['orcid_id'] = null;
        }

        try {
            $user->update($validated);
            return redirect()->route('profil.saya')->with('success', 'Profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }

    public function updatePhoto(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        try {
            if ($user->profile_photo) {
                $cleanPath = str_starts_with($user->profile_photo, 'public/')
                    ? substr($user->profile_photo, 7)
                    : $user->profile_photo;

                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $user->update(['profile_photo' => $path]);

            return redirect()->route('profil.saya')->with('success', 'Foto profil berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal mengupload foto: ' . $e->getMessage());
        }
    }

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

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasPassword()) {
            return redirect()->route('profil.saya')->with('error', 'Anda login menggunakan ' . $user->provider_name . ', tidak dapat mengubah password.');
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        try {
            $user->update(['password' => Hash::make($request->password)]);
            return redirect()->route('profil.saya')->with('success', 'Password berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->route('profil.saya')->with('error', 'Gagal memperbarui password: ' . $e->getMessage());
        }
    }
}
