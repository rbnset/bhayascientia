<?php

namespace App\Support;

use App\Models\Publication;
use App\Models\PublicationVersion;
use SimpleSoftwareIO\QrCode\Generator as QrGenerator;

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

    public static function stamp(string $absolutePath, PublicationVersion $version): string
    {
        $version->loadMissing('publication');
        $publication = $version->publication;

        $pdf       = new PdfWithRotation();
        $pageCount = $pdf->setSourceFile($absolutePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplId = $pdf->importPage($pageNo);
            $size  = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            self::drawHeaderStamp($pdf, $size, $publication, $version, $pageNo, $pageCount);

            if (in_array($publication->status, self::SIDE_WATERMARK_STATUSES)) {
                self::drawSideWatermark($pdf, $size, $publication->status);
            }
        }

        return $pdf->Output('S');
    }

    // ─────────────────────────────────────────────────────────────
    // Header stamp — sekarang lebih lebar untuk muat QR Code
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
        $stampWidth  = 75;   // diperlebar dari 58 → 75 untuk muat QR
        $stampHeight = 22;
        $x           = $size['width'] - $stampWidth - $margin;
        $y           = $margin;

        $uniqueCode  = self::generateCode($publication, $version);
        $verifyUrl   = route('document.verify', $uniqueCode);

        // ── Background ──────────────────────────────────────────
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.2);
        $pdf->RoundedRect($x, $y, $stampWidth, $stampHeight, 2, 'DF');

        // ── QR Code (kanan dalam stamp) ──────────────────────────
        $qrSize    = 18;  // mm — seukuran tinggi stamp
        $qrX       = $x + $stampWidth - $qrSize - 2;
        $qrY       = $y + 2;
        $qrPngPath = self::generateQrPng($verifyUrl);

        if ($qrPngPath) {
            $pdf->Image($qrPngPath, $qrX, $qrY, $qrSize, $qrSize, 'PNG');
            @unlink($qrPngPath); // hapus tmp file setelah dipakai
        }

        // Lebar area teks (kiri QR)
        $textAreaW = $stampWidth - $qrSize - 6;

        // ── Logo ─────────────────────────────────────────────────
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

        // ── Nama platform ─────────────────────────────────────────
        $pdf->SetFont('Helvetica', 'B', 5.5);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetXY($textX, $logoY + 0.3);
        $pdf->Cell($textAreaW - ($textX - $x), 3, 'DABRAKA', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 4);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($textX, $logoY + 3.2);
        $pdf->Cell($textAreaW - ($textX - $x), 2.5, 'Darma Brata Buana Cendekia', 0, 1, 'L');

        // ── Divider ───────────────────────────────────────────────
        $divY = $y + 9;
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->SetLineWidth(0.15);
        $pdf->Line($x + 2, $divY, $x + $textAreaW, $divY);

        // ── Metadata ──────────────────────────────────────────────
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

        // ── Status badge ──────────────────────────────────────────
        $statusText  = strtoupper(str_replace('_', ' ', $publication->status));
        $statusColor = self::STATUS_COLORS[$publication->status] ?? [100, 100, 100];
        $badgeY      = $metaY + 8;

        $pdf->SetFillColor(...$statusColor);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 3.8);
        $pdf->SetXY($colLeft, $badgeY);
        $pdf->Cell($colW, 3, $statusText, 0, 1, 'C', true);

        // ── Label scan di bawah QR ────────────────────────────────
        $pdf->SetFont('Helvetica', '', 3.2);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->SetXY($qrX, $qrY + $qrSize + 0.5);
        $pdf->Cell($qrSize, 2.5, 'Scan untuk verifikasi', 0, 0, 'C');

        // ── Reset ─────────────────────────────────────────────────
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
        $text  = strtoupper(str_replace('_', ' ', $status));

        $pdf->SetTextColor(230, 230, 230);
        $pdf->SetFont('Helvetica', 'B', 28);

        $x = 20;
        $y = $size['height'] / 2 + 25;

        $pdf->RotatedText($x, $y, $text, 90);

        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Generate QR Code → simpan ke tmp → return path
    // ─────────────────────────────────────────────────────────────
    private static function generateQrPng(string $url): ?string
    {
        try {
            $qrCode = \Endroid\QrCode\QrCode::create($url)
                ->setSize(200)
                ->setMargin(4);

            $writer = new \Endroid\QrCode\Writer\PngWriter();
            $result = $writer->write($qrCode);

            $path = sys_get_temp_dir() . '/qr_' . md5($url) . '.png';
            $result->saveToFile($path);

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Generate kode unik
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
