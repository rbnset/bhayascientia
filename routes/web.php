<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentVerificationController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\OnboardingController;
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
use App\Http\Controllers\TourController;
use App\Models\PublicationVersion;
use App\Support\PdfStamper;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Manuscript Routes
| Hanya user yang login & verified bisa akses PDF
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // ── View manuscript (dengan stamp & watermark) ─────────────
    Route::get('/manuscripts/{version}', function (PublicationVersion $version) {
        $version->loadMissing('publication');

        abort_unless(filled($version->pdf_file_path), 404);

        $absolutePath = Storage::disk('public')->path($version->pdf_file_path);
        abort_unless(is_file($absolutePath), 404);

        try {
            $content = PdfStamper::stamp($absolutePath, $version);

            return response($content, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="manuscript.pdf"',
                'Content-Length'      => strlen($content),
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('PdfStamper fallback: ' . $e->getMessage(), [
                'version_id' => $version->id,
            ]);

            return response()->file($absolutePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="manuscript.pdf"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            ]);
        }
    })->name('manuscripts.view');

    // ── Download manuscript (tanpa stamp, file asli) ───────────
    Route::get('/manuscripts/{version}/download', function (PublicationVersion $version) {
        abort_unless(filled($version->pdf_file_path), 404);

        return Storage::disk('public')->download(
            $version->pdf_file_path,
            'manuscript-v' . ($version->version_number ?? 'x') . '.pdf'
        );
    })->name('manuscripts.download');
});

Route::get('/verify/{code}', [DocumentVerificationController::class, 'verify'])
    ->name('document.verify')
    ->where('code', '[A-Za-z0-9\-]+');

/*
|--------------------------------------------------------------------------
| Onboarding — Guest only, session-based
|--------------------------------------------------------------------------
*/
Route::get('/onboarding', [OnboardingController::class, 'show'])
    ->name('onboarding.show');

Route::post('/onboarding/complete', [OnboardingController::class, 'complete'])
    ->name('onboarding.complete');

/*
|--------------------------------------------------------------------------
| Static Pages
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => view('pages.home'))->name('home');
Route::get('/event', fn() => view('pages.event'))->name('event');

/*
|--------------------------------------------------------------------------
| Auth Routes (Guest Only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');

    // ✅ Uncomment: login & register POST
    Route::post('/login',    [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login.post');

    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.post');

    // Google OAuth
    Route::get('auth/google',          [GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    // Facebook OAuth
    Route::get('auth/facebook',          [GoogleAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
    Route::get('auth/facebook/callback', [GoogleAuthController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Dashboard Redirect
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    return redirect()->route('publikasi.library');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| OTP Verification
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/verify-email',         [OtpController::class, 'show'])->name('otp.show');
    Route::post('/verify-email',        [OtpController::class, 'verify'])
        ->middleware('throttle:otp')
        ->name('otp.verify');
    Route::post('/verify-email/resend', [OtpController::class, 'resend'])
        ->middleware('throttle:otp-resend')
        ->name('otp.resend');
});

/*
|--------------------------------------------------------------------------
| Publikasi Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('publikasi')->name('publikasi.')->group(function () {
    Route::get('/',         [PublicationIndexController::class,    'index'])->name('index');
    Route::get('/jelajahi', [PublicationBrowseController::class,   'browse'])->name('browse');
    Route::get('/search',   [PublicationSearchController::class,   'search'])->name('search');
    Route::get('/trending', [PublicationTrendingController::class, 'trending'])->name('trending');
    Route::get('/library',  [PublicationLibraryController::class,  'library'])->name('library');

    Route::get('/kategori',                [PublicationCategoriesController::class, 'categories'])->name('category');
    Route::get('/kategori/{categorySlug}', [PublicationCategoriesController::class, 'categories'])->name('category.show');

    // ⚠️ Wildcard PALING BAWAH
    Route::get('/{slug}',          [PublicationController::class, 'show'])->name('show');
    Route::get('/{slug}/download', [PublicationController::class, 'download'])->name('download');
    Route::get('/{slug}/read',     [PublicationController::class, 'read'])->name('read');
    Route::get('/{slug}/pdf',      [PublicationController::class, 'servePdf'])->name('pdf'); // ✅ BARU: serve PDF dengan header benar
});

// Favorite & Save (auth + verified)
Route::middleware(['auth', 'verified.otp'])->group(function () {
    Route::post('/publikasi/{slug}/favorite', [PublicationController::class, 'toggleFavorite'])->name('publikasi.favorite');
    Route::post('/publikasi/{slug}/save',     [PublicationController::class, 'toggleSaved'])->name('publikasi.save');
});

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

Route::post('/kontak', [ContactController::class, 'submit'])
    ->middleware('throttle:kontak')
    ->name('kontak.submit');

/*
|--------------------------------------------------------------------------
| About & Legal
|--------------------------------------------------------------------------
*/
Route::get('/tentang', [AboutController::class, 'index'])->name('tentang');

Route::controller(LegalController::class)->group(function () {
    Route::get('/privacy-policy',   'privacyPolicy')->name('privacy-policy');
    Route::get('/terms-conditions', 'termsConditions')->name('terms-conditions');
});

Route::get('/submission-guidelines', [SubmissionGuidelineController::class, 'index'])
    ->name('submission-guidelines');

/*
|--------------------------------------------------------------------------
| Authenticated + Verified Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.otp'])->group(function () {

    // ── Subscription ──────────────────────────────────────────────────────
    Route::get('/langganan',             [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/langganan',            [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::put('/langganan',             [SubscriptionController::class, 'update'])->name('subscription.update');
    Route::delete('/langganan',          [SubscriptionController::class, 'destroy'])->name('subscription.destroy');
    Route::post('/langganan/reactivate', [SubscriptionController::class, 'reactivate'])->name('subscription.reactivate');
    Route::get('/langganan/categories',  [SubscriptionController::class, 'getCategoriesAjax'])->name('subscription.categories');

    // ── Profile ───────────────────────────────────────────────────────────
    Route::get('/profil-saya',                  [ProfileController::class, 'index'])->name('profil.saya');
    Route::post('/profil-saya/update',          [ProfileController::class, 'update'])->name('profil.update');
    Route::post('/profil-saya/update-photo',    [ProfileController::class, 'updatePhoto'])->name('profil.updatePhoto');
    Route::delete('/profil-saya/delete-photo',  [ProfileController::class, 'deletePhoto'])->name('profil.deletePhoto');
    Route::post('/profil-saya/update-password', [ProfileController::class, 'updatePassword'])->name('profil.updatePassword');
});

/*
|--------------------------------------------------------------------------
| ✅ Product Tour — Cookie-based, simpan per halaman
|--------------------------------------------------------------------------
*/
Route::post('/tour/complete/{page}', [TourController::class, 'complete'])
    ->name('tour.complete')
    ->whereIn('page', ['index', 'browse', 'detail']);

/*
|--------------------------------------------------------------------------
| Utility / Dev Routes
|--------------------------------------------------------------------------
*/
// Route::get('/test-card', fn() => view('test-card'));

Route::get('/placeholder-image', [PlaceholderImageController::class, 'generate'])->name('placeholder.image');
Route::get('/placeholder-cover', [PlaceholderCoverController::class, 'generate'])->name('placeholder.cover');
