<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Publication;
use App\Models\PublicationViewLog;
use App\Models\PublicationTypeContent;
use Illuminate\Support\Str;

trait PublicationHelperTrait
{
    /**
     * ✅ Format file size to human readable
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes == 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, $k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * ✅ Log publication view with throttling (5 menit per IP per publikasi)
     */
    private function logPublicationView(Publication $publication): void
    {
        $cacheKey = 'publication:view:throttle.' . $publication->id . '.' . request()->ip();
        if (!Cache::has($cacheKey)) {
            PublicationViewLog::logView($publication);
            $publication->clearStatsCache();
            Cache::put($cacheKey, true, now()->addMinutes(5));
        }
    }

    /**
     * ✅ Clean storage path (remove 'public/' prefix jika ada)
     */
    private function cleanPath(?string $path): ?string
    {
        if (!$path) return null;

        // Hapus prefix 'public/' kalau ada
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        // Hapus leading slash
        return ltrim($path, '/');
    }

    /**
     * ✅ Cek apakah path valid dan file exists di storage
     */
    private function storageFileExists(?string $path): bool
    {
        if (!$path) return false;
        $cleanPath = $this->cleanPath($path);
        return !empty($cleanPath) && Storage::disk('public')->exists($cleanPath);
    }

    /**
     * ✅ Konversi storage path ke public URL
     */
    private function storageFileUrl(string $path): string
    {
        return asset('storage/' . $this->cleanPath($path));
    }

    /**
     * ✅ Get Cover URL dari Publication
     * Priority: cover_image_path (langsung) → versions (relasi) → null
     * Selalu return NULL kalau tidak ada, TIDAK pernah return string kosong
     */
    protected function getCoverUrl($publication): ?string
    {
        // 1️⃣ Cek cover_image_path langsung di publication
        if (!empty($publication->cover_image_path)) {
            $cleanPath = $this->cleanPath($publication->cover_image_path);
            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }

            if (config('app.debug')) {
                \Log::warning('Cover image not found in storage', [
                    'publication_id' => $publication->id,
                    'title'          => $publication->title,
                    'cover_path'     => $publication->cover_image_path,
                    'clean_path'     => $cleanPath,
                ]);
            }
        }

        // 2️⃣ Cek dari relasi versions (kalau sudah di-load)
        if ($publication->relationLoaded('versions') && $publication->versions->isNotEmpty()) {
            foreach ($publication->versions as $version) {
                if (!empty($version->cover_image_path)) {
                    $cleanPath = $this->cleanPath($version->cover_image_path);
                    if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                        return asset('storage/' . $cleanPath);
                    }
                }
            }
        }

        // 3️⃣ Cek dari versions via query (kalau relasi belum di-load, hindari N+1)
        // Hanya lakukan kalau benar-benar perlu (gunakan dengan hati-hati)
        // if (!$publication->relationLoaded('versions')) {
        //     $version = $publication->versions()
        //         ->whereNotNull('cover_image_path')
        //         ->orderBy('version_number', 'desc')
        //         ->first();
        //     if ($version && $this->storageFileExists($version->cover_image_path)) {
        //         return $this->storageFileUrl($version->cover_image_path);
        //     }
        // }

        // ✅ Tidak ada cover → return null, bukan string kosong
        return null;
    }

    /**
     * ✅ Get Cover URL dari PublicationTypeContent
     * Return NULL kalau tidak ada atau file tidak ditemukan
     */
    private function getTypeContentCover($content): ?string
    {
        if (!$content) return null;

        // Cek image_path
        if (!empty($content->image_path)) {
            $cleanPath = $this->cleanPath($content->image_path);
            if ($cleanPath && Storage::disk('public')->exists($cleanPath)) {
                return asset('storage/' . $cleanPath);
            }

            if (config('app.debug')) {
                \Log::warning('Type content cover not found', [
                    'content_id' => $content->id ?? null,
                    'image_path' => $content->image_path,
                    'clean_path' => $cleanPath ?? null,
                ]);
            }
        }

        // Cek image_url kalau ada (beberapa model pakai ini)
        if (!empty($content->image_url) && filter_var($content->image_url, FILTER_VALIDATE_URL)) {
            return $content->image_url;
        }

        return null;
    }

    /**
     * ✅ Cek apakah publication punya cover yang valid
     */
    private function hasValidCover($publication): bool
    {
        return $this->getCoverUrl($publication) !== null;
    }

    /**
     * ✅ Get Placeholder Cover (backward compatibility)
     * Sebaiknya tidak dipakai — biarkan Blade yang handle placeholder
     */
    private function getPlaceholderCover($publication): string
    {
        $titleShort = Str::limit($publication->title ?? 'Publikasi', 25);
        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }
}
