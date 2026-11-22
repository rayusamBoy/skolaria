<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('isolates cached values between tenants', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    $cacheKey = 'tenant-isolation-test';

    $tenantA = call_user_func([$this, 'createTestTenant']);

    Cache::put($cacheKey, 'Tenant A value');

    tenancy()->end();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    expect(Cache::get($cacheKey))->toBeNull();

    Cache::put($cacheKey, 'Tenant B value');

    tenancy()->initialize($tenantA);

    expect(Cache::get($cacheKey))->toBe('Tenant A value');

    tenancy()->initialize($tenantB);

    expect(Cache::get($cacheKey))->toBe('Tenant B value');
});
