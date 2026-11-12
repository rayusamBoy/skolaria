<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Contracts\Scanner;
use PermissionKit\Support\Context;
use PermissionKit\Tests\Fixtures\Http\Controllers\UserController;
use PermissionKit\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    Route::middleware('web')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/show/{id}', [UserController::class, 'show'])->name('users.show');
    });
});

it('allows access when the user has the permission', function () {
    Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $this->user->givePermissionTo('users.view');

    $response = $this->actingAs($this->user)->get('/users');

    $response->assertOk();
    expect($response->getContent())->toBe('ok');
});

it('denies access when the user lacks the permission', function () {
    $response = $this->actingAs($this->user)->get('/users');

    $response->assertForbidden();
});

it('does not enforce when permission-kit.enforce is false', function () {
    config()->set('permission-kit.enforce', false);

    $response = $this->actingAs($this->user)->get('/users');

    $response->assertOk();
    expect($response->getContent())->toBe('ok');
});

test('it does not discover internal trait methods', function () {
    $definitions = app(Scanner::class)->scan(
        Context::fromConfig('default', [
            config('permission-kit.contexts.'.app(ContextResolver::class)->resolve(), []),
        ])
    );

    expect($definitions->pluck('method'))
        ->not->toContain('bootAuthorizesPermissions');
});
