<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ── Alias Middleware ──────────────────────────────────────────────────
        $middleware->alias([
            'verified.otp' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // ── Security Headers (berlaku untuk SEMUA halaman web) ────────────────
        // $middleware->web(append: [
        //     \App\Http\Middleware\SecurityHeaders::class,
        // ]);

        // ✅ Onboarding HARUS pakai appendToGroup 'web'
        // karena butuh session yang sudah di-start oleh middleware web bawaan
        // $middleware->appendToGroup('web', \App\Http\Middleware\EnsureOnboardingComplete::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
