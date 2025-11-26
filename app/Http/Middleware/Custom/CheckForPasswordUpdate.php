<?php

namespace App\Http\Middleware\Custom;

use Closure;

class CheckForPasswordUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If the user is authenticated and did not update password, as well as did not want to logout.
        if (auth()->check() && auth()->user()->password_updated_at === null && !$request->routeIs('logout')) {
            // If the user try to submit the change password form, just go as usual; allow for any errors to return.
            if ($request->routeIs('my_account.change_pass'))
                return $next($request);
            elseif (!$request->routeIs('my_account.*'))
                // If the route the user is in, is not my account (the actual route that contains the change password form).
                // Force the user to my_account route.
                return redirect()->route('my_account.edit')->with('pop_warning', __('msg.update_password'));
        }
        
        return $next($request);
    }
}
