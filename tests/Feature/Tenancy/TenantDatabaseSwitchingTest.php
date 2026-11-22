<?php

use App\Models\MyClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('preserves tenant data when switching between tenant databases', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    $tenantA = call_user_func([$this, 'createTestTenant']);

    MyClass::create([
        'name' => 'Tenant A Class',
        'class_type_id' => null,
    ]);

    tenancy()->end();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    MyClass::create([
        'name' => 'Tenant B Class',
        'class_type_id' => null,
    ]);

    expect(MyClass::where('name', 'Tenant B Class')->exists())->toBeTrue()
        ->and(MyClass::where('name', 'Tenant A Class')->exists())->toBeFalse();

    tenancy()->initialize($tenantA);

    expect(MyClass::where('name', 'Tenant A Class')->exists())->toBeTrue()
        ->and(MyClass::where('name', 'Tenant B Class')->exists())->toBeFalse();

    expect(tenancy()->tenant->getTenantKey())->toBe($tenantA->id);
});
