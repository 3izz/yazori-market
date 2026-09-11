<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Each admin menu section (products, categories, purchases, inventory,
 * sales, reports, settings) needs the admin PIN once per section per
 * session, not once per click - entering a section and typing the PIN
 * unlocks that section for the rest of the session, so navigating back
 * into it later doesn't ask again. Other sections still ask the first time
 * they're visited. The dashboard is deliberately not part of this group at
 * all - it uses its own blur/reveal mechanism instead so it stays reachable
 * without a page redirect. The PIN is checked only for page navigation
 * (GET): a page you already unlocked can still submit its own forms
 * (POST/PUT/DELETE) without asking again.
 */
class RequireAdminNavPin
{
    private const SECTION_PATTERNS = [
        'categories' => ['categories.*'],
        'products' => ['products.*'],
        'purchases' => ['purchases.*'],
        'inventory' => ['inventory.*'],
        'sales' => ['sales.*'],
        'reports' => ['reports.*'],
        'settings' => ['settings.*'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get')) {
            return $next($request);
        }

        $section = $this->resolveSection($request->route()?->getName());
        $unlockedSections = $request->session()->get('admin_nav_unlocked_sections', []);

        if (in_array($section, $unlockedSections, true)) {
            return $next($request);
        }

        $request->session()->put('admin_nav_intended', $request->fullUrl());
        $request->session()->put('admin_nav_pending_section', $section);

        return redirect()->route('admin.pin.challenge');
    }

    private function resolveSection(?string $routeName): string
    {
        if (! $routeName) {
            return 'unknown';
        }

        foreach (self::SECTION_PATTERNS as $section => $patterns) {
            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return $section;
                }
            }
        }

        return $routeName;
    }
}
