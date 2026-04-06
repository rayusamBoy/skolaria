<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Mk;
use Closure;
use \Illuminate\Http\Request;

class ExamIsLocked
{
    /**
     * Handle an incoming request.
     * Allows the request to proceed only if the exam is currently locked. If the exam is not locked, it redirects to the dashboard.
     *
     * @param  Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return Mk::examIsLocked() ? $next($request) : redirect()->route('dashboard');
    }
}
