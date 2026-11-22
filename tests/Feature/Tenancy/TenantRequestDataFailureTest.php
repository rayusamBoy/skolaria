
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('does not initialize tenancy when the tenant cookie is invalid', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    tenancy()->end();
    Auth::forgetGuards();

    $response = $this
        ->withCookie('tenant', 'non-existent-tenant-id')
        ->get('/test');

    // The request should continue without initializing a tenant.
    expect(tenancy()->initialized)->toBeFalse();

    // /test requires authentication, so central context redirects to login.
    $response->assertRedirect('/login');

    expect(tenancy()->initialized)->toBeFalse();
});
