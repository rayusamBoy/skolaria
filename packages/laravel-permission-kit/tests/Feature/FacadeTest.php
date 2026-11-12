<?php

use PermissionKit\Facades\PermissionKit;
use PermissionKit\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('returns false for guests', function () {
    expect(PermissionKit::can('users.view'))->toBeFalse();
});

it('can checks permission for auth user', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');
    $this->actingAs($this->user);

    expect(PermissionKit::can('users.view'))->toBeTrue()
        ->and(PermissionKit::can('users.delete'))->toBeFalse();
});

it('canAny returns true when any permission is granted', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');
    $this->actingAs($this->user);

    expect(PermissionKit::canAny(['users.view', 'users.delete']))->toBeTrue();
});

it('canAll requires all permissions', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users.delete', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');
    $this->actingAs($this->user);

    expect(PermissionKit::canAll(['users.view', 'users.delete']))->toBeFalse();

    $this->user->givePermissionTo('users.delete');

    expect(PermissionKit::canAll(['users.view', 'users.delete']))->toBeTrue();
});

it('forUser returns all assigned permission names', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');
    $this->actingAs($this->user);

    expect(PermissionKit::forUser())->toContain('users.view');
});

it('currentContext returns the active context key', function () {
    expect(PermissionKit::currentContext())->toBe('default');
});
