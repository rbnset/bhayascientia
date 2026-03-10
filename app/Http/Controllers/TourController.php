<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class TourController extends Controller
{
    public function complete(Request $request, string $page)
    {
        $allowedPages = ['index', 'browse', 'detail']; // whitelist page

        if (! in_array($page, $allowedPages)) {
            return response()->json(['status' => 'ignored']);
        }

        $cookieName = "has_seen_{$page}_tour";

        // Cookie berlaku 365 hari, httpOnly, aman
        $cookie = cookie(
            name: $cookieName,
            value: '1',
            minutes: 60 * 24 * 365, // 1 tahun
            path: '/',
            secure: request()->isSecure(),
            httpOnly: true,
            sameSite: 'Lax',
        );

        return response()
            ->json(['status' => 'ok'])
            ->withCookie($cookie);
    }
}
