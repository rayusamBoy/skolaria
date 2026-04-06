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
    protected array $keysToDecode = ['payment', 'user', 'central_user', 'student', 'book', 'notice', 'tenant'];

    /**
     * Handle an incoming request.
     * Decodes specified route parameters using Qs::decodeHash before the request is processed by the controller. 
     * If decoding fails, it aborts with a 404 error.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $params = $request->route()->parameters();
        foreach ($params as $key => $value) {
            if (\in_array($key, $this->keysToDecode) && $value !== null) {
                try {
                    $request->route()->setParameter($key, Qs::decodeHash($value));
                } catch (\Exception $e) {
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
