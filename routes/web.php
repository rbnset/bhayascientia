<?php

use App\Http\Controllers\PublicationController;
use App\Http\Controllers\PublikasiController;
use App\Models\PublicationVersion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/manuscripts/{version}', function (PublicationVersion $version) {
    abort_unless(auth()->check(), 403);

    $version->loadMissing('publication');

    abort_unless(filled($version->pdf_file_path), 404);

    // Pastikan path di DB itu RELATIF terhadap disk public, mis: "manuscripts/abc.pdf"
    $absolutePath = Storage::disk('public')->path($version->pdf_file_path);

    abort_unless(is_file($absolutePath), 404);

    $publication = $version->publication;

    // Buat PDF hasil watermark/rotation lalu ambil sebagai STRING
    $pdf = new \App\Support\PdfWithRotation();
    $pageCount = $pdf->setSourceFile($absolutePath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId);

        if (in_array($publication->status, ['submitted', 'revision_required'])) {
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

    // 'S' = return as string (lebih aman untuk Laravel response)
    $content = $pdf->Output('S');

    return response($content, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="manuscript.pdf"',
        'Content-Length' => strlen($content),
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
})->name('manuscripts.view');

Route::get('/manuscripts/{version}/download', function (PublicationVersion $version) {
    abort_unless(auth()->check(), 403);

    abort_unless(filled($version->pdf_file_path), 404);

    return Storage::disk('public')->download(
        $version->pdf_file_path,
        'manuscript-v' . ($version->version_number ?? 'x') . '.pdf'
    );
})->name('manuscripts.download');







/*
|--------------------------------------------------------------------------
| Static Pages Routes
|--------------------------------------------------------------------------
*/
Route::view('/', 'pages.home')->name('home');
Route::view('/event', 'pages.event')->name('event');
Route::view('/tentang', 'pages.about')->name('tentang');
Route::view('/kontak', 'pages.contact')->name('kontak');

/*
|--------------------------------------------------------------------------
| Publikasi Routes
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Publikasi Routes
|--------------------------------------------------------------------------
*/
Route::prefix('publikasi')->name('publikasi.')->group(function () {
    Route::get('/', [PublicationController::class, 'index'])->name('index');
    Route::get('/categories', [PublicationController::class, 'categories'])->name('categories');
    Route::get('/trending', [PublicationController::class, 'trending'])->name('trending');
    Route::get('/library', [PublicationController::class, 'library'])->name('library');
    Route::get('/{slug}', [PublicationController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [PublicationController::class, 'download'])->name('download');
    Route::get('/{slug}/read', [PublicationController::class, 'read'])->name('read');
});


/*
|--------------------------------------------------------------------------
| Profile Routes (untuk nanti)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::view('/profile', 'pages.profile')->name('profile');
});

Route::get('/author/{id}', [PublicationController::class, 'showAuthor'])->name('author.show');
