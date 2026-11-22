<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('isolates filesystem storage between tenants', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    $tenantA = call_user_func([$this, 'createTestTenant']);

    Storage::disk('local')->put('isolation-test.txt', 'Tenant A file');

    tenancy()->end();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    expect(Storage::disk('local')->exists('isolation-test.txt'))->toBeFalse();

    Storage::disk('local')->put('isolation-test.txt', 'Tenant B file');

    tenancy()->initialize($tenantA);

    expect(Storage::disk('local')->get('isolation-test.txt'))
        ->toBe('Tenant A file');

    tenancy()->initialize($tenantB);

    expect(Storage::disk('local')->get('isolation-test.txt'))
        ->toBe('Tenant B file');
});
