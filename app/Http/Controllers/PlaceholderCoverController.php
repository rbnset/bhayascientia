<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PlaceholderCoverController extends Controller
{
    public function generate(Request $request)
    {
        // Get parameters dengan validation
        $initials = strtoupper(substr($request->input('initials', 'NN'), 0, 3));
        $type = $request->input('type', 'Publikasi');
        $title = $request->input('title', 'Untitled');
        $category = $request->input('category', 'Umum');
        $author = $request->input('author', 'Anonymous');

        // ✅ Normalize type (trim & lowercase untuk matching)
        $typeNormalized = mb_strtolower(trim($type));

        // Cache key berdasarkan parameter
        $cacheKey = 'placeholder_svg_' . md5($initials . $typeNormalized . $title . $category . $author);

        // Cache SVG selama 7 hari (lebih pendek untuk development)
        $svg = Cache::remember($cacheKey, 60 * 24 * 7, function () use ($initials, $type, $typeNormalized, $title, $category, $author) {
            return $this->createSVG($initials, $type, $typeNormalized, $title, $category, $author);
        });

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=604800'); // 7 days
    }

    private function createSVG($initials, $typeOriginal, $typeNormalized, $title, $category, $author)
    {
        // ✅ Gradient colors berdasarkan type (LOWERCASE KEYS)
        $gradients = [
            // Indonesian names
            'jurnal' => ['start' => '#FF6B18', 'end' => '#E64627'],      // 🟠 Orange
            'buku' => ['start' => '#3B82F6', 'end' => '#1D4ED8'],        // 🔵 Blue
            'opini' => ['start' => '#10B981', 'end' => '#059669'],       // 🟢 Green
            'artikel' => ['start' => '#F59E0B', 'end' => '#D97706'],     // 🟡 Yellow
            'penelitian' => ['start' => '#8B5CF6', 'end' => '#6D28D9'],  // 🟣 Purple
            'skripsi' => ['start' => '#EC4899', 'end' => '#BE185D'],     // 🩷 Pink
            'tesis' => ['start' => '#06B6D4', 'end' => '#0891B2'],       // 🔷 Cyan
            'disertasi' => ['start' => '#EF4444', 'end' => '#DC2626'],   // 🔴 Red
            'makalah' => ['start' => '#14B8A6', 'end' => '#0F766E'],     // 🐚 Teal
            'laporan' => ['start' => '#A855F7', 'end' => '#7C3AED'],     // 💜 Light Purple
            'prosiding' => ['start' => '#F97316', 'end' => '#EA580C'],   // 🟠 Orange Red
            'konferensi' => ['start' => '#84CC16', 'end' => '#65A30D'],  // 🟩 Lime

            // English variants
            'journal' => ['start' => '#FF6B18', 'end' => '#E64627'],
            'book' => ['start' => '#3B82F6', 'end' => '#1D4ED8'],
            'opinion' => ['start' => '#10B981', 'end' => '#059669'],
            'article' => ['start' => '#F59E0B', 'end' => '#D97706'],
            'research' => ['start' => '#8B5CF6', 'end' => '#6D28D9'],
            'thesis' => ['start' => '#06B6D4', 'end' => '#0891B2'],
            'dissertation' => ['start' => '#EF4444', 'end' => '#DC2626'],
            'paper' => ['start' => '#14B8A6', 'end' => '#0F766E'],
            'report' => ['start' => '#A855F7', 'end' => '#7C3AED'],
            'proceeding' => ['start' => '#F97316', 'end' => '#EA580C'],

            // Default fallback (Gray)
            'default' => ['start' => '#6B7280', 'end' => '#4B5563'],
        ];

        // ✅ Get gradient with fallback
        $gradient = $gradients[$typeNormalized] ?? $gradients['default'];

        // ✅ Log untuk debugging (hanya di development)
        if (config('app.debug')) {
            \Log::info('SVG Cover Generation', [
                'type_original' => $typeOriginal,
                'type_normalized' => $typeNormalized,
                'gradient_used' => $gradient,
                'is_default' => !isset($gradients[$typeNormalized]),
            ]);
        }

        // Wrap text untuk title (max 35 chars per line, max 3 lines)
        $wrappedTitle = $this->wrapText($title, 35, 3);

        // Truncate strings
        $truncatedAuthor = Str::limit($author, 35, '...');
        $truncatedCategory = Str::limit($category, 30, '...');

        // Generate unique IDs untuk avoid collision
        $gradientId = 'grad-' . uniqid();
        $patternId = 'pattern-' . uniqid();
        $overlayId = 'overlay-' . uniqid();

        $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="600" height="900" viewBox="0 0 600 900" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <!-- Main Gradient Background -->
        <linearGradient id="{$gradientId}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$gradient['start']};stop-opacity:1" />
            <stop offset="100%" style="stop-color:{$gradient['end']};stop-opacity:1" />
        </linearGradient>

        <!-- Dot Pattern -->
        <pattern id="{$patternId}" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
            <circle cx="20" cy="20" r="1.5" fill="white" opacity="0.1"/>
        </pattern>

        <!-- Overlay Gradient (subtle) -->
        <linearGradient id="{$overlayId}" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%" style="stop-color:white;stop-opacity:0.08" />
            <stop offset="50%" style="stop-color:transparent;stop-opacity:0" />
            <stop offset="100%" style="stop-color:black;stop-opacity:0.15" />
        </linearGradient>
    </defs>

    <!-- Base Background -->
    <rect width="600" height="900" fill="url(#{$gradientId})"/>

    <!-- Pattern Overlay -->
    <rect width="600" height="900" fill="url(#{$patternId})"/>

    <!-- Gradient Overlay -->
    <rect width="600" height="900" fill="url(#{$overlayId})"/>

    <!-- Top Logo Container (Minimal Circle) -->
    <g transform="translate(50, 50)">
        <circle cx="0" cy="0" r="35" fill="white" opacity="0.15"/>
        <circle cx="0" cy="0" r="35" fill="none" stroke="white" stroke-width="2" opacity="0.3"/>
        <text x="0" y="8" font-family="Arial, sans-serif" font-size="28" font-weight="bold" fill="white" text-anchor="middle">
            {$this->escapeXml(substr($initials, 0, 1))}
        </text>
    </g>

    <!-- Publication Type Badge (Top Right) -->
    <g transform="translate(400, 50)">
        <rect x="0" y="0" width="160" height="44" rx="22" fill="white" opacity="0.2"/>
        <rect x="0" y="0" width="160" height="44" rx="22" fill="none" stroke="white" stroke-width="1.5" opacity="0.4"/>
        <text x="80" y="28" font-family="Arial, sans-serif" font-size="15" font-weight="700" fill="white" text-anchor="middle">
            {$this->escapeXml(strtoupper($typeOriginal))}
        </text>
    </g>

    <!-- Large Initials (Center) -->
    <text x="300" y="310" font-family="Arial, sans-serif" font-size="140" font-weight="900" fill="white" opacity="0.95" text-anchor="middle">
        {$this->escapeXml($initials)}
    </text>

    <!-- Decorative Line -->
    <line x1="220" y1="350" x2="280" y2="350" stroke="white" stroke-width="3" opacity="0.5" stroke-linecap="round"/>
    <circle cx="300" cy="350" r="5" fill="white" opacity="0.7"/>
    <line x1="320" y1="350" x2="380" y2="350" stroke="white" stroke-width="3" opacity="0.5" stroke-linecap="round"/>

    <!-- Title (Wrapped, Multi-line) -->
    {$this->generateTextLines($wrappedTitle, 300, 410, 30)}

    <!-- Category Badge (Bottom Section) -->
    <g transform="translate(300, 740)">
        <rect x="-80" y="-18" width="160" height="36" rx="18" fill="white" opacity="0.15"/>
        <text y="8" font-family="Arial, sans-serif" font-size="17" font-weight="600" fill="white" opacity="0.9" text-anchor="middle">
            {$this->escapeXml($truncatedCategory)}
        </text>
    </g>

    <!-- Author Name (Bottom) -->
    <g transform="translate(300, 810)">
        <text y="0" font-family="Arial, sans-serif" font-size="16" font-weight="500" fill="white" opacity="0.85" text-anchor="middle">
            {$this->escapeXml($truncatedAuthor)}
        </text>
    </g>

    <!-- Bottom Decorative Line -->
    <line x1="240" y1="860" x2="360" y2="860" stroke="white" stroke-width="2" opacity="0.3" stroke-linecap="round"/>

    <!-- Corner Decorations -->
    <g transform="translate(540, 0)" opacity="0.08">
        <path d="M0 0L60 0L60 60Q45 60 33 48Q21 36 9 21Q0 9 0 0Z" fill="white"/>
    </g>
    <g transform="translate(60, 900) scale(1, -1)" opacity="0.08">
        <path d="M0 0L60 0L60 60Q45 60 33 48Q21 36 9 21Q0 9 0 0Z" fill="white"/>
    </g>
</svg>
SVG;

        return $svg;
    }

    /**
     * ✅ Wrap text dengan max chars per line dan max lines
     */
    private function wrapText($text, $maxChars, $maxLines)
    {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? $currentLine . ' ' . $word : $word;

            if (mb_strlen($testLine) <= $maxChars) {
                $currentLine = $testLine;
            } else {
                if ($currentLine) {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;

                if (count($lines) >= $maxLines - 1) {
                    break;
                }
            }
        }

        if ($currentLine && count($lines) < $maxLines) {
            $lines[] = $currentLine;
        }

        // Add ellipsis if truncated
        if (count($lines) == $maxLines) {
            $remainingWords = array_slice($words, count(explode(' ', implode(' ', $lines))));
            if (!empty($remainingWords)) {
                $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], '.') . '...';
            }
        }

        return $lines;
    }

    /**
     * ✅ Generate SVG text elements for multi-line title
     */
    private function generateTextLines($lines, $x, $startY, $lineHeight)
    {
        if (empty($lines)) {
            return '';
        }

        $textElements = '<text x="' . $x . '" y="' . $startY . '" font-family="Arial, sans-serif" font-size="24" font-weight="700" fill="white" opacity="0.95" text-anchor="middle">';

        foreach ($lines as $index => $line) {
            $dy = $index === 0 ? '0' : $lineHeight;
            $textElements .= '<tspan x="' . $x . '" dy="' . $dy . '">' . $this->escapeXml($line) . '</tspan>';
        }

        $textElements .= '</text>';

        return $textElements;
    }

    /**
     * ✅ Escape XML special characters
     */
    private function escapeXml($text)
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
