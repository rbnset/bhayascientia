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
        'submitted'         => [245, 158,  11],
        'in_review'         => [59, 130, 246],
        'revision_required' => [239,  68,  68],
        'accepted'          => [34, 197,  94],
        'rejected'          => [239,  68,  68],
        'published'         => [16, 185, 129],
    ];

    private const GUEST_PAGE_LIMITS = [
        'jurnal' => 3,
        'buku'   => 10,
        'opini'  => 1,
    ];

    private const DEFAULT_GUEST_LIMIT = 3;

    // Tinggi footer stamp dalam mm — cukup kecil agar tidak memakan konten
    private const FOOTER_HEIGHT = 14;

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

            $pagesToRender = ($pageLimit !== null) ? min($pageLimit, $pageCount) : $pageCount;
            $isLimited     = ($pageLimit !== null) && ($pageCount > $pageLimit);

            for ($pageNo = 1; $pageNo <= $pagesToRender; $pageNo++) {
                $tplId = $pdf->importPage($pageNo);
                $size  = $pdf->getTemplateSize($tplId);

                $fh = self::FOOTER_HEIGHT;

                // ✅ Buat halaman lebih tinggi sebesar footer agar PDF asli
                // tidak tertimpa — konten PDF dirender di area atas,
                // footer stamp di area bawah yang kosong.
                $pageH = $size['height'] + $fh;
                $pdf->AddPage($size['orientation'], [$size['width'], $pageH]);

                // Render PDF asli di bagian atas (tidak menyentuh area footer)
                $pdf->useTemplate($tplId, 0, 0, $size['width'], $size['height']);

                // Kirim ukuran halaman total ke drawFooterStamp
                $fullSize = ['width' => $size['width'], 'height' => $pageH, 'orientation' => $size['orientation']];
                self::drawFooterStamp($pdf, $fullSize, $publication, $version, $pageNo, $pageCount);

                // Watermark & side stamp pakai $size asli (area konten PDF saja)
                // agar tidak melebar ke area footer
                if (in_array($publication->status, self::SIDE_WATERMARK_STATUSES)) {
                    self::drawSideWatermark($pdf, $size, $publication->status);
                }

                if ($isGuest) {
                    self::drawGuestWatermark($pdf, $size);
                }
            }

            if ($isLimited) {
                $lastTplId    = $pdf->importPage($pagesToRender);
                $lastPageSize = $pdf->getTemplateSize($lastTplId);
                // CTA page pakai ukuran penuh (tanpa tambahan footer height)
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
    // FOOTER STAMP — memanjang full-width di bawah setiap halaman
    //
    // Layout:
    //  [aksen oranye] | [logo + nama] | [kode + tanggal + hint] | [hal + status] | [QR]
    // ─────────────────────────────────────────────────────────────
    private static function drawFooterStamp(
        PdfWithRotation $pdf,
        array $size,
        Publication $publication,
        PublicationVersion $version,
        int $pageNo,
        int $pageCount,
    ): void {
        $w   = $size['width'];
        $fh  = self::FOOTER_HEIGHT;
        $fy  = $size['height'] - $fh;
        $px  = 3;

        $uniqueCode = self::generateCode($publication, $version);
        $verifyUrl  = route('document.verify', $uniqueCode);

        // ── Garis pemisah atas ────────────────────────────────────
        $pdf->SetDrawColor(210, 210, 210);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(0, $fy, $w, $fy);

        // ── Background footer ─────────────────────────────────────
        $pdf->SetFillColor(252, 252, 253);
        $pdf->Rect(0, $fy, $w, $fh, 'F');

        // ── Aksen oranye kiri (2mm) ───────────────────────────────
        $pdf->SetFillColor(255, 107, 24);
        $pdf->Rect(0, $fy, 2, $fh, 'F');

        // ── QR di kanan ───────────────────────────────────────────
        $qrSize    = $fh - 2;
        $qrX       = $w - $qrSize - $px;
        $qrY       = $fy + 1;
        $qrPngPath = self::generateQrPng($verifyUrl);

        if ($qrPngPath) {
            $pdf->Image($qrPngPath, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
            @unlink($qrPngPath);
        }

        // ── Logo + nama platform ───────────────────────────────────
        $curX    = $px + 3;
        $logoY   = $fy + ($fh - 6) / 2;
        $logoPath = public_path('assets/images/logos/logo.png');

        if (file_exists($logoPath)) {
            $logoH = 6;
            $pdf->Image($logoPath, $curX, $logoY, 0, $logoH, 'PNG');
            $curX += $logoH * 1.5 + 1;
        }

        $pdf->SetFont('Helvetica', 'B', 5.5);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->SetXY($curX, $fy + 2.5);
        $pdf->Cell(22, 3, 'DABRAKA', 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 3.8);
        $pdf->SetTextColor(130, 130, 130);
        $pdf->SetXY($curX, $fy + 5.8);
        $pdf->Cell(22, 2.5, 'dabraka.org', 0, 0, 'L');

        // ── Divider vertikal 1 ─────────────────────────────────────
        $divX1 = $curX + 24;
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.15);
        $pdf->Line($divX1, $fy + 2, $divX1, $fy + $fh - 2);

        // ── Info kode + tanggal ────────────────────────────────────
        $infoX = $divX1 + 3;

        $pdf->SetFont('Helvetica', 'B', 4);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetXY($infoX, $fy + 2.2);
        $pdf->Cell(58, 2.8, 'Kode Verifikasi: ' . $uniqueCode, 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 3.8);
        $pdf->SetTextColor(130, 130, 130);
        $pdf->SetXY($infoX, $fy + 5.5);
        $pdf->Cell(58, 2.5, 'Diakses: ' . now()->format('d/m/Y H:i') . '   Versi ' . ($version->version_number ?? '1'), 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 3.5);
        $pdf->SetTextColor(160, 160, 160);
        $pdf->SetXY($infoX, $fy + 8.5);
        $pdf->Cell(58, 2.5, 'Scan QR untuk verifikasi keaslian dokumen ini', 0, 0, 'L');

        // ── Divider vertikal 2 ─────────────────────────────────────
        $divX2 = $infoX + 62;
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.15);
        $pdf->Line($divX2, $fy + 2, $divX2, $fy + $fh - 2);

        // ── Halaman + status badge ─────────────────────────────────
        $rightColX = $divX2 + 2;
        $rightColW = $qrX - $rightColX - 2;

        $pdf->SetFont('Helvetica', 'B', 7);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->SetXY($rightColX, $fy + 1.8);
        $pdf->Cell($rightColW, 4.5, $pageNo . ' / ' . $pageCount, 0, 0, 'C');

        $pdf->SetFont('Helvetica', '', 3.3);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($rightColX, $fy + 6);
        $pdf->Cell($rightColW, 2.5, 'Halaman', 0, 0, 'C');

        $statusText  = strtoupper(str_replace('_', ' ', $publication->status));
        $statusColor = self::STATUS_COLORS[$publication->status] ?? [100, 100, 100];

        $pdf->SetFillColor(...$statusColor);
        $pdf->RoundedRect($rightColX, $fy + 9.2, $rightColW, 3.2, 1, 'F');

        $pdf->SetFont('Helvetica', 'B', 3.2);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($rightColX, $fy + 9.8);
        $pdf->Cell($rightColW, 2, $statusText, 0, 0, 'C');

        // Reset
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
    }

    // ─────────────────────────────────────────────────────────────
    // Guest watermark — diagonal di tengah halaman
    // ─────────────────────────────────────────────────────────────
    private static function drawGuestWatermark(PdfWithRotation $pdf, array $size): void
    {
        $centerX = $size['width'] / 2;
        $centerY = $size['height'] / 2;

        $pdf->SetFont('Helvetica', 'B', 42);
        $pdf->SetTextColor(220, 220, 220);
        $pdf->RotatedText($centerX - 30, $centerY + 15, 'PREVIEW', 45);

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetTextColor(200, 200, 200);
        $pdf->RotatedText($centerX - 38, $centerY + 28, 'Login untuk akses penuh', 45);

        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Halaman CTA login untuk guest
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

        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect(0, 0, $w, $h, 'F');

        $pdf->SetFillColor(255, 107, 24);
        $pdf->Rect(0, 0, $w, 5, 'F');
        $pdf->Rect(0, $h - 5, $w, 5, 'F');

        $centerX = $w / 2;
        $startY  = $h * 0.22;
        $iconCY  = $startY + 14;

        // Lingkaran ikon
        $pdf->SetFillColor(255, 237, 213);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0.8);
        $pdf->Circle($centerX, $iconCY, 16);

        // Gembok
        $lbW = 12;
        $lbH = 9;
        $lbX = $centerX - $lbW / 2;
        $lbY = $iconCY + 2;
        $pdf->SetFillColor(255, 107, 24);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0);
        $pdf->RoundedRect($lbX, $lbY, $lbW, $lbH, 1.5, 'F');
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(2.2);
        $pdf->Arc($centerX, $lbY + 0.5, 4.5, 190, 350);
        $pdf->SetFillColor(255, 237, 213);
        $pdf->SetLineWidth(0);
        $pdf->Circle($centerX, $lbY + 4.5, 1.8);

        // Teks
        $titleY = $iconCY + 22;
        $pdf->SetFont('Helvetica', 'B', 18);
        $pdf->SetTextColor(26, 26, 26);
        $pdf->SetXY(0, $titleY);
        $pdf->Cell($w, 9, 'Pratinjau Berakhir', 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 9.5);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($w * 0.15, $titleY + 11);
        $pdf->Cell($w * 0.7, 5.5, 'Kamu telah membaca ' . $shownPages . ' dari ' . $totalPages . ' halaman.', 0, 1, 'C');
        $pdf->SetXY($w * 0.15, $titleY + 17);
        $pdf->Cell($w * 0.7, 5.5, 'Login untuk melanjutkan membaca secara gratis.', 0, 1, 'C');

        // Stats
        $statsY  = $titleY + 28;
        $statW   = $w * 0.22;
        $statGap = ($w - $statW * 3) / 4;
        $stats   = [
            ['Dibaca',  $shownPages . ' hal'],
            ['Tersisa', ($totalPages - $shownPages) . ' hal'],
            ['Total',   $totalPages . ' hal'],
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

        // Divider
        $divY = $statsY + 20;
        $pdf->SetDrawColor(238, 240, 247);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($w * 0.15, $divY, $w * 0.85, $divY);

        // Tombol
        $btnW = 65;
        $btnH = 11;
        $btnX = ($w - $btnW) / 2;
        $btn1Y = $divY + 8;

        $pdf->SetFillColor(255, 107, 24);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0);
        $pdf->RoundedRect($btnX, $btn1Y, $btnW, $btnH, 2.5, 'F');
        $pdf->Link($btnX, $btn1Y, $btnW, $btnH, config('app.url') . '/login');
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY($btnX, $btn1Y + 3.5);
        $pdf->Cell($btnW, 4, 'Masuk Sekarang  ->', 0, 0, 'C');

        $btn2Y = $btn1Y + $btnH + 5;
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(255, 107, 24);
        $pdf->SetLineWidth(0.6);
        $pdf->RoundedRect($btnX, $btn2Y, $btnW, $btnH, 2.5, 'DF');
        $pdf->Link($btnX, $btn2Y, $btnW, $btnH, config('app.url') . '/register');
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(255, 107, 24);
        $pdf->SetXY($btnX, $btn2Y + 3.5);
        $pdf->Cell($btnW, 4, 'Daftar Gratis', 0, 0, 'C');

        // Manfaat
        $benefitY = $btn2Y + $btnH + 8;
        $benefits = ['Gratis selamanya', 'Ribuan publikasi', 'Tanpa kartu kredit'];
        $benefitW = $w / count($benefits);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->SetTextColor(115, 115, 115);
        foreach ($benefits as $i => $b) {
            $pdf->SetXY($benefitW * $i, $benefitY);
            $pdf->Cell($benefitW, 5, '✓  ' . $b, 0, 0, 'C');
        }

        $pdf->SetFont('Helvetica', '', 6.5);
        $pdf->SetTextColor(180, 180, 180);
        $pdf->SetXY(0, $benefitY + 9);
        $pdf->Cell($w, 4, config('app.url'), 0, 1, 'C');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
    }

    // ─────────────────────────────────────────────────────────────
    // Side watermark (status draft/revision)
    // ─────────────────────────────────────────────────────────────
    private static function drawSideWatermark(PdfWithRotation $pdf, array $size, string $status): void
    {
        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetFont('Helvetica', 'B', 28);
        $pdf->RotatedText(20, $size['height'] / 2 + 25, strtoupper(str_replace('_', ' ', $status)), 90);
        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Generate QR Code PNG
    // ─────────────────────────────────────────────────────────────
    private static function generateQrPng(string $url): ?string
    {
        try {
            $qrCode = new \Endroid\QrCode\QrCode(data: $url, size: 200, margin: 4);
            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            $path = storage_path('app/temp/qr_' . md5($url) . '.png');

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
