<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

const TENANT_COOKIE_TEST_ID = '01900000-0000-7000-8000-000000000001';
const SECOND_TENANT_COOKIE_TEST_ID = '01900000-0000-7000-8000-000000000002';

beforeEach(function () {
    // Keep the test independent of tenant database provisioning.
    config(['tenancy.bootstrappers' => []]);

    // Create only the central tables needed by the tenant resolver.
    Schema::create('tenants', function ($table) {
        $table->uuid('id')->primary();
        $table->timestamps();
        $table->json('data')->nullable();
        $table->softDeletes();
    });

    Schema::create('settings', function ($table) {
        $table->increments('id');
        $table->string('key');
        $table->string('value')->nullable();
        $table->timestamps();
    });

    // Suppress model events so TenantCreated does not provision a database.
    Tenant::withoutEvents(function () {
        Tenant::create([
            'id' => TENANT_COOKIE_TEST_ID,
        ]);
    });

    // A small probe route lets us inspect the initialized tenant context.

    Route::middleware([
        InitializeTenancyByRequestData::class,
    ])->get('/__tests/tenant-context', function () {
        return response()->json([
            'tenant_id' => tenancy()->tenant?->getTenantKey(),
        ]);
    });
});

it('initializes the tenant using the tenant cookie', function () {
    $this->withCookie('tenant', TENANT_COOKIE_TEST_ID)
        ->get('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => TENANT_COOKIE_TEST_ID,
        ]);
});

it('does not initialize a tenant using the tenant header', function () {
    $this->withHeader('X-Tenant', TENANT_COOKIE_TEST_ID)
        ->getJson('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => null,
        ]);
});

it('does not initialize a tenant using the tenant query parameter', function () {
    $this->getJson('/__tests/tenant-context?tenant='.TENANT_COOKIE_TEST_ID)
        ->assertOk()
        ->assertJson([
            'tenant_id' => null,
        ]);
});

it('continues without a tenant when the cookie is missing', function () {
    $this->getJson('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => null,
        ]);
});

it('continues without a tenant when the cookie contains an unknown tenant id', function () {
    $this->withCookie('tenant', 'unknown-tenant')
        ->getJson('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => null,
        ]);
});

it('loads the real login page with the tenant cookie', function () {
    $this->withCookie('tenant', TENANT_COOKIE_TEST_ID)
        ->get('/login')
        ->assertOk()
        ->assertViewIs('auth.login')
        ->assertViewHas('current_tenant_id', TENANT_COOKIE_TEST_ID); // current_tenant_id - value shared on LoadCommonContext middleware
});

it('redirects unauthenticated users to login from the tenant dashboard', function () {
    $this->withCookie('tenant', TENANT_COOKIE_TEST_ID)
        ->get('/dashboard')
        ->assertRedirect('/login');
});

it('does not initialize tenant context when accessing the central dashboard', function () {
    $this->get('/central/dashboard')
        ->assertRedirect();

    expect(tenancy()->tenant)->toBeNull();
});

it('does not initialize tenant context on central routes when a tenant cookie is present', function () {
    $this->withCookie('tenant', TENANT_COOKIE_TEST_ID)
        ->get('/central/dashboard')
        ->assertRedirect();

    expect(tenancy()->tenant)->toBeNull();
});

it('switches tenant context when the tenant cookie changes', function () {
    Tenant::withoutEvents(function () {
        Tenant::create([
            'id' => SECOND_TENANT_COOKIE_TEST_ID,
        ]);
    });

    // First request: initialize tenant one.
    $this->withCookie('tenant', TENANT_COOKIE_TEST_ID)
        ->get('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => TENANT_COOKIE_TEST_ID,
        ]);

    // Second request: switch to tenant two.
    $this->withCookie('tenant', SECOND_TENANT_COOKIE_TEST_ID)
        ->get('/__tests/tenant-context')
        ->assertOk()
        ->assertJson([
            'tenant_id' => SECOND_TENANT_COOKIE_TEST_ID,
        ]);
});
