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
     * ✅ Log publication view with throttling
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
     * ✅ Clean storage path (remove 'public/' prefix)
     */
    private function cleanPath(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'public/')) {
            return substr($path, 7);
        }
        return $path;
    }

    /**
     * ✅ Get Type Content Cover - Return NULL jika tidak ada
     */
    private function getTypeContentCover($content): ?string
    {
        if (!$content || !$content->image_path) {
            return null; // ← UBAH: Return NULL instead of placeholder
        }

        $cleanPath = $this->cleanPath($content->image_path);

        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // Log warning if file not found
        if (config('app.debug')) {
            \Log::warning('Type content cover not found', [
                'content_id' => $content->id ?? null,
                'image_path' => $content->image_path,
                'clean_path' => $cleanPath,
            ]);
        }

        return null; // ← UBAH: Return NULL instead of placeholder
    }

    /**
     * ✅ Get Cover URL - Return NULL jika tidak ada (untuk support custom placeholder di blade)
     */
    private function getCoverUrl($publication): ?string
    {
        // ✅ UBAH: Return NULL jika tidak ada cover_image_path
        if (!$publication->cover_image_path) {
            return null;
        }

        $cleanPath = $this->cleanPath($publication->cover_image_path);

        // Check if file exists in storage
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }

        // Log warning jika file tidak ditemukan (hanya di development)
        if (config('app.debug')) {
            \Log::warning('Cover image not found', [
                'publication_id' => $publication->id,
                'title' => $publication->title,
                'cover_path' => $publication->cover_image_path,
                'clean_path' => $cleanPath,
                'expected_location' => storage_path('app/public/' . $cleanPath),
            ]);
        }

        // ✅ UBAH: Return NULL instead of placeholder
        // Custom placeholder akan di-handle di blade component
        return null;
    }

    /**
     * ✅ Get Placeholder Cover (untuk backward compatibility - OPTIONAL)
     * Method ini TIDAK dipanggil lagi, tapi tetap ada jika ada kode lama yang membutuhkan
     */
    private function getPlaceholderCover($publication): string
    {
        $categoryName = 'Publikasi';
        if ($publication->relationLoaded('categories') && $publication->categories->isNotEmpty()) {
            $categoryName = $publication->categories->first()->name;
        }

        $titleShort = Str::limit($publication->title, 25);

        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }

    /**
     * ✅ Check if publication has valid cover
     */
    private function hasValidCover($publication): bool
    {
        if (!$publication->cover_image_path) {
            return false;
        }

        $cleanPath = $this->cleanPath($publication->cover_image_path);

        return Storage::disk('public')->exists($cleanPath);
    }
}
