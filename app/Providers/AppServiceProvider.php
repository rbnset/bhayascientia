<?php

namespace App\Providers;

use App\Actions\Author\GetBestAuthorsAction;
use App\Models\Author;
use App\Models\Publication;
use App\Models\User;
use App\Observers\PublicationObserver;
use App\Observers\UserRoleObserver;
use App\Repositories\AuthorRepository;
use App\Services\AuthorService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\RoleAssigned;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthorService::class);
        $this->app->singleton(AuthorRepository::class);
        $this->app->singleton(GetBestAuthorsAction::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── HTTPS Enforce (production only) ──────────────────────────────────
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // ── Observer: auto-generate PDF cache saat publikasi di-publish ───────
        Publication::observe(PublicationObserver::class);

        // ── Observer: menangani event saved/updated biasa pada User ───────────
        User::observe(UserRoleObserver::class);

        // ── Spatie v6: auto-create author profile saat role author di-assign ──
        // Event RoleAssigned fired setiap kali assignRole() / syncRoles() dipanggil
        // $event->role berisi object Role yang di-assign (ada property ->name)
        Event::listen(RoleAssigned::class, function (RoleAssigned $event) {
            $user = $event->model;

            // Pastikan yang di-assign adalah User, bukan model lain
            if (! $user instanceof User) {
                return;
            }

            // Cek spesifik role 'author' yang di-assign — lebih efisien
            // daripada refresh + hasRole() yang query ulang semua roles
            if ($event->role->name !== 'author') {
                return;
            }

            // Jangan buat duplikat jika author profile sudah ada
            if ($user->authorProfile()->exists()) {
                return;
            }

            Author::create([
                'user_id'     => $user->id,
                'name'        => null,
                'email'       => null,
                'affiliation' => null,
                'bio'         => null,
                'photo_path'  => null,
            ]);
        });

        // ── Library badge count via View Composer ─────────────────────────────
        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            if (auth()->check()) {
                $navItems = config('publication.navigation');
                foreach ($navItems as &$item) {
                    if (($item['href'] ?? '') === 'publikasi.library') {
                        $item['badge'] = auth()->user()->savedPublications()->count();
                    }
                }
                $view->with('publicationNav', $navItems);
            }
        });

        $this->configureRateLimiting();
    }

    protected function configureRateLimiting(): void
    {
        // Login: maks 5x per menit per IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Terlalu banyak percobaan login. Coba lagi dalam 1 menit.',
                    ]);
                });
        });

        // Register: maks 5x per 10 menit per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Terlalu banyak percobaan daftar. Coba lagi dalam 10 menit.',
                    ]);
                });
        });

        // OTP verify: maks 10x per menit per user/IP
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(10)
                ->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'code' => 'Terlalu banyak percobaan. Coba lagi dalam 1 menit.',
                    ]);
                });
        });

        // OTP resend: maks 3x per 10 menit per user/IP
        RateLimiter::for('otp-resend', function (Request $request) {
            return Limit::perMinutes(10, 3)
                ->by(optional($request->user())->id ?: $request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'resend' => 'Batas kirim ulang OTP habis. Coba lagi dalam 10 menit.',
                    ]);
                });
        });

        // Kontak: maks 5x per 10 menit per IP (cegah spam form kontak)
        RateLimiter::for('kontak', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'message' => 'Terlalu banyak pesan dikirim. Coba lagi dalam 10 menit.',
                    ]);
                });
        });
    }
}
