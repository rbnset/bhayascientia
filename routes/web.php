<?php

use App\Models\PublicationVersion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Support\PdfWithRotation;

Route::get('/manuscripts/{version}', function (PublicationVersion $version) {

    abort_unless(auth()->check(), 403);

    $publication = $version->publication;
    $path = Storage::disk('public')->path($version->pdf_file_path);

    abort_unless(file_exists($path), 404);

    return response()->stream(function () use ($path, $publication) {

        $pdf = new \App\Support\PdfWithRotation();
        $pageCount = $pdf->setSourceFile($path);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

            $tplId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            if (in_array($publication->status, [
                'submitted',
                'revision_required',
            ])) {

                $pdf->SetFont('Helvetica', 'B', 40);
                $pdf->SetTextColor(210, 210, 210);

                $text = strtoupper(str_replace('_', ' ', $publication->status));

                $pdf->RotatedText(
                    $size['width'] / 2 - 80,
                    $size['height'] / 2,
                    $text,
                    45
                );
            }
        }

        $pdf->Output('I');
    }, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="manuscript.pdf"',
    ]);
})->name('manuscripts.view');
