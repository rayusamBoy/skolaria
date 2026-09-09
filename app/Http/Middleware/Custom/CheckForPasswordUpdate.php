<?php

namespace App\Http\Middleware\Custom;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckForPasswordUpdate
{
    /**
     * Handle an incoming request.
     *
     * Checks if the authenticated user has updated their password.
     * If not, and they are not trying to logout or access the change password form,
     * redirects them to the change password page with a warning message.
     * This ensures that users update their passwords before accessing other parts of the application.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // User not authenticated or password already updated
        if (! $user || $user->password_updated_at !== null) {
            return $next($request);
        }

        // Allow logout and password change routes
        if ($request->routeIs('logout', 'my_account.change_pass')) {
            return $next($request);
        }

        // Allow any route under 'my_account.*' (including the edit form)
        if ($request->routeIs('my_account.*')) {
            return $next($request);
        }

        // Force redirect to password change page
        return redirect()->route('my_account.edit')->with('pop_warning', __('msg.update_password'));
    }
}
