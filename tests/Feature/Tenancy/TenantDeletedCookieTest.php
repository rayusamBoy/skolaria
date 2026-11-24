<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('does not initialize tenancy when the tenant from the cookie has been deleted', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    $tenant = call_user_func([$this, 'createTestTenant']);
    $tenantId = $tenant->id;

    // Delete the tenant record without triggering tenant database cleanup.
    Tenant::withoutEvents(fn () => $tenant->forceDelete());

    // Prepare the next simulated request.
    tenancy()->end();
    Auth::forgetGuards();

    $response = $this
        ->withCookie('tenant', $tenantId)
        ->get('/privacy-policy');

    $response->assertOk();

    expect(tenancy()->initialized)->toBeFalse();
});
