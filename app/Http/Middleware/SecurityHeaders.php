<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // ── 1. Cegah Clickjacking ─────────────────────────────────────────────
        // Larang halaman dimuat dalam iframe dari domain lain
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // ── 2. Cegah MIME Sniffing ────────────────────────────────────────────
        // Browser tidak boleh menebak content-type sendiri
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── 3. Aktifkan XSS Protection (browser lama) ────────────────────────
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // ── 4. Kontrol Info Referrer ──────────────────────────────────────────
        // Kirim referrer hanya ke sesama origin, tidak ke domain lain
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── 5. Batasi Fitur Browser ───────────────────────────────────────────
        // Matikan fitur browser yang tidak diperlukan
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()'
        );

        // ── 6. HSTS — Paksa HTTPS ─────────────────────────────────────────────
        // Browser wajib pakai HTTPS selama 1 tahun
        // ⚠️ Aktifkan HANYA jika sudah full HTTPS di production!
        if (app()->isProduction()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        // ── 7. Content Security Policy (CSP) ─────────────────────────────────
        // Kontrol dari mana saja resource boleh dimuat
        // Sesuaikan dengan resource yang dipakai di DABRAKA
        $csp = implode('; ', [
            "default-src 'self'",

            // Script: self + Google reCAPTCHA + Vite HMR (dev)
            "script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/",

            // Style: self + inline (Tailwind butuh ini)
            "style-src 'self' 'unsafe-inline'",

            // Gambar: self + data URI (placeholder) + storage
            "img-src 'self' data: blob: https:",

            // Font: self
            "font-src 'self' data:",

            // Frame: Google reCAPTCHA + Google Maps
            "frame-src 'self' https://www.google.com/ https://maps.google.com/ https://www.google.com/maps/",

            // Koneksi: self + reCAPTCHA
            "connect-src 'self' https://www.google.com/recaptcha/",

            // Form action hanya ke domain sendiri
            "form-action 'self'",

            // Base URI hanya domain sendiri
            "base-uri 'self'",

            // Upgrade semua HTTP ke HTTPS
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);

        // ── 8. Hapus header yang mengekspos info server ───────────────────────
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
