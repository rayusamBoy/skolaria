<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Http\Request;

class CheckForPasswordUpdate
{
    /**
     * Handle an incoming request.
     * Checks if the authenticated user has updated their password. 
     * If not, and they are not trying to logout or access the change password form, it redirects them to the change password page with a warning message. 
     * This ensures that users update their passwords before accessing other parts of the application.
     *
     * @param  Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
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
