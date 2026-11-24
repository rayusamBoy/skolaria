<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Tenant;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Concerns\AuthorizesPermissions;
use Stancl\Tenancy\Features\UserImpersonation;

class UserImpersonationController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(
        protected UserRepo $user,
        protected TenantRepo $tenant
    ) {}

    /**
     * Show all tenants linked to the current central user.
     */
    public function index(): View|RedirectResponse
    {
        if (config('tenancy.allow_user_impersonation') === false) {
            return back();
        }

        $tenants = $this->tenant->getAllTenants();
        $tenant_super_admin_user_id = 1;

        return view('impersonation.index', compact('tenants', 'tenant_super_admin_user_id'));
    }

    #[SkipPermission]
    public function impersonate(string|Model $token): RedirectResponse
    {
        try {
            session()->put('impersonated_from_central', true);

            return UserImpersonation::makeResponse($token)->with('pop_success', 'You have now logged in as the impersonated user - Super Admin.');
        } catch (\Error) {
            session()->forget('impersonated_from_central');

            return back();
        }
    }

    public function redirect_to_impersonation_route(string $tenant_id, int $user_id): RedirectResponse
    {
        if (config('tenancy.allow_user_impersonation') === false) {
            return back();
        }

        $tenant = $this->tenant->findOrFailTenant($tenant_id);
        $token = $this->generateToken($tenant, $user_id);

        if (config('tenancy.active_identification_mode') === 'request_data') {
            return redirect("/impersonate/{$token->token}")->withCookie(
                Cookie::make('tenant', $tenant->id, 120)
            );
        }

        $domain = Qs::formatDomain($tenant->domain->domain ?? $tenant->domain);

        return redirect("$domain/impersonate/{$token->token}");
    }

    private function generateToken(Tenant $tenant, int $user_id, string $redirect_url = '/dashboard')
    {
        return tenancy()->impersonate($tenant, $user_id, $redirect_url);
    }
}
