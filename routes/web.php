<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\PlaceholderCoverController;
use App\Http\Controllers\PlaceholderImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Publication\PublicationBrowseController;
use App\Http\Controllers\Publication\PublicationCategoriesController;
use App\Http\Controllers\Publication\PublicationIndexController;
use App\Http\Controllers\Publication\PublicationLibraryController;
use App\Http\Controllers\Publication\PublicationSearchController;
use App\Http\Controllers\Publication\PublicationTrendingController;
use App\Http\Controllers\SubmissionGuidelineController;
use App\Http\Controllers\SubscriptionController;
use App\Models\PublicationVersion;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Manuscript Routes
|--------------------------------------------------------------------------
*/

Route::get('/manuscripts/{version}', function (PublicationVersion $version) {
    abort_unless(auth()->check(), 403);

    $version->loadMissing('publication');
    abort_unless(filled($version->pdf_file_path), 404);

    $absolutePath = Storage::disk('public')->path($version->pdf_file_path);
    abort_unless(is_file($absolutePath), 404);

    $publication = $version->publication;

    $pdf       = new \App\Support\PdfWithRotation();
    $pageCount = $pdf->setSourceFile($absolutePath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplId = $pdf->importPage($pageNo);
        $size  = $pdf->getTemplateSize($tplId);

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

    $content = $pdf->Output('S');

    return response($content, 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'inline; filename="manuscript.pdf"',
        'Content-Length'      => strlen($content),
        'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
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
| Static Pages
|--------------------------------------------------------------------------
*/
Route::view('/', 'pages.home')->name('home');
Route::view('/event', 'pages.event')->name('event');

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('auth/facebook', [GoogleAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('auth/facebook/callback', [GoogleAuthController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Authenticated Only)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', function () {
        return redirect()->route('publikasi.library');
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Publikasi Routes
| ⚠️ URUTAN PENTING: specific routes SEBELUM wildcard {slug}
|--------------------------------------------------------------------------
*/
Route::prefix('publikasi')->name('publikasi.')->group(function () {
    Route::get('/', [PublicationIndexController::class, 'index'])->name('index');
    Route::get('/jelajahi', [PublicationBrowseController::class, 'browse'])->name('browse');
    Route::get('/search', [PublicationSearchController::class, 'search'])->name('search');
    Route::get('/trending', [PublicationTrendingController::class, 'trending'])->name('trending');
    Route::get('/library', [PublicationLibraryController::class, 'library'])->name('library');

    Route::get('/kategori', [PublicationCategoriesController::class, 'categories'])->name('category');
    Route::get('/kategori/{categorySlug}', [PublicationCategoriesController::class, 'categories'])->name('category.show');

    // ⚠️ Wildcard PALING BAWAH
    Route::get('/{slug}', [PublicationController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [PublicationController::class, 'download'])->name('download');
    Route::get('/{slug}/read', [PublicationController::class, 'read'])->name('read');
});

// Favorite & Save
Route::post('/publikasi/{slug}/favorite', [PublicationController::class, 'toggleFavorite'])->name('publikasi.favorite');
Route::post('/publikasi/{slug}/save', [PublicationController::class, 'toggleSaved'])->name('publikasi.save');

/*
|--------------------------------------------------------------------------
| Author
|--------------------------------------------------------------------------
*/
Route::get('/author/{identifier}', [AuthorController::class, 'show'])->name('author.profile');

/*
|--------------------------------------------------------------------------
| Contact
|--------------------------------------------------------------------------
*/
Route::get('/kontak', [ContactController::class, 'index'])->name('kontak');
Route::post('/kontak', [ContactController::class, 'submit'])->name('kontak.submit');

/*
|--------------------------------------------------------------------------
| About & Legal
|--------------------------------------------------------------------------
*/
Route::get('/tentang', [AboutController::class, 'index'])->name('tentang');

Route::controller(LegalController::class)->group(function () {
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacy-policy');
    Route::get('/terms-conditions', 'termsConditions')->name('terms-conditions');
});

Route::get('/submission-guidelines', [SubmissionGuidelineController::class, 'index'])
    ->name('submission-guidelines');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // ── Subscription ────────────────────────────────────────────────────────
    Route::get('/langganan', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/langganan', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::put('/langganan', [SubscriptionController::class, 'update'])->name('subscription.update');
    Route::delete('/langganan', [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
    Route::post('/langganan/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');

    // AJAX — ⚠️ harus di atas /langganan/{wildcard} jika ada
    Route::get('/langganan/categories', [SubscriptionController::class, 'getCategoriesAjax'])->name('subscription.categories');

    // ── Profile ─────────────────────────────────────────────────────────────
    Route::get('/profil-saya', [ProfileController::class, 'index'])->name('profil.saya');
    Route::post('/profil-saya/update', [ProfileController::class, 'update'])->name('profil.update');
    Route::post('/profil-saya/update-photo', [ProfileController::class, 'updatePhoto'])->name('profil.updatePhoto');
    Route::delete('/profil-saya/delete-photo', [ProfileController::class, 'deletePhoto'])->name('profil.deletePhoto');
    Route::post('/profil-saya/update-password', [ProfileController::class, 'updatePassword'])->name('profil.updatePassword');
});

/*
|--------------------------------------------------------------------------
| Utility / Dev Routes
|--------------------------------------------------------------------------
*/
Route::get('/test-card', fn() => view('test-card'));
Route::get('/placeholder-image', [PlaceholderImageController::class, 'generate'])->name('placeholder.image');
Route::get('/placeholder-cover', [PlaceholderCoverController::class, 'generate'])->name('placeholder.cover');

/*
|--------------------------------------------------------------------------
| TEMPORARY — Preview Email (Hapus setelah testing!)
|--------------------------------------------------------------------------
*/
// Route::get('/preview-autoreply', function () {
//     $data = [
//         'name'    => 'Robin Setiyawan',
//         'email'   => 'rbn.setiyawan@gmail.com',
//         'phone'   => '085669877959',
//         'subject' => 'Saran Fitur Platform',
//         'message' => "Halo tim DABRAKA,\n\nSaya ingin memberikan saran terkait fitur pencarian publikasi.\n\nTerima kasih.",
//     ];
//     return view('emails.contact-autoreply', compact('data'));
// });

// Route::get('/preview-admin', function () {
//     $data = [
//         'name'    => 'Robin Setiyawan',
//         'email'   => 'rbn.setiyawan@gmail.com',
//         'phone'   => '085669877959',
//         'subject' => 'Saran Fitur Platform',
//         'message' => "Halo tim DABRAKA,\n\nSaya ingin memberikan saran terkait fitur pencarian publikasi.\n\nTerima kasih.",
//     ];
//     return view('emails.contact', compact('data'));
// });


// // TEMPORARY — hapus setelah testing!
// Route::get('/preview-langganan', function () {
//     return app(App\Http\Controllers\SubscriptionController::class)->index();
// });

// // TEMPORARY — hapus setelah testing!
// Route::get('/preview-email-subscription', function () {
//     $user = App\Models\User::first();
//     $publications = App\Models\Publication::where('status', 'published')
//         ->latest('published_at')
//         ->take(5)
//         ->get();

//     return view('emails.subscription-digest', compact('user', 'publications'));
// });

// TEMPORARY — hapus setelah testing!
Route::get('/preview-digest/{type}', function (string $type) {
    $subscription = App\Models\Subscription::with('user')->active()->first();

    abort_unless($subscription, 404, 'Belum ada subscriber aktif.');

    $publications = App\Models\Publication::with(['publicationType', 'categories', 'authors'])
        ->where('status', 'published')
        ->latest('published_at')
        ->take(5)
        ->get();

    return new App\Mail\SubscriptionDigestMail(
        subscription: $subscription,
        publications: $publications,
        digestType: $type,
        periodLabel: 'Preview – ' . now()->format('d M Y'),
    );
})->middleware('auth');
