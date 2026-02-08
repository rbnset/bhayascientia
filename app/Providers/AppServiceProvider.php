<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AuthorService;
use App\Repositories\AuthorRepository;
use App\Actions\Author\GetBestAuthorsAction;

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
        //
    }
}
