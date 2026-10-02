<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        // ---- Share BEFORE rendering the view ----
        if ($user = $request->user()) {
            $remember = $user->getRememberToken() !== null && $user->getRememberToken() !== '';

            $lifetime = $remember
                ? (int) config('auth.remember_ttl', 60 * 24 * 14) * 60
                : (int) config('session.lifetime') * 60;

            $expiresAt = now()->addSeconds($lifetime)->getTimestamp();

            View::share('session_expires_at', $expiresAt);
            View::share('session_remember', $remember);

            $response = $next($request);

            return $response;
        }

        return $next($request);
    }
}
