<?php

declare(strict_types=1);

use App\User;
use Database\Seeders\TenancyRolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Support\CreatesTestTenant;

uses(RefreshDatabase::class, CreatesTestTenant::class);

beforeEach(function () {
    call_user_func([$this, 'createTestTenant']);
});

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('creates an isolated tenant database with permission tables', function () {
    expect(Schema::connection('tenant')->hasTable('roles'))
        ->toBeTrue()
        ->and(Schema::connection('tenant')->hasTable('permissions'))
        ->toBeTrue();
});

it('does not grant tenant Admin Super Admin permissions', function () {
    // Register the actual permissions discovered from tenant controllers.
    Artisan::call('permission-kit:sync', [
        '--context' => 'tenant',
    ]);

    // Run the application's real tenant role seeder.
    $this->seed(TenancyRolesTableSeeder::class);

    $admin = Role::findByName(
        config('roles.default_roles.tenant.admin'),
        'web'
    );

    $superAdmin = Role::findByName(
        config('roles.default_roles.tenant.superadmin'),
        'web'
    );

    expect($admin->hasPermissionTo('soft_deletes.payment_force_delete'))
        ->toBeFalse()
        ->and($admin->hasPermissionTo('soft_deletes.user_force_delete'))
        ->toBeFalse()
        ->and($admin->hasPermissionTo('soft_deletes.empty_soft_deleted_model'))
        ->toBeFalse()
        ->and($superAdmin->permissions)
        ->toHaveCount(0);
});

it('enforces tenant Admin and Super Admin authorization boundaries', function () {
    Artisan::call('permission-kit:sync', [
        '--context' => 'tenant',
    ]);

    $this->seed(TenancyRolesTableSeeder::class);

    $adminRole = Role::findByName(
        config('roles.default_roles.tenant.admin'),
        'web'
    );

    $superAdminRole = Role::findByName(
        config('roles.root_roles.tenant.superadmin'),
        'web'
    );

    $admin = User::factory()->create([
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    $superAdmin = User::factory()->create([
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    $admin->assignRole($adminRole);
    $superAdmin->assignRole($superAdminRole);

    expect($admin->hasRole($adminRole))->toBeTrue()
        ->and($superAdmin->hasRole($superAdminRole))->toBeTrue()
        ->and($admin->hasPermissionTo('soft_deletes.payment_force_delete'))->toBeFalse()
        ->and(Gate::forUser($admin)->denies('soft_deletes.payment_force_delete'))->toBeTrue();
});

it('isolates user records between tenant databases', function () {
    $tenantA = call_user_func([$this, 'createTestTenant']);

    $userA = User::factory()->create([
        'email' => 'tenant-a@example.test',
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    $tenantB = call_user_func([$this, 'createTestTenant']);

    $userB = User::factory()->create([
        'email' => 'tenant-b@example.test',
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    expect($tenantA->database()->getName())
        ->not->toBe($tenantB->database()->getName());

    // Tenant B must not see Tenant A's user.
    expect(User::where('email', $userA->email)->exists())->toBeFalse()
        ->and(User::where('email', $userB->email)->exists())->toBeTrue();

    // Switch back to Tenant A.
    tenancy()->end();
    tenancy()->initialize($tenantA);

    // Tenant A must not see Tenant B's user.
    expect(User::where('email', $userA->email)->exists())->toBeTrue()
        ->and(User::where('email', $userB->email)->exists())->toBeFalse();
});
