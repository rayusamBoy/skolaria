<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\View\View;

class TenantSwitchController extends Controller
{
    protected UserRepo $user;
    protected TenantRepo $tenant;

    public function __construct(
        UserRepo $user,
        TenantRepo $tenant
    ) {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    /**
     * Show all schools linked to the current central user.
     */
    public function index(): View
    {
        if (!auth()->check() && session()->has('redirected_from_central')) {
            Auth::loginUsingId(session('central_user_id'));
        }

        $managed_tenant_ids = $this->tenant->whereTenantManagerQuery(['central_user_id' => auth()->id()])->pluck('tenant_id')->toArray();
        $schools = $this->tenant->whereInQuery('id', $managed_tenant_ids)->with('domain')->get();

        return view('entry.schools_central', compact('schools'));
    }

    /**
     * Generate a signed token and redirect to the target tenant.
     */
    public function switch($tenantId): RedirectResponse
    {
        $central_user = auth()->user();

        // Verify this central user actually manages this tenant
        $linked = $this->tenant->whereTenantManagerQuery(['tenant_id' => $tenantId])->exists();

        if (!$linked) {
            abort(403, 'You do not manage this tenant.');
        }

        // Build signed token
        $payload = [
            'central_user_id' => $central_user->id,
            'tenant_id' => $tenantId,
            'expires' => now()->addMinutes(2)->timestamp,
        ];
        $secret = config('app.impersonate_secret');
        $token = hash_hmac('sha256', json_encode($payload), $secret);
        $token = base64_encode(json_encode($payload) . '::' . $token);

        return redirect()->route('tenant.impersonate', ['token' => $token])->withCookie(
            Cookie::make('tenant', $tenantId, 120)
        );
    }
}
