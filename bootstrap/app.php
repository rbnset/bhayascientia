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
        // Dipanggil via ->middleware('nama') di route tertentu
        $middleware->alias([
            'verified.otp' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // ── Global Web Middleware ─────────────────────────────────────────────
        // Urutan eksekusi: dari atas ke bawah
        // PENTING: EnsureOnboardingComplete harus SETELAH SecurityHeaders
        // karena onboarding butuh session & cookie yang sudah siap
        $middleware->web(append: [

            // 1. Security headers — ditambahkan ke semua response
            //    Tidak butuh session, aman di posisi pertama
            \App\Http\Middleware\SecurityHeaders::class,

            // 2. Onboarding gate — cek apakah user sudah lihat onboarding
            //    Butuh: session (sudah siap), cookie (sudah didekripsi)
            //    Harus setelah semua middleware web bawaan Laravel
            // \App\Http\Middleware\EnsureOnboardingComplete::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
