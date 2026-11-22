<?php

declare(strict_types=1);

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Support\CreatesTestTenant;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('does not reuse one tenant user session in another tenant', function () {
    $password = 'TenantPassword123!';

    // Create Tenant A and its user.
    $tenantA = call_user_func([$this, 'createTestTenant']);

    $userA = User::factory()->create([
        'email' => 'tenant-a@example.test',
        'password' => Hash::make($password),
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    // Create Tenant B with its own database and sessions table.
    $tenantB = call_user_func([$this, 'createTestTenant']);

    // Use the real database session driver for this test.
    config([
        'session.driver' => 'database',
        'session.connection' => null,
    ]);

    app('session')->forgetDrivers();

    // Let the HTTP middleware initialize tenancy from the request.
    tenancy()->end();

    // Log in to Tenant A.
    $loginResponse = $this
        ->withCookie('tenant', $tenantA->id)
        ->post('/login', [
            'identity' => $userA->email,
            'password' => $password,
        ]);

    $loginResponse->assertRedirect();

    // Keep the same browser session cookie for subsequent requests.
    $sessionCookie = collect($loginResponse->headers->getCookies())
        ->first(fn($cookie) => $cookie->getName() === config('session.cookie'));

    expect($sessionCookie)->not->toBeNull();

    $sessionCookieValue = $sessionCookie->getValue();

    // The authenticated session must work in Tenant A.
    $this
        ->withCookie(config('session.cookie'), $sessionCookieValue)
        ->withCookie('tenant', $tenantA->id)
        ->get('/test')
        ->assertOk();

    // Simulate the end of the Tenant A HTTP request.
    tenancy()->end();

    // Clear the cached authenticated user between simulated requests.
    Auth::forgetGuards();

    // The same browser session must NOT authenticate in Tenant B.
    $response = $this
        ->withCookie(config('session.cookie'), $sessionCookieValue)
        ->withCookie('tenant', $tenantB->id)
        ->get('/test');

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenancy()->tenant->getTenantKey())->toBe($tenantB->id);

    $response->assertRedirect('/login');
});

