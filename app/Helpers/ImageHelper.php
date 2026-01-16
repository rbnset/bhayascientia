<?php

namespace App\Helpers;

class ImageHelper
{
    /**
     * Generate placeholder image URL berdasarkan title dan category
     * Menggunakan service DiceBear atau UI Avatars dengan color custom
     */
    public static function generatePlaceholder(string $title, ?string $category = null, int $width = 400, int $height = 600): string
    {
        // Daftar warna untuk tiap kategori
        $categoryColors = [
            'Teknologi Informasi' => ['bg' => 'FF6B18', 'text' => 'FFFFFF'],
            'Ilmu Sosial' => ['bg' => '3B82F6', 'text' => 'FFFFFF'],
            'Ekonomi' => ['bg' => '10B981', 'text' => 'FFFFFF'],
            'Hukum' => ['bg' => 'EF4444', 'text' => 'FFFFFF'],
            'Kesehatan' => ['bg' => 'EC4899', 'text' => 'FFFFFF'],
            'Pendidikan' => ['bg' => '8B5CF6', 'text' => 'FFFFFF'],
            'Sains' => ['bg' => '06B6D4', 'text' => 'FFFFFF'],
            'Teknik' => ['bg' => 'F59E0B', 'text' => 'FFFFFF'],
            'Pertanian' => ['bg' => '84CC16', 'text' => 'FFFFFF'],
            'Psikologi' => ['bg' => 'A855F7', 'text' => 'FFFFFF'],
            'Komunikasi' => ['bg' => '14B8A6', 'text' => 'FFFFFF'],
            'Bisnis' => ['bg' => '0EA5E9', 'text' => 'FFFFFF'],
            'default' => ['bg' => 'A3A6AE', 'text' => 'FFFFFF'],
        ];

        // Ambil warna berdasarkan category
        $colors = $categoryColors[$category] ?? $categoryColors['default'];

        // Ambil 3 kata pertama dari title untuk placeholder text
        $words = explode(' ', $title);
        $placeholderText = implode('+', array_slice($words, 0, 3));

        // Generate URL menggunakan via.placeholder.com dengan text
        return "https://via.placeholder.com/{$width}x{$height}/{$colors['bg']}/{$colors['text']}?text=" . urlencode($placeholderText);
    }

    /**
     * Get cover URL with fallback to placeholder
     */
    public static function getCoverUrl(?string $coverPath, string $title, ?string $category = null): string
    {
        if ($coverPath && file_exists(storage_path('app/public/' . $coverPath))) {
            return asset('storage/' . $coverPath);
        }

        return self::generatePlaceholder($title, $category);
    }
}
