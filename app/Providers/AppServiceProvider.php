<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthorService;
use App\Repositories\AuthorRepository;
use App\Actions\Author\GetBestAuthorsAction;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Author-related services sebagai singleton
        $this->app->singleton(AuthorService::class);
        $this->app->singleton(AuthorRepository::class);
        $this->app->singleton(GetBestAuthorsAction::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
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

        // Register: maks 5x per 10 menit per IP (cegah spam akun)
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(10, 5)
                ->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Terlalu banyak percobaan daftar. Coba lagi dalam 10 menit.',
                    ]);
                });
        });
    }
}
