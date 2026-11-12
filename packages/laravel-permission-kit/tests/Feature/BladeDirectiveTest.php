<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use PermissionKit\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
    $this->actingAs($this->user);
});

it('renders the block when permission is granted', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');

    $rendered = Blade::render(<<<'BLADE'
        @permissionKit('users.view')
        YES
        @endpermissionKit
        BLADE);

    expect(trim($rendered))->toBe('YES');
});

it('hides the block when permission is missing', function () {
    $rendered = Blade::render(<<<'BLADE'
        @permissionKit('users.view')
        YES
        @endpermissionKit
        BLADE);

    expect(trim($rendered))->toBe('');
});

it('renders the block when any permission matches', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');

    $rendered = Blade::render(<<<'BLADE'
        @permissionKitAny(['users.view', 'users.delete'])
        YES
        @endpermissionKitAny
        BLADE);

    expect(trim($rendered))->toBe('YES');
});

it('renders the block only when all permissions match', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    Permission::create(['name' => 'users.delete', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');

    $missing = Blade::render(<<<'BLADE'
        @permissionKitAll(['users.view', 'users.delete'])
        YES
        @endpermissionKitAll
        BLADE);

    expect(trim($missing))->toBe('');

    $this->user->givePermissionTo('users.delete');

    $present = Blade::render(<<<'BLADE'
        @permissionKitAll(['users.view', 'users.delete'])
        YES
        @endpermissionKitAll
        BLADE);

    expect(trim($present))->toBe('YES');
});
