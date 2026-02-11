<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\Publication;
use App\Models\PublicationViewLog;
use Illuminate\Support\Str;

trait PublicationHelperTrait
{
    private function formatFileSize(int $bytes): string
    {
        if ($bytes == 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, $k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    private function logPublicationView(Publication $publication): void
    {
        $cacheKey = 'publication:view:throttle.' . $publication->id . '.' . request()->ip();
        if (!Cache::has($cacheKey)) {
            PublicationViewLog::logView($publication);
            $publication->clearStatsCache();
            Cache::put($cacheKey, true, now()->addMinutes(5));
        }
    }

    private function cleanPath(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'public/')) {
            return substr($path, 7);
        }
        return $path;
    }

    private function getTypeContentCover($content): string
    {
        if (!$content || !$content->image_path) {
            return 'https://placehold.co/800x600/FF6B18/white?text=' . urlencode('Publication Type');
        }
        $cleanPath = $this->cleanPath($content->image_path);
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }
        return 'https://placehold.co/800x600/FF6B18/white?text=' . urlencode($content->title ?? 'Content');
    }

    private function getCoverUrl($publication): string
    {
        if (!$publication->cover_image_path) {
            return $this->getPlaceholderCover($publication);
        }
        $cleanPath = $this->cleanPath($publication->cover_image_path);
        if (Storage::disk('public')->exists($cleanPath)) {
            return asset('storage/' . $cleanPath);
        }
        if (config('app.debug')) {
            \Log::warning('Cover image not found', [
                'publication_id' => $publication->id,
                'title' => $publication->title,
                'cover_path' => $publication->cover_image_path,
                'clean_path' => $cleanPath,
            ]);
        }
        return $this->getPlaceholderCover($publication);
    }

    private function getPlaceholderCover($publication): string
    {
        $categoryName = 'Publikasi';
        if ($publication->relationLoaded('categories') && $publication->categories->isNotEmpty()) {
            $categoryName = $publication->categories->first()->name;
        }
        $titleShort = Str::limit($publication->title, 25);
        return 'https://placehold.co/400x600/FF6B18/white?text=' . urlencode($titleShort);
    }
}
