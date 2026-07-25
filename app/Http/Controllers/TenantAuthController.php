<?php

namespace App\Http\Controllers;

use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class TenantAuthController extends Controller
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
     * Impersonate a tenant user.
     */
    public function impersonate(Request $request): RedirectResponse
    {
        $token = $request->query('token');
        if (!$token)
            abort(403, 'Token not found');

        [$encoded_payload, $hash] = explode('::', base64_decode($token));
        $payload = json_decode($encoded_payload, true);

        $expected_hash = hash_hmac('sha256', $encoded_payload, config('app.impersonate_secret'));
        if (!hash_equals($expected_hash, $hash) || $payload['expires'] < time()) {
            abort(403, 'Invalid token');
        }

        // Find the local user linked to this central user for this tenant
        $link = $this->tenant->whereTenantManagerQuery(['central_user_id' => $payload['central_user_id'], 'tenant_id' => $payload['tenant_id']])->first();

        if (!$link || !$link->tenant_user_id) {
            abort(403, 'Invalid token');
        }

        $local_user = $this->user->findUser($link->tenant_user_id);
        if (!$local_user)
            abort(403);

        $tenant_name = $this->tenant->findOrFailTenant($payload['tenant_id'])->name;
        Auth::login($local_user);

        // Mark that this session originated from a central impersonation
        session([
            'impersonated_by_central' => true,
            'central_user_id' => $payload['central_user_id'],
        ]);

        return redirect()->route('dashboard')->with('pop_success', "You are now logged in as {$local_user->name} ({$tenant_name})");
    }

    /**
     * Log out the current tenant user and redirect to the central domain.
     */
    public function back_to_central(): RedirectResponse
    {
        // Clear the impersonation flag so it doesn't persist in session
        session()->forget(['impersonated_by_central', 'central_user_id']);

        // Log out the current tenant user
        // Auth::logout();

        // Redirect to the central domain's school selection page
        return redirect()->route('tenant.switch.schools')->withCookie(
            Cookie::forget('tenant')
        )->with('redirected_from_central', true);
    }
}
