<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Usr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class LoadCommonContext
{
    /**
     * Handle an incoming request.
     * Loads common data into the view context, such as user preferences, classes, settings, and permissions.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $preferences = $user->preferences ?? Usr::getDefaultUserPreferences();
        $my_classes = Usr::tenancyInitilized() ? Usr::getAllClassesOrdered() : collect();
        $settings = Usr::getSettings();

        View::share([
            'user_preferences' => $preferences,
            'my_classes' => $my_classes,
            'settings' => $settings,
            'tenant_permissions' => config('permissions.names.tenant', []),
            'central_permissions' => config('permissions.names.central', []),
        ]);

        return $next($request);
    }
}
