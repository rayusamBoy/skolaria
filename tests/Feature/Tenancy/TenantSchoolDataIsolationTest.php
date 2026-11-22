<?php

use App\Models\MyClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('does not expose school classes from another tenant', function () {
    config(['tenancy.active_identification_mode' => 'request_data']);

    $tenantA = call_user_func([$this, 'createTestTenant']);

    $class = MyClass::create([
        'name' => 'Tenant A Class',
        'class_type_id' => null,
    ]);

    $classId = $class->id;

    tenancy()->end();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    expect(MyClass::find($classId))->toBeNull()
        ->and(MyClass::where('name', 'Tenant A Class')->exists())->toBeFalse();

    expect(tenancy()->tenant->getTenantKey())->toBe($tenantB->id);
});
