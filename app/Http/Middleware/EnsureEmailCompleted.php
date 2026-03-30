<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailCompleted
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            auth()->user()->needs_email_completion &&
            !$request->routeIs('orcid.complete-email*') &&
            !$request->routeIs('logout')
        ) {
            return redirect()->route('orcid.complete-email.show');
        }

        return $next($request);
    }
}
