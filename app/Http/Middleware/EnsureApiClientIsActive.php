<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiClientIsActive
{
    /**
     * Ensure the authenticated Sanctum principal is an active API client.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $client = $request->user();

        abort_unless(
            $client instanceof ApiClient && $client->isActive(),
            Response::HTTP_FORBIDDEN,
            'This API client is inactive or not authorized.'
        );

        return $next($request);
    }
}
