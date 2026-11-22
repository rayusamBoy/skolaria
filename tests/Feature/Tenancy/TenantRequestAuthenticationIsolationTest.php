
<?php

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

afterEach(function () {
    Auth::forgetGuards();

    call_user_func([$this, 'destroyTestTenant']);
});

beforeEach(function () {
    config(['tenancy.active_identification_mode' => 'request_data']);
});

it('does not share authenticated users between tenant requests', function () {
    $tenantA = call_user_func([$this, 'createTestTenant']);

    /** @var User $userA */
    $userA = User::factory()->create([
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    tenancy()->end();
    Auth::forgetGuards();

    $tenantB = call_user_func([$this, 'createTestTenant']);

    $userB = User::factory()->create([
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    tenancy()->end();
    Auth::forgetGuards();

    $this->withCookie('tenant', $tenantA->id)
        ->actingAs($userA)
        ->get('/test')
        ->assertSuccessful();

    expect(Auth::check())->toBeTrue()
        ->and(Auth::id())->toBe($userA->id);

    tenancy()->end();
    Auth::forgetGuards();

    $this->withCookie('tenant', $tenantB->id)
        ->get('/test')
        ->assertRedirect();

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenant('id'))->toBe($tenantB->id)
        ->and(Auth::check())->toBeFalse();
});
