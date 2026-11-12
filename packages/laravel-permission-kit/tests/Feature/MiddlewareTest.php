<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PermissionKit\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Route::middleware('web')->group(function () {
        Route::get('/protected', fn () => 'ok')
            ->middleware('permission.kit:users.view')
            ->name('protected');
    });

    $this->user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
});

it('allows access with permission', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');

    $response = $this->actingAs($this->user)->get('/protected');

    $response->assertOk();
    expect($response->getContent())->toBe('ok');
});

it('blocks access without permission', function () {
    $response = $this->actingAs($this->user)->get('/protected');

    $response->assertForbidden();
});
