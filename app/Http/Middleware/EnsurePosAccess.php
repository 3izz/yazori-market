<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a cashier reach the POS screen with just the short PIN set from
 * Settings, without needing the full admin username/password - while a
 * logged-in admin using POS still passes through normally. Every other page
 * stays behind the regular "auth" middleware, which a PIN-only session does
 * not satisfy.
 */
class EnsurePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() || $request->session()->get('pos_unlocked')) {
            return $next($request);
        }

        return redirect()->route('pos.unlock');
    }
}
