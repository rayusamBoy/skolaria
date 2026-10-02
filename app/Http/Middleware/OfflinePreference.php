<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfflinePreference
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($user = $request->user()) {
            $allow = (int) ($user->preferences['allow_offline_access'] ?? 0);
            $response->headers->set('X-Allow-Offline', (string) $allow);

            if ($allow === 1) {
                // Cacheable, but the browser must revalidate with the server.
                $response->headers->set('Cache-Control', 'no-cache, private, must-revalidate');
            } else {
                // Never cache, never keep in BF cache.
                $response->headers->set('Cache-Control', 'no-store, private, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
            }
        }

        return $response;
    }
}
