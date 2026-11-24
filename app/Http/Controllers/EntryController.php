<?php

namespace App\Http\Controllers;

use App\Repositories\TenantRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class EntryController extends Controller
{
    public function __construct(
        protected TenantRepo $tenant
    ) {}

    /**
     * Select a school by tenant ID, store the tenant ID in a cookie,
     * and redirect to its login page.
     */
    public function select(int $tenant_id)
    {
        $tenant = $this->tenant->findOrFailTenant($tenant_id);

        return redirect('/login')->withCookie(
            Cookie::make('tenant', $tenant->id, 120)
        );
    }

    /**
     * Select a school by its slug, set the cookie, and redirect to the login page.
     */
    public function slug(int $tenant_slug)
    {
        $tenant = $this->tenant->whereTenantQuery([
            'data->identification_mode' => 'request_data',
            'data->tenant_slug' => $tenant_slug,
        ])->firstOrFail();

        return redirect('/login')->withCookie(
            Cookie::make('tenant', $tenant->id, 120)
        );
    }

    /**
     * Exit from the current tenant context and return to the central context.
     *
     * This only apply for the request_data (ie., cookie) identification mode
     */
    public function leave_tenant(Request $request)
    {
        return redirect('/')->withCookie(
            Cookie::forget('tenant')
        );
    }
}
