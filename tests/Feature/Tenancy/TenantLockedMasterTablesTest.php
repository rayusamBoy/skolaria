
<?php

use App\Helpers\Usr;
use App\Services\FeatureModuleService;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\CreatesTestTenant;

uses(CreatesTestTenant::class);

beforeEach(function () {
    call_user_func([$this, 'destroyTestTenant']);

    Artisan::call('migrate', [
        '--database' => 'sqlite',
        '--path' => database_path('migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);

    $this->tenant = call_user_func([$this, 'createTestTenant']);

    config([
        'tenancy.active_identification_mode' => 'request_data',
    ]);

    // Keep the remaining setup unchanged.

    // Keep this test focused on the master-table lock,
    // independently of feature-module settings.
    $featureModules = Mockery::mock(FeatureModuleService::class);
    $featureModules->shouldReceive('isPermissionAllowed')
        ->andReturn(true);

    $this->app->instance(FeatureModuleService::class, $featureModules);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

afterEach(function () {
    call_user_func([$this, 'destroyTestTenant']);
});

it('allows reading but denies updating a locked master table for an admin', function () {
    $guard = config('auth.defaults.guard');

    $viewPermission = Permission::create([
        'name' => 'my_classes.view',
        'guard_name' => $guard,
    ]);

    $updatePermission = Permission::create([
        'name' => 'my_classes.update',
        'guard_name' => $guard,
    ]);

    $adminRole = Role::create([
        'name' => config('roles.default_roles.tenant.admin', 'Admin'),
        'guard_name' => $guard,
    ]);

    $adminRole->givePermissionTo([
        $viewPermission,
        $updatePermission,
    ]);

    $admin = User::factory()->create([
        'religion' => 'Islam',
        'code' => (string) Str::uuid(),
    ]);

    $admin->assignRole($adminRole);

    $this->tenant->update([
        'locked_master_tables' => ['my_classes'],
    ]);

    expect(tenancy()->initialized)->toBeTrue()
        ->and(tenancy()->tenant->id)->toBe($this->tenant->id)
        ->and(tenancy()->tenant->locked_master_tables)->toBe(['my_classes'])
        ->and($admin->hasAnyRole(Usr::getTenancyAwareRootRoles()))->toBeFalse();

    expect(Gate::forUser($admin)->allows('my_classes.view'))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('my_classes.update'))->toBeFalse();
});
