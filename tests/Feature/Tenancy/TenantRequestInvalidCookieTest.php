
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

beforeEach(function () {
    config(['tenancy.active_identification_mode' => 'request_data']);
});

it('continues in central context when the tenant cookie contains an unknown tenant ID', function () {
    $this->withCookie('tenant', 'non-existent-tenant-id')
        ->get('/privacy-policy')
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeFalse()
        ->and(tenant('id'))->toBeNull();
});

it('continues in central context when the tenant cookie is malformed', function () {
    $this->withCookie('tenant', 'invalid/tenant/id')
        ->get('/privacy-policy')
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeFalse()
        ->and(tenant('id'))->toBeNull();
});
