<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class PlaceholderCoverController extends Controller
{
    public function generate(Request $request)
    {
        $initials = strtoupper(substr($request->input('initials', 'NN'), 0, 3));
        $type     = Str::limit(strip_tags($request->input('type', 'Publikasi')), 50);
        $title    = Str::limit(strip_tags($request->input('title', 'Untitled')), 100);
        $category = Str::limit(strip_tags($request->input('category', 'Umum')), 50);
        $author   = Str::limit(strip_tags($request->input('author', 'Anonymous')), 100);

        $typeNormalized = mb_strtolower(trim($type));
        $version        = $request->input('v', '1');
        $cacheKey       = 'placeholder_svg_v7_' . md5($initials . $typeNormalized . $title . $category . $author . $version);

        $svg = Cache::remember($cacheKey, now()->addDays(7), function () use ($initials, $type, $typeNormalized, $title, $category, $author) {
            return $this->createSVG($initials, $type, $typeNormalized, $title, $category, $author);
        });

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=604800')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    // ─── Ambil logo sebagai Base64 ──────────────────────────────────────────
    private function getLogoBase64(): string
    {
        // Cache logo Base64 agar tidak baca file berulang kali
        return Cache::remember('placeholder_logo_base64', now()->addDay(), function () {
            $logoPath = public_path('assets/images/logos/logo.svg');

            if (!file_exists($logoPath)) {
                return '';
            }

            $logoContent = file_get_contents($logoPath);
            $ext         = strtolower(pathinfo($logoPath, PATHINFO_EXTENSION));

            $mimeType = match ($ext) {
                'png'         => 'image/png',
                'jpg', 'jpeg' => 'image/jpeg',
                'webp'        => 'image/webp',
                default       => 'image/svg+xml',
            };

            return 'data:' . $mimeType . ';base64,' . base64_encode($logoContent);
        });
    }

    private function createSVG(string $initials, string $typeOriginal, string $typeNormalized, string $title, string $category, string $author): string
    {
        $gradients = [
            // Indonesian
            'jurnal'       => ['start' => '#FF6B18', 'end' => '#E64627'],
            'buku'         => ['start' => '#3B82F6', 'end' => '#1D4ED8'],
            'opini'        => ['start' => '#10B981', 'end' => '#059669'],
            'artikel'      => ['start' => '#F59E0B', 'end' => '#D97706'],
            'penelitian'   => ['start' => '#8B5CF6', 'end' => '#6D28D9'],
            'skripsi'      => ['start' => '#EC4899', 'end' => '#BE185D'],
            'tesis'        => ['start' => '#06B6D4', 'end' => '#0891B2'],
            'disertasi'    => ['start' => '#EF4444', 'end' => '#DC2626'],
            'makalah'      => ['start' => '#14B8A6', 'end' => '#0F766E'],
            'laporan'      => ['start' => '#A855F7', 'end' => '#7C3AED'],
            'prosiding'    => ['start' => '#F97316', 'end' => '#EA580C'],
            'konferensi'   => ['start' => '#84CC16', 'end' => '#65A30D'],
            // English
            'journal'      => ['start' => '#FF6B18', 'end' => '#E64627'],
            'book'         => ['start' => '#3B82F6', 'end' => '#1D4ED8'],
            'opinion'      => ['start' => '#10B981', 'end' => '#059669'],
            'article'      => ['start' => '#F59E0B', 'end' => '#D97706'],
            'research'     => ['start' => '#8B5CF6', 'end' => '#6D28D9'],
            'thesis'       => ['start' => '#06B6D4', 'end' => '#0891B2'],
            'dissertation' => ['start' => '#EF4444', 'end' => '#DC2626'],
            'paper'        => ['start' => '#14B8A6', 'end' => '#0F766E'],
            'report'       => ['start' => '#A855F7', 'end' => '#7C3AED'],
            'proceeding'   => ['start' => '#F97316', 'end' => '#EA580C'],
            // Default
            'default'      => ['start' => '#122966', 'end' => '#1a3a8a'], // ← sesuai brand kamu
        ];

        $gradient = $gradients[$typeNormalized] ?? $gradients['default'];

        if (config('app.debug')) {
            \Log::info('SVG Cover Generation', [
                'type_original'   => $typeOriginal,
                'type_normalized' => $typeNormalized,
                'gradient_used'   => $gradient,
                'is_default'      => !isset($gradients[$typeNormalized]),
            ]);
        }

        $authorsDisplay    = $this->formatAuthors($author);
        $wrappedTitle      = $this->wrapText($title, 30, 4);
        $truncatedCategory = Str::limit($category, 32, '...');

        // Unique IDs per render (penting agar tidak konflik jika multiple SVG di halaman)
        $uid        = substr(md5(uniqid()), 0, 8);
        $gradientId = 'grad-'    . $uid;
        $patternId  = 'pattern-' . $uid;
        $overlayId  = 'overlay-' . $uid;
        $glowId     = 'glow-'    . $uid;

        $logoBase64  = $this->getLogoBase64();
        $titleSVG    = $this->generateTextLines($wrappedTitle, 300, 455, 38);
        $typeBadge   = $this->escapeXml(strtoupper($typeOriginal));
        $authorSVG   = $this->escapeXml($authorsDisplay);
        $categorySVG = $this->escapeXml($truncatedCategory);
        $initialsSVG = $this->escapeXml($initials);

        // ─── Logo element ────────────────────────────────────────────────────
        if (!empty($logoBase64)) {
            $logoElement = <<<LOGO
    <!-- LOGO (Base64 Embedded) -->
    <image href="{$logoBase64}"
           xlink:href="{$logoBase64}"
           x="32" y="28"
           width="110" height="110"
           preserveAspectRatio="xMidYMid meet"
           opacity="1"
           filter="url(#{$glowId})"/>
LOGO;
        } else {
            $firstInitial = $this->escapeXml(substr($initials, 0, 1));
            $logoElement  = <<<FALLBACK
    <!-- LOGO FALLBACK (Initial) -->
    <g transform="translate(32, 28)">
        <rect x="0" y="0" width="110" height="110" rx="16"
              fill="rgba(255,255,255,0.15)"
              stroke="rgba(255,255,255,0.4)" stroke-width="2"/>
        <text x="55" y="72"
              font-family="Arial Black, Arial, sans-serif"
              font-size="60" font-weight="900"
              fill="white" text-anchor="middle">{$firstInitial}</text>
    </g>
FALLBACK;
        }

        return <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="600" height="900" viewBox="0 0 600 900"
     xmlns="http://www.w3.org/2000/svg"
     xmlns:xlink="http://www.w3.org/1999/xlink">
    <defs>
        <linearGradient id="{$gradientId}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%"   stop-color="{$gradient['start']}" stop-opacity="1"/>
            <stop offset="55%"  stop-color="{$gradient['end']}"   stop-opacity="1"/>
            <stop offset="100%" stop-color="{$gradient['start']}" stop-opacity="0.88"/>
        </linearGradient>
        <pattern id="{$patternId}" x="0" y="0" width="44" height="44" patternUnits="userSpaceOnUse">
            <circle cx="22" cy="22" r="1.4" fill="white" opacity="0.09"/>
        </pattern>
        <linearGradient id="{$overlayId}" x1="0%" y1="0%" x2="0%" y2="100%">
            <stop offset="0%"   stop-color="rgba(255,255,255,0.10)"/>
            <stop offset="45%"  stop-color="rgba(0,0,0,0.0)"/>
            <stop offset="100%" stop-color="rgba(0,0,0,0.28)"/>
        </linearGradient>
        <filter id="{$glowId}" x="-15%" y="-15%" width="130%" height="130%">
            <feDropShadow dx="0" dy="0" stdDeviation="5"
                          flood-color="rgba(255,255,255,0.55)"/>
        </filter>
    </defs>

    <!-- Background Layers -->
    <rect width="600" height="900" fill="url(#{$gradientId})"/>
    <rect width="600" height="900" fill="url(#{$patternId})"/>
    <rect width="600" height="900" fill="url(#{$overlayId})"/>

    <!-- Corner Ornaments -->
    <polygon points="600,0 600,100 500,0" fill="rgba(255,255,255,0.06)"/>
    <polygon points="0,900 100,900 0,800" fill="rgba(255,255,255,0.06)"/>

{$logoElement}

    <!-- Type Badge (glassmorphism) -->
    <g transform="translate(418, 44)">
        <rect x="0" y="0" width="162" height="46" rx="23"
              fill="rgba(255,255,255,0.18)"
              stroke="rgba(255,255,255,0.45)" stroke-width="1.4"/>
        <rect x="4" y="4" width="154" height="38" rx="19"
              fill="rgba(255,255,255,0.08)"/>
        <text x="81" y="30"
              font-family="Arial, sans-serif" font-size="16"
              font-weight="800" fill="white" text-anchor="middle"
              letter-spacing="1.2">{$typeBadge}</text>
    </g>

    <!-- Center Initials -->
    <text x="300" y="330"
          font-family="Arial Black, Arial, sans-serif"
          font-size="158" font-weight="900"
          fill="white" opacity="0.97"
          text-anchor="middle"
          stroke="rgba(0,0,0,0.12)" stroke-width="2">{$initialsSVG}</text>

    <!-- Divider -->
    <line x1="222" y1="372" x2="288" y2="372"
          stroke="rgba(255,255,255,0.65)" stroke-width="4" stroke-linecap="round"/>
    <circle cx="300" cy="372" r="7" fill="white" opacity="0.92"/>
    <line x1="312" y1="372" x2="378" y2="372"
          stroke="rgba(255,255,255,0.65)" stroke-width="4" stroke-linecap="round"/>

    <!-- Title -->
    {$titleSVG}

    <!-- Separator -->
    <line x1="60" y1="670" x2="540" y2="670"
          stroke="rgba(255,255,255,0.18)" stroke-width="1" stroke-linecap="round"/>

    <!-- Category Badge -->
    <g transform="translate(300, 720)">
        <rect x="-110" y="-24" width="220" height="48" rx="24"
              fill="rgba(255,255,255,0.18)"
              stroke="rgba(255,255,255,0.42)" stroke-width="1.4"/>
        <text y="7"
              font-family="Arial, sans-serif" font-size="20"
              font-weight="700" fill="white" opacity="0.98"
              text-anchor="middle" letter-spacing="0.5">{$categorySVG}</text>
    </g>

    <!-- Author -->
    <text x="300" y="793"
          font-family="Arial, sans-serif" font-size="20"
          font-weight="600" fill="white" opacity="0.95"
          text-anchor="middle" letter-spacing="0.3">{$authorSVG}</text>

    <!-- Tagline -->
    <text x="300" y="838"
          font-family="Arial, sans-serif" font-size="16.5"
          font-weight="400" font-style="italic"
          fill="rgba(255,255,255,0.88)" text-anchor="middle"
          letter-spacing="0.9">Where Knowledge Shapes Policing</text>

    <!-- Bottom Accent -->
    <line x1="232" y1="870" x2="368" y2="870"
          stroke="rgba(255,255,255,0.45)" stroke-width="3.5" stroke-linecap="round"/>
    <circle cx="300" cy="870" r="6" fill="white" opacity="0.82"/>

</svg>
SVG;
    }

    private function formatAuthors(string $author): string
    {
        $authors = array_values(array_filter(array_map('trim', explode(',', $author))));
        return match (true) {
            count($authors) === 0 => 'Anonymous',
            count($authors) <= 2  => implode(', ', $authors),
            default               => $authors[0] . ', ' . $authors[1] . ' et al.',
        };
    }

    private function wrapText(string $text, int $maxChars, int $maxLines): array
    {
        $words       = explode(' ', $text);
        $lines       = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
            if (mb_strlen($testLine) <= $maxChars) {
                $currentLine = $testLine;
            } else {
                if ($currentLine) $lines[] = $currentLine;
                $currentLine = $word;
                if (count($lines) >= $maxLines - 1) break;
            }
        }

        if ($currentLine && count($lines) < $maxLines) {
            $lines[] = $currentLine;
        }

        if (count($lines) === $maxLines) {
            $joined    = implode(' ', $lines);
            $remaining = array_slice($words, count(explode(' ', $joined)));
            if (!empty($remaining)) {
                $lines[$maxLines - 1] = rtrim($lines[$maxLines - 1], '.') . '...';
            }
        }

        return $lines;
    }

    private function generateTextLines(array $lines, int $x, int $startY, int $lineHeight): string
    {
        if (empty($lines)) return '';

        $totalHeight = (count($lines) - 1) * $lineHeight;
        $adjustedY   = $startY - ($totalHeight / 2);

        $out = '<text'
            . ' x="' . $x . '"'
            . ' y="' . $adjustedY . '"'
            . ' font-family="Arial Black, Arial, sans-serif"'
            . ' font-size="28"'
            . ' font-weight="800"'
            . ' fill="white"'
            . ' opacity="0.97"'
            . ' text-anchor="middle"'
            . ' letter-spacing="0.4"'
            . '>';

        foreach ($lines as $i => $line) {
            $dy   = $i === 0 ? '0' : $lineHeight;
            $out .= '<tspan x="' . $x . '" dy="' . $dy . '">'
                . $this->escapeXml($line)
                . '</tspan>';
        }

        return $out . '</text>';
    }

    private function escapeXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
