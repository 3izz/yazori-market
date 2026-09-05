<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * This business specifically wants every click into an admin page - not just
 * the initial username/password login - to demand a second, separate PIN,
 * so an already-open admin session can't just be browsed by whoever is
 * standing at the keyboard. The PIN is checked only for page navigation
 * (GET): a page you already passed the PIN to reach can still submit its own
 * forms (POST/PUT/DELETE) without asking again, since that's a continuation
 * of an already-verified visit, not a new one. The unlock flag is consumed
 * the moment it's used, so the very next navigation asks again.
 */
class RequireAdminNavPin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get')) {
            return $next($request);
        }

        if ($request->session()->pull('admin_nav_unlocked') === true) {
            return $next($request);
        }

        $request->session()->put('admin_nav_intended', $request->fullUrl());

        return redirect()->route('admin.pin.challenge');
    }
}
