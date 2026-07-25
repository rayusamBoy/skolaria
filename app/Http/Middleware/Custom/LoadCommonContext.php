<?php

namespace App\Http\Middleware\Custom;

use App\Helpers\Usr;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TenantManager;

class LoadCommonContext
{
    /**
     * Handle an incoming request.
     * Loads common data into the view context, such as user preferences, classes, settings, and permissions.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $preferences = $user->preferences ?? Usr::getDefaultUserPreferences();
        $is_tenant_context = Usr::tenancyInitialized();
        $my_classes = $is_tenant_context ? Usr::getAllClassesOrdered() : collect();
        $settings = Usr::getSettings();
        $managed_tenants = collect();

        if (auth()->check()) {
            if (!$is_tenant_context) {
                // Central context: show all tenants linked to this central user
                $managed_tenants = TenantManager::with('tenant')
                    ->where('central_user_id', auth()->id())
                    ->get()
                    ->pluck('tenant');
            } else {
                // Tenant context: only show if the session was created via central impersonation
                if (session('impersonated_by_central')) {
                    $central_user_id = session('central_user_id');
                    if ($central_user_id) {
                        $managed_tenants = TenantManager::with('tenant')
                            ->where('central_user_id', $central_user_id)
                            ->get()
                            ->pluck('tenant');
                    }
                }
            }
        }

        View::share([
            'user_preferences' => $preferences,
            'my_classes' => $my_classes,
            'settings' => $settings,
            'tenant_permissions' => config('permissions.names.tenant', []),
            'central_permissions' => config('permissions.names.central', []),
            'managed_tenants' => $managed_tenants,
            'current_tenant_id' => tenant()?->getTenantKey() ?? null,
            'is_tenant_context' => $is_tenant_context,
        ]);

        return $next($request);
    }
}
