
<?php

use App\Models\MyClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

beforeEach(function () {
    config(['tenancy.active_identification_mode' => 'request_data']);
});

it('switches tenant database context between consecutive requests', function () {
    $tenantA = call_user_func([$this, 'createTestTenant']);

    MyClass::create([
        'name' => 'Tenant A Class',
    ]);

    tenancy()->end();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    MyClass::create([
        'name' => 'Tenant B Class',
    ]);

    tenancy()->end();

    $this->withCookie('tenant', $tenantA->id)
        ->get('/privacy-policy')
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenant('id'))->toBe($tenantA->id);

    tenancy()->end();
    Auth::forgetGuards();

    $this->withCookie('tenant', $tenantB->id)
        ->get('/privacy-policy')
        ->assertSuccessful();

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenant('id'))->toBe($tenantB->id)
        ->and(MyClass::where('name', 'Tenant B Class')->exists())->toBeTrue()
        ->and(MyClass::where('name', 'Tenant A Class')->exists())->toBeFalse();
});
