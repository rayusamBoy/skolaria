<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('clears the tenant cookie and returns to central context', function () {
    $tenant = call_user_func([$this, 'createTestTenant']);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenancy()->tenant->getTenantKey())->toBe($tenant->id);

    // Leave the tenant context.
    $response = $this
        ->withCookie('tenant', $tenant->id)
        ->post('/tenant/leave');

    $response->assertRedirect('/');
    $response->assertCookieExpired('tenant');

    // Prepare the next simulated request.
    tenancy()->end();
    Auth::forgetGuards();

    // Remove the tenant cookie from the test client's cookie jar.
    unset($this->defaultCookies['tenant']);

    // The next request should use central context.
    $centralResponse = $this->get('/privacy-policy');

    $centralResponse->assertOk();

    expect(tenancy()->initialized)->toBeFalse();
});
