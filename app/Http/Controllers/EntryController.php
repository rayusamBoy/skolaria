<?php

namespace App\Http\Controllers;

use App\Repositories\TenantRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class EntryController extends Controller
{
    protected TenantRepo $tenant;

    public function __construct(
        TenantRepo $tenant
    ) {
        $this->tenant = $tenant;
    }

    /**
     * Show the list of schools for this entry domain.
     */
    public function index()
    {
        $schools = $this->tenant->whereTenantQuery(['data->identification_mode' => 'request_data'])->get(['id', 'data']);

        return view('entry.schools', compact('schools'));
    }

    /**
     * Select a school by tenant ID, store the tenant ID in a cookie,
     * and redirect to its login page.
     */
    public function select($tenant_id)
    {
        $tenant = $this->tenant->findOrFailTenant($tenant_id);

        return redirect('/login')->withCookie(
            Cookie::make('tenant', $tenant->id, 120)
        );
    }

    /**
     * Select a school by its slug, set the cookie, and redirect to the login page.
     */
    public function slug($tenant_slug)
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
     * Logout from the current school and return to the central context.
     */
    public function tenant_logout(Request $request)
    {
        return redirect('/')->withCookie(
            Cookie::forget('tenant')
        );
    }
}
