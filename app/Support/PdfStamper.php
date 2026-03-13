<?php

namespace App\Support;

use App\Models\Publication;
use App\Models\PublicationVersion;

class PdfStamper
{
    private const SIDE_WATERMARK_STATUSES = [
        'submitted',
        'revision_required',
    ];

    private const STATUS_COLORS = [
        'draft'             => [156, 163, 175],
        'submitted'         => [245, 158, 11],
        'in_review'         => [59, 130, 246],
        'revision_required' => [239, 68, 68],
        'accepted'          => [34, 197, 94],
        'rejected'          => [239, 68, 68],
        'published'         => [16, 185, 129],
    ];

    // Batas halaman preview untuk guest per tipe publikasi
    private const GUEST_PAGE_LIMITS = [
        'jurnal' => 3,
        'buku'   => 10,
        'opini'  => 1,
    ];

    private const DEFAULT_GUEST_LIMIT = 3;

    // ─────────────────────────────────────────────────────────────
    // Convert PDF modern → PDF 1.4 via GhostScript
    // ─────────────────────────────────────────────────────────────
    private static function convertToCompatible(string $absolutePath): string
    {
        $tempPath = storage_path('app/temp/converted_' . md5($absolutePath . time()) . '.pdf');

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $gs  = '/usr/bin/gs';
        $cmd = escapeshellcmd($gs)
            . ' -dBATCH'
            . ' -dNOPAUSE'
            . ' -dQUIET'
            . ' -dSAFER'
            . ' -sDEVICE=pdfwrite'
            . ' -dCompatibilityLevel=1.4'
            . ' -sOutputFile=' . escapeshellarg($tempPath)
            . ' ' . escapeshellarg($absolutePath)
            . ' 2>/dev/null';

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($tempPath)) {
            throw new \RuntimeException('GhostScript conversion failed with code: ' . $returnCode);
        }

        return $tempPath;
    }

    // ─────────────────────────────────────────────────────────────
    // Main stamp entry point
    // ─────────────────────────────────────────────────────────────
    public static function stamp(string $absolutePath, PublicationVersion $version, bool $isGuest = false): string
    {
        $version->loadMissing('publication');
        $publication = $version->publication;

        // Tentukan batas halaman untuk guest
        $pageLimit = null;
        if ($isGuest) {
            $typeSlug  = $publication->publicationType?->slug ?? '';
            $pageLimit = self::GUEST_PAGE_LIMITS[$typeSlug] ?? self::DEFAULT_GUEST_LIMIT;
        }

        $convertedPath = null;
        try {
            $convertedPath = self::convertToCompatible($absolutePath);
            $pathToUse     = $convertedPath;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('GS convert failed, using original: ' . $e->getMessage());
            $pathToUse = $absolutePath;
        }

        try {
            $pdf       = new PdfWithRotation();
            $pageCount = $pdf->setSourceFile($pathToUse);

            // Untuk guest: batasi halaman yang dirender
            $pagesToRender = ($pageLimit !== null) ? min($pageLimit, $pageCount) : $pageCount;
            $isLimited     = ($pageLimit !== null) && ($pageCount > $pageLimit);

            for ($pageNo = 1; $pageNo <= $pagesToRender; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $size  = $pdf->getTemplateSize($tplId);

                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);

                self::drawHeaderStamp($pdf, $size, $publication, $version, $pageNo, $pageCount);

                if (in_array($publication->status, self::SIDE_WATERMARK_STATUSES)) {
                    self::drawSideWatermark($pdf, $size, $publication->status);
                }

                if ($isGuest) {
                    self::drawGuestWatermark($pdf, $size);
                }
            }

            // Halaman CTA login di akhir jika halaman dipotong
            if ($isLimited) {
                // Ambil ukuran halaman terakhir yang dirender sebagai referensi
                $lastTplId   = $pdf->importPage($pagesToRender);
                $lastPageSize = $pdf->getTemplateSize($lastTplId);
                self::drawLoginCTAPage($pdf, $lastPageSize, $publication, $pagesToRender, $pageCount);
            }

            $result = $pdf->Output('S');
        } finally {
            if ($convertedPath && file_exists($convertedPath)) {
                @unlink($convertedPath);
            }
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────────
    // Guest watermark — diagonal di tengah setiap halaman
    // ─────────────────────────────────────────────────────────────
    private static function drawGuestWatermark(PdfWithRotation $pdf, array $size): void
    {
        $centerX = $size['width'] / 2;
        $centerY = $size['height'] / 2;

        // Watermark utama diagonal
        $pdf->SetFont('Helvetica', 'B', 42);
        $pdf->SetTextColor(220, 220, 220);
        $pdf->RotatedText($centerX - 30, $centerY + 15, 'PREVIEW', 45);

        // Teks kecil di bawah watermark
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->RotatedText($centerX - 38, $centerY + 28, 'Login untuk akses penuh', 45);

        // Reset
        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Halaman CTA login — ditambahkan di akhir untuk guest
    // ─────────────────────────────────────────────────────────────
    private static function drawLoginCTAPage(
        PdfWithRotation $pdf,
        array $size,
        Publication $publication,
        int $shownPages,
        int $totalPages,
    ): void {
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

        $w = $size['width'];
        $h = $size['height'];

        // ── Background putih bersih ──
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, $w, $h, 'F');

        // ── Strip oranye atas ──
        $pdf->SetFillColor(255, 107, 24);
        $pdf->Rect(0, 0, $w, 5, 'F');

        // ── Strip oranye bawah ──
        $pdf->SetFillColor(255, 107, 24);
        $pdf->Rect(0, $h - 5, $w, 5, 'F');

        // ── Lingkaran dekoratif background ──
        $pdf->SetFillColor(255, 247, 242);
        $pdf->SetDrawColor(255, 247, 242);
        $pdf->SetLineWidth(0);
        $pdf->Circle($w * 0.1, $h * 0.2, 30, 0, 360, 'F');
        $pdf->Circle($w * 0.9, $h * 0.8, 25, 0, 360, 'F');
        $pdf->Circle($w * 0.85, $h * 0.15, 18, 0, 360, 'F');

        // ── Konten tengah — posisi vertikal ──
        $centerX = $w / 2;
        $startY  = $h * 0.22;

        // ── Ikon gembok dalam lingkaran oranye ──
        $iconCY = $startY + 14;

        // Lingkaran background ikon
        $pdf->SetFillColor(255, 237, 213);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0.8);
        $pdf->Circle($centerX, $iconCY, 16, 0, 360, 'DF');

        // Body gembok
        $lockBodyW = 12;
        $lockBodyH = 9;
        $lockBodyX = $centerX - $lockBodyW / 2;
        $lockBodyY = $iconCY + 2;
        $pdf->SetFillColor(255, 107, 24);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0);
        $pdf->RoundedRect($lockBodyX, $lockBodyY, $lockBodyW, $lockBodyH, 1.5, 'F');

        // Shackle (busur atas gembok)
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(2.2);
        $pdf->Arc($centerX, $lockBodyY + 0.5, 4.5, 190, 350);

        // Lubang kunci
        $pdf->SetFillColor(255, 237, 213);
        $pdf->SetLineWidth(0);
        $pdf->Circle($centerX, $lockBodyY + 4.5, 1.8, 0, 360, 'F');

        // ── Judul utama ──
        $titleY = $iconCY + 22;
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->SetXY(0, $titleY);
        $pdf->Cell($w, 9, 'Pratinjau Berakhir', 0, 1, 'C');

        // ── Subjudul — info halaman ──
        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($w * 0.15, $titleY + 11);
        $pdf->Cell($w * 0.7, 5.5, 'Kamu telah membaca ' . $shownPages . ' dari ' . $totalPages . ' halaman.', 0, 1, 'C');

        $pdf->SetXY($w * 0.15, $titleY + 17);
        $pdf->Cell($w * 0.7, 5.5, 'Login untuk melanjutkan membaca secara gratis.', 0, 1, 'C');

        // ── Stats bar ──
        $statsY = $titleY + 28;
        $statW  = $w * 0.22;
        $statGap = ($w - $statW * 3) / 4;

        $stats = [
            ['Dibaca', $shownPages . ' hal'],
            ['Tersisa', ($totalPages - $shownPages) . ' hal'],
            ['Total', $totalPages . ' hal'],
        ];

        foreach ($stats as $i => $stat) {
            $sx = $statGap + $i * ($statW + $statGap);
            $pdf->SetFillColor(249, 250, 252);
            $pdf->SetDrawColor(238, 240, 247);
            $pdf->SetLineWidth(0.3);
            $pdf->RoundedRect($sx, $statsY, $statW, 14, 2, 'DF');

            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(26, 26, 26);
            $pdf->SetXY($sx, $statsY + 2);
            $pdf->Cell($statW, 5, $stat[1], 0, 1, 'C');

            $pdf->SetFont('Helvetica', '', 6.5);
            $pdf->SetTextColor(115, 115, 115);
            $pdf->SetXY($sx, $statsY + 7);
            $pdf->Cell($statW, 4, $stat[0], 0, 1, 'C');
        }

        // ── Garis pemisah ──
        $divY = $statsY + 20;
        $pdf->SetDrawColor(238, 240, 247);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($w * 0.15, $divY, $w * 0.85, $divY);

        // ── Tombol "Masuk Sekarang" ──
        $btnW  = 65;
        $btnH  = 11;
        $btnX  = ($w - $btnW) / 2;
        $btn1Y = $divY + 8;

        $pdf->SetFillColor(255, 107, 24);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0);
        $pdf->RoundedRect($btnX, $btn1Y, $btnW, $btnH, 2.5, 'F');

        $loginUrl = config('app.url') . '/login';
        $pdf->Link($btnX, $btn1Y, $btnW, $btnH, $loginUrl);

        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($btnX, $btn1Y + 3.5);
        $pdf->Cell($btnW, 4, 'Masuk Sekarang  →', 0, 0, 'C');

        // ── Tombol "Daftar Gratis" ──
        $btn2Y = $btn1Y + $btnH + 5;

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0.6);
        $pdf->RoundedRect($btnX, $btn2Y, $btnW, $btnH, 2.5, 'DF');

        $registerUrl = config('app.url') . '/register';
        $pdf->Link($btnX, $btn2Y, $btnW, $btnH, $registerUrl);

        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(255, 107, 24);
        $pdf->SetXY($btnX, $btn2Y + 3.5);
        $pdf->Cell($btnW, 4, 'Daftar Gratis', 0, 0, 'C');

        // ── Teks manfaat ──
        $benefitY = $btn2Y + $btnH + 8;
        $benefits = ['✓  Gratis selamanya', '✓  Ribuan publikasi', '✓  Tanpa kartu kredit'];
        $benefitW = $w / count($benefits);

        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetTextColor(115, 115, 115);

        foreach ($benefits as $i => $benefit) {
            $pdf->SetXY($benefitW * $i, $benefitY);
            $pdf->Cell($benefitW, 5, $benefit, 0, 0, 'C');
        }

        // ── URL hint ──
        $pdf->SetFont('Helvetica', '', 6.5);
        $pdf->SetTextColor(180, 180, 180);
        $pdf->SetXY(0, $benefitY + 9);
        $pdf->Cell($w, 4, config('app.url'), 0, 1, 'C');

        // Reset
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
    }

    // ─────────────────────────────────────────────────────────────
    // Header stamp
    // ─────────────────────────────────────────────────────────────
    private static function drawHeaderStamp(
        PdfWithRotation $pdf,
        array $size,
        Publication $publication,
        PublicationVersion $version,
        int $pageNo,
        int $pageCount,
    ): void {
        $margin      = 6;
        $stampWidth  = 75;
        $stampHeight = 22;
        $x           = $size['width'] - $stampWidth - $margin;
        $y           = $margin;

        $uniqueCode = self::generateCode($publication, $version);
        $verifyUrl  = route('document.verify', $uniqueCode);

        // Background
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.2);
        $pdf->RoundedRect($x, $y, $stampWidth, $stampHeight, 2, 'DF');

        // QR Code
        $qrSize    = 18;
        $qrX       = $x + $stampWidth - $qrSize - 2;
        $qrY       = $y + 2;
        $qrPngPath = self::generateQrPng($verifyUrl);

        if ($qrPngPath) {
            $pdf->Image($qrPngPath, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
            @unlink($qrPngPath);
        }

        $textAreaW = $stampWidth - $qrSize - 6;

        // Logo
        $logoPath = public_path('images/logos/logo.png');
        $logoX    = $x + 2;
        $logoY    = $y + 2;
        $logoH    = 6;

        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, $logoX, $logoY, 0, $logoH, 'PNG');
            $textX = $logoX + $logoH * 1.2 + 1;
        } else {
            $textX = $logoX;
        }

        // Nama platform
        $pdf->SetFont('Helvetica', 'B', 5.5);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetXY($textX, $logoY + 0.3);
        $pdf->Cell($textAreaW - ($textX - $x), 3, 'DABRAKA', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 4);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($textX, $logoY + 3.2);
        $pdf->Cell($textAreaW - ($textX - $x), 2.5, 'Darma Brata Buana Cendekia', 0, 1, 'L');

        // Divider
        $divY = $y + 9;
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->SetLineWidth(0.15);
        $pdf->Line($x + 2, $divY, $x + $textAreaW, $divY);

        // Metadata
        $metaY   = $divY + 1.5;
        $colLeft = $x + 2;
        $colW    = $textAreaW - 4;

        $pdf->SetFont('Helvetica', '', 3.8);
        $pdf->SetTextColor(120, 120, 120);

        $pdf->SetXY($colLeft, $metaY);
        $pdf->Cell($colW, 2.5, 'Diakses: ' . now()->format('d/m/Y H:i'), 0, 0, 'L');

        $pdf->SetXY($colLeft, $metaY + 2.5);
        $pdf->Cell($colW / 2, 2.5, 'Versi: ' . ($version->version_number ?? '-'), 0, 0, 'L');
        $pdf->SetXY($colLeft + $colW / 2, $metaY + 2.5);
        $pdf->Cell($colW / 2, 2.5, 'Hal: ' . $pageNo . '/' . $pageCount, 0, 0, 'L');

        $pdf->SetXY($colLeft, $metaY + 5);
        $pdf->SetFont('Helvetica', 'B', 3.8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell($colW, 2.5, 'Kode: ' . $uniqueCode, 0, 0, 'L');

        // Status badge
        $statusText  = strtoupper(str_replace('_', ' ', $publication->status));
        $statusColor = self::STATUS_COLORS[$publication->status] ?? [100, 100, 100];
        $badgeY      = $metaY + 8;

        $pdf->SetFillColor(...$statusColor);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 3.8);
        $pdf->SetXY($colLeft, $badgeY);
        $pdf->Cell($colW, 3, $statusText, 0, 1, 'C', true);

        // Label scan di bawah QR
        $pdf->SetFont('Helvetica', '', 3.2);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($qrX, $qrY + $qrSize + 0.5);
        $pdf->Cell($qrSize, 2.5, 'Scan untuk verifikasi', 0, 0, 'C');

        // Reset
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Side watermark
    // ─────────────────────────────────────────────────────────────
    private static function drawSideWatermark(
        PdfWithRotation $pdf,
        array $size,
        string $status,
    ): void {
        $text = strtoupper(str_replace('_', ' ', $status));

        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetFont('Helvetica', 'B', 28);

        $x = 20;
        $y = $size['height'] / 2 + 25;

        $pdf->RotatedText($x, $y, $text, 90);

        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Generate QR Code
    // ─────────────────────────────────────────────────────────────
    private static function generateQrPng(string $url): ?string
    {
        try {
            $qrCode = new \Endroid\QrCode\QrCode(
                data: $url,
                size: 200,
                margin: 4,
            );

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            $filename = 'qr_' . md5($url) . '.png';
            $path     = storage_path('app/temp/' . $filename);

            if (!is_dir(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            file_put_contents($path, $result->getString());

            return file_exists($path) ? $path : null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('QR Error: ' . $e->getMessage());
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Generate kode unik verifikasi
    // ─────────────────────────────────────────────────────────────
    private static function generateCode(Publication $publication, PublicationVersion $version): string
    {
        $hash = strtoupper(substr(
            hash('sha256', $publication->id . '-' . $version->id . '-' . config('app.key')),
            0,
            6
        ));

        return 'DBK-' . str_pad($publication->id, 4, '0', STR_PAD_LEFT) . '-V' . ($version->version_number ?? '1') . '-' . $hash;
    }
}
