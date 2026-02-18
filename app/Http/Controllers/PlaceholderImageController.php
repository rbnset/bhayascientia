<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Laravel\Facades\Image;

class PlaceholderImageController extends Controller
{
    public function generate(Request $request)
    {
        // Ambil parameter dari URL
        $initials = $request->input('initials', 'NN');
        $type = $request->input('type', 'Publikasi');
        $title = $request->input('title', 'Untitled');
        $category = $request->input('category', 'Umum');
        $author = $request->input('author', 'Anonymous');

        // Cache key berdasarkan parameter
        $cacheKey = 'placeholder_img_' . md5($initials . $type . $title . $category . $author);

        // Cache image selama 30 hari
        $image = Cache::remember($cacheKey, 60 * 24 * 30, function () use ($initials, $type, $title, $category, $author) {
            return $this->createImage($initials, $type, $title, $category, $author);
        });

        return response($image)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=2592000'); // 30 days
    }

    private function createImage($initials, $type, $title, $category, $author)
    {
        // Dimensi cover (aspect ratio 2:3)
        $width = 600;
        $height = 900;

        // ✅ Normalize type (lowercase)
        $typeNormalized = mb_strtolower(trim($type));

        // ✅ Gradient colors berdasarkan type (LOWERCASE KEYS)
        $gradients = [
            'buku' => ['start' => [59, 130, 246], 'end' => [29, 78, 216]],      // Blue
            'jurnal' => ['start' => [255, 107, 24], 'end' => [230, 70, 39]],    // Orange
            'opini' => ['start' => [16, 185, 129], 'end' => [5, 150, 105]],     // Green
            'artikel' => ['start' => [245, 158, 11], 'end' => [217, 119, 6]],   // Yellow
            'penelitian' => ['start' => [139, 92, 246], 'end' => [109, 40, 217]], // Purple
            'skripsi' => ['start' => [236, 72, 153], 'end' => [190, 24, 93]],   // Pink
            'tesis' => ['start' => [6, 182, 212], 'end' => [8, 145, 178]],      // Cyan
            'disertasi' => ['start' => [239, 68, 68], 'end' => [220, 38, 38]],  // Red
            'makalah' => ['start' => [20, 184, 166], 'end' => [15, 118, 110]],  // Teal
            'laporan' => ['start' => [168, 85, 247], 'end' => [124, 58, 237]],  // Light Purple
            'default' => ['start' => [107, 114, 128], 'end' => [75, 85, 99]],   // Gray
        ];

        $gradient = $gradients[$typeNormalized] ?? $gradients['default'];

        // Create canvas
        $img = Image::canvas($width, $height);

        // Apply gradient background
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int)($gradient['start'][0] + ($gradient['end'][0] - $gradient['start'][0]) * $ratio);
            $g = (int)($gradient['start'][1] + ($gradient['end'][1] - $gradient['start'][1]) * $ratio);
            $b = (int)($gradient['start'][2] + ($gradient['end'][2] - $gradient['start'][2]) * $ratio);

            $img->rectangle(0, $y, $width, $y + 1, function ($draw) use ($r, $g, $b) {
                $draw->background("rgb($r, $g, $b)");
                $draw->border(0, "rgb($r, $g, $b)");
            });
        }

        // Add dot pattern
        for ($x = 20; $x < $width; $x += 40) {
            for ($y = 20; $y < $height; $y += 40) {
                $img->circle(3, $x, $y, function ($draw) {
                    $draw->background('rgba(255, 255, 255, 0.1)');
                    $draw->border(0, 'rgba(255, 255, 255, 0.1)');
                });
            }
        }

        // Add overlay gradient
        $overlay = Image::canvas($width, $height, 'rgba(0, 0, 0, 0)');
        for ($y = 0; $y < $height; $y++) {
            $alpha = 0.2 * (1 - $y / $height);
            $overlay->rectangle(0, $y, $width, $y + 1, function ($draw) use ($alpha) {
                $draw->background("rgba(0, 0, 0, $alpha)");
                $draw->border(0, "rgba(0, 0, 0, $alpha)");
            });
        }
        $img->insert($overlay);

        // Add top logo (circle)
        $img->circle(60, 60, 60, function ($draw) {
            $draw->background('rgba(255, 255, 255, 0.2)');
            $draw->border(2, 'rgba(255, 255, 255, 0.3)');
        });

        // Add type badge (top right)
        $img->rectangle($width - 180, 30, $width - 30, 70, function ($draw) {
            $draw->background('rgba(255, 255, 255, 0.25)');
            $draw->border(1, 'rgba(255, 255, 255, 0.4)');
        });

        // Check if font exists
        $fontBold = public_path('fonts/Arial-Bold.ttf');
        $fontRegular = public_path('fonts/Arial.ttf');

        if (file_exists($fontBold)) {
            $img->text(strtoupper($type), $width - 105, 55, function ($font) use ($fontBold) {
                $font->file($fontBold);
                $font->size(16);
                $font->color('#ffffff');
                $font->align('center');
                $font->valign('middle');
            });

            // Add large initials (center)
            $img->text($initials, $width / 2, $height / 2 - 50, function ($font) use ($fontBold) {
                $font->file($fontBold);
                $font->size(120);
                $font->color('rgba(255, 255, 255, 0.95)');
                $font->align('center');
                $font->valign('middle');
            });

            // Add title
            $wrappedTitle = $this->wrapText($title, 35);
            $titleLines = explode("\n", $wrappedTitle);
            $lineHeight = 25;
            $startY = $height / 2 + 60;

            foreach (array_slice($titleLines, 0, 3) as $index => $line) {
                $img->text($line, $width / 2, $startY + ($index * $lineHeight), function ($font) use ($fontBold) {
                    $font->file($fontBold);
                    $font->size(16);
                    $font->color('rgba(255, 255, 255, 0.9)');
                    $font->align('center');
                    $font->valign('top');
                });
            }
        }

        if (file_exists($fontRegular)) {
            // Add category
            $img->text($category, $width / 2, $height - 100, function ($font) use ($fontRegular) {
                $font->file($fontRegular);
                $font->size(14);
                $font->color('rgba(255, 255, 255, 0.9)');
                $font->align('center');
                $font->valign('middle');
            });

            // Add author
            $img->text($this->truncateText($author, 30), $width / 2, $height - 70, function ($font) use ($fontRegular) {
                $font->file($fontRegular);
                $font->size(14);
                $font->color('rgba(255, 255, 255, 0.9)');
                $font->align('center');
                $font->valign('middle');
            });
        }

        // Add decorative line
        $img->line($width / 2 - 60, $height / 2 + 20, $width / 2 + 60, $height / 2 + 20, function ($draw) {
            $draw->color('rgba(255, 255, 255, 0.6)');
            $draw->width(2);
        });

        // Add bottom line
        $img->line($width / 2 - 50, $height - 40, $width / 2 + 50, $height - 40, function ($draw) {
            $draw->color('rgba(255, 255, 255, 0.4)');
            $draw->width(2);
        });

        // Encode to PNG
        return $img->encode('png', 90);
    }

    private function wrapText($text, $maxChars)
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            if (mb_strlen($currentLine . ' ' . $word) <= $maxChars) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }
        if ($currentLine) {
            $lines[] = $currentLine;
        }

        return implode("\n", $lines);
    }

    private function truncateText($text, $maxLength)
    {
        return mb_strlen($text) > $maxLength ? mb_substr($text, 0, $maxLength) . '...' : $text;
    }
}
