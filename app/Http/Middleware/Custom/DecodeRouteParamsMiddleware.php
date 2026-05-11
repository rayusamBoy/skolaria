<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Qs;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecodeRouteParamsMiddleware
{
    /**
     * List of route parameter names to decode.
     */
    protected const array KEYS_TO_DECODE = [
        'payment',
        'user',
        'central_user',
        'student',
        'book',
        'book_request',
        'notice',
        'tenant',
        'ticket',
        'pin'
    ];

    /**
     * Handle an incoming request.
     *
     * Decodes specified route parameters using Qs::decodeHash before the request
     * is processed by the controller. If decoding fails, aborts with 404.
     *
     * @param  Request  $request
     * @param  Closure(Request): Response  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $parameters = $route->parameters();

        foreach (self::KEYS_TO_DECODE as $key) {
            if (!\array_key_exists($key, $parameters) || $parameters[$key] === null) {
                continue;
            }

            try {
                $route->setParameter($key, Qs::decodeHash($parameters[$key]));
            } catch (\Exception $e) {
                abort(404);
            }
        }

        return $next($request);
    }
}
