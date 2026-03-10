<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TourController extends Controller
{
    private const ALLOWED_PAGES = ['index', 'browse', 'detail'];
    private const COOKIE_TTL_MINUTES = 60 * 24 * 365; // 1 tahun

    public function complete(Request $request, string $page)
    {
        // ✅ Whitelist validasi page
        if (! in_array($page, self::ALLOWED_PAGES, strict: true)) {
            return response()->json([
                'status'  => 'ignored',
                'message' => 'Page not recognized.',
            ], 422);
        }

        // ✅ Idempotency — skip kalau cookie sudah ada
        $cookieName = 'has_seen_' . $page . '_tour';
        if ($request->cookie($cookieName)) {
            return response()->json([
                'status'  => 'already_seen',
                'message' => 'Tour already completed.',
            ], 200);
        }

        // ✅ Set cookie dengan flag keamanan
        $cookie = cookie(
            name: $cookieName,
            value: '1',
            minutes: self::COOKIE_TTL_MINUTES,
            path: '/',
            domain: null,
            secure: $request->isSecure(),
            httpOnly: true,
            sameSite: 'Lax',
        );

        return response()
            ->json([
                'status'  => 'ok',
                'message' => 'Tour marked as completed.',
            ], 200)
            ->withCookie($cookie);
    }
}
