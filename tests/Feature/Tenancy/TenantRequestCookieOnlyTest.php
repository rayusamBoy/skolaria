
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

it('does not initialize tenancy from the X-Tenant header', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    tenancy()->end();

    $this->withHeader('X-Tenant', $tenant->id)
        ->get('/privacy-policy')
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeFalse();
});

it('does not initialize tenancy from the tenant query parameter', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    tenancy()->end();

    $this->get('/privacy-policy?tenant='.$tenant->id)
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeFalse();
});
