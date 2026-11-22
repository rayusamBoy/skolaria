<?php

namespace App\Http\Middleware;

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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $preferences = $user->preferences ?? Usr::getDefaultUserPreferences();
        $is_tenant_context = Usr::tenancyInitialized();
        $my_classes = $is_tenant_context && ! app()->runningUnitTests() ? Usr::getAllClassesOrdered() : collect();
        $settings = Usr::getSettings();

        View::share([
            'user_preferences' => $preferences,
            'my_classes' => $my_classes,
            'settings' => $settings,
            'current_tenant_id' => tenant()?->getTenantKey() ?? null,
            'is_tenant_context' => $is_tenant_context,
        ]);

        return $next($request);
    }
}
