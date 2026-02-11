<?php

use App\Http\Controllers\PublicationController;
use App\Http\Controllers\PublikasiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SubscriptionController;
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
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', function () {
        return redirect()->route('publikasi.library');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Publikasi Routes
|--------------------------------------------------------------------------
*/
Route::prefix('publikasi')->name('publikasi.')->group(function () {
    Route::get('/', [PublicationController::class, 'index'])->name('index');

    Route::get('/jelajahi', [PublicationController::class, 'browse'])->name('browse');
    Route::get('/search', [PublicationController::class, 'search'])->name('search');
    Route::get('/categories', [PublicationController::class, 'categories'])->name('categories');
    Route::get('/trending', [PublicationController::class, 'trending'])->name('trending');
    Route::get('/library', [PublicationController::class, 'library'])->name('library');
    Route::get('/{slug}', [PublicationController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [PublicationController::class, 'download'])->name('download');
    Route::get('/{slug}/read', [PublicationController::class, 'read'])->name('read');
});

Route::get('/author/{identifier}', [AuthorController::class, 'show'])->name('author.profile');


Route::post('/publikasi/{slug}/favorite', [PublicationController::class, 'toggleFavorite'])
    ->name('publikasi.favorite');

Route::post('/publikasi/{slug}/save', [PublicationController::class, 'toggleSaved'])
    ->name('publikasi.save');

Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');
Route::post('/kontak', [ContactController::class, 'submit'])->name('kontak.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::put('/subscription', [SubscriptionController::class, 'update'])->name('subscription.update');
    Route::delete('/subscription', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
    Route::post('/subscription/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');

    // ✅ AJAX endpoint untuk dynamic category filtering
    Route::post('/subscription/get-categories', [SubscriptionController::class, 'getCategories'])->name('subscription.getCategories');
});
