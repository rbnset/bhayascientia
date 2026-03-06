<?php

namespace App\Support;

use App\Models\Publication;
use App\Models\PublicationVersion;

class PdfStamper
{
    /**
     * Status yang mendapat diagonal watermark di tengah halaman.
     */
    private const DIAGONAL_WATERMARK_STATUSES = [
        'submitted',
        'revision_required',
    ];

    /**
     * Warna status badge.
     */
    private const STATUS_COLORS = [
        'draft'             => [156, 163, 175], // gray
        'submitted'         => [245, 158, 11],  // amber
        'in_review'         => [59, 130, 246],  // blue
        'revision_required' => [239, 68, 68],   // red
        'accepted'          => [34, 197, 94],   // green
        'rejected'          => [239, 68, 68],   // red
        'published'         => [16, 185, 129],  // emerald
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

            // ── 1. Header stamp pojok kanan atas ──────────────────────
            self::drawHeaderStamp($pdf, $size, $publication, $version, $pageNo, $pageCount);

            // ── 2. Diagonal watermark (hanya status tertentu) ─────────
            if (in_array($publication->status, self::DIAGONAL_WATERMARK_STATUSES)) {
                self::drawDiagonalWatermark($pdf, $size, $publication->status);
            }
        }

        return $pdf->Output('S');
    }

    // ─────────────────────────────────────────────────────────────
    // Header stamp pojok kanan atas
    // ─────────────────────────────────────────────────────────────
    private static function drawHeaderStamp(
        PdfWithRotation $pdf,
        array $size,
        Publication $publication,
        PublicationVersion $version,
        int $pageNo,
        int $pageCount,
    ): void {
        $margin      = 6;   // mm dari tepi
        $stampWidth  = 58;  // mm lebar kotak stamp
        $stampHeight = 22;  // mm tinggi kotak stamp
        $x           = $size['width'] - $stampWidth - $margin;
        $y           = $margin;

        // ── Background putih semi-transparan ──────────────────────
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(220, 220, 220);
        $pdf->SetLineWidth(0.2);
        $pdf->RoundedRect($x, $y, $stampWidth, $stampHeight, 2, 'DF');

        // ── Logo (jika file tersedia) ──────────────────────────────
        $logoPath = public_path('images/dabraka-logo.png');
        $logoX    = $x + 2;
        $logoY    = $y + 2;
        $logoH    = 6;

        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, $logoX, $logoY, 0, $logoH, 'PNG');
            $textX = $logoX + $logoH * 1.2 + 1;
        } else {
            $textX = $logoX;
        }

        // ── Nama platform ──────────────────────────────────────────
        $pdf->SetFont('Helvetica', 'B', 5.5);
        $pdf->SetTextColor(30, 30, 30);
        $pdf->SetXY($textX, $logoY + 0.3);
        $pdf->Cell($stampWidth - ($textX - $x) - 2, 3, 'DABRAKA', 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 4);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetXY($textX, $logoY + 3.2);
        $pdf->Cell($stampWidth - ($textX - $x) - 2, 2.5, 'Darma Brata Buana Cendekia', 0, 1, 'L');

        // ── Divider ────────────────────────────────────────────────
        $divY = $y + 9;
        $pdf->SetDrawColor(230, 230, 230);
        $pdf->SetLineWidth(0.15);
        $pdf->Line($x + 2, $divY, $x + $stampWidth - 2, $divY);

        // ── Metadata ───────────────────────────────────────────────
        $metaY    = $divY + 1.5;
        $colLeft  = $x + 2;
        $colRight = $x + $stampWidth / 2 + 1;
        $colW     = $stampWidth / 2 - 3;

        $pdf->SetFont('Helvetica', '', 3.8);
        $pdf->SetTextColor(120, 120, 120);

        // Baris 1: Tanggal akses | Versi
        $pdf->SetXY($colLeft, $metaY);
        $pdf->Cell($colW, 2.5, 'Diakses: ' . now()->format('d/m/Y H:i'), 0, 0, 'L');

        $pdf->SetXY($colRight, $metaY);
        $pdf->Cell($colW, 2.5, 'Versi: ' . ($version->version_number ?? '-'), 0, 1, 'L');

        // Baris 2: Kode unik | Halaman
        $uniqueCode = self::generateCode($publication, $version);
        $pdf->SetXY($colLeft, $metaY + 2.8);
        $pdf->Cell($colW, 2.5, 'ID: ' . $uniqueCode, 0, 0, 'L');

        $pdf->SetXY($colRight, $metaY + 2.8);
        $pdf->Cell($colW, 2.5, 'Hal: ' . $pageNo . '/' . $pageCount, 0, 1, 'L');

        // Baris 3: Status badge
        $statusText   = strtoupper(str_replace('_', ' ', $publication->status));
        $statusColor  = self::STATUS_COLORS[$publication->status] ?? [100, 100, 100];
        $badgeY       = $metaY + 5.8;
        $badgeW       = $stampWidth - 4;

        $pdf->SetFillColor(...$statusColor);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 3.8);
        $pdf->SetXY($colLeft, $badgeY);
        $pdf->Cell($badgeW, 3, $statusText, 0, 1, 'C', true);

        // Reset warna
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Diagonal watermark di tengah halaman
    // ─────────────────────────────────────────────────────────────
    private static function drawDiagonalWatermark(
        PdfWithRotation $pdf,
        array $size,
        string $status,
    ): void {
        $text  = strtoupper(str_replace('_', ' ', $status));
        $color = self::STATUS_COLORS[$status] ?? [200, 200, 200];

        $pdf->SetFont('Helvetica', 'B', 42);
        $pdf->SetTextColor($color[0], $color[1], $color[2]);

        // Opacity simulasi dengan warna terang
        $pdf->SetTextColor(
            min(255, $color[0] + 100),
            min(255, $color[1] + 100),
            min(255, $color[2] + 100),
        );

        $pdf->RotatedText(
            $size['width'] / 2 - 40,
            $size['height'] / 2 + 10,
            $text,
            35
        );

        $pdf->SetTextColor(0, 0, 0);
    }

    // ─────────────────────────────────────────────────────────────
    // Generate kode unik untuk penanda dokumen
    // Format: DBK-{pub_id}-V{version}-{hash4}
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
