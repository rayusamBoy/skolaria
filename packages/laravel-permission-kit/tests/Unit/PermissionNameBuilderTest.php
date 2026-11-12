<?php

use PermissionKit\Services\PermissionNameBuilder;
use PermissionKit\Support\Context;
use PermissionKit\Tests\Fixtures\Http\Controllers\StudentController;
use PermissionKit\Tests\Fixtures\Http\Controllers\UserController;
use ReflectionClass;

it('maps index to view', function () {
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(UserController::class);
    $method = $class->getMethod('index');

    $context = new Context(
        key: 'default',
        prefix: null,
        namespaces: ['PermissionKit\\Tests\\Fixtures\\Http\\Controllers'],
        skip_names: [],
        skip_paths: [],
    );

    $definition = $builder->build($class, $method, $context);

    expect($definition->name)->toBe('users.view');
});

it('maps store to create', function () {
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(UserController::class);
    $method = $class->getMethod('store');

    $context = new Context('default', null, [], [], []);
    $definition = $builder->build($class, $method, $context);

    expect($definition->name)->toBe('users.create');
});

it('maps destroy to delete', function () {
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(UserController::class);
    $method = $class->getMethod('destroy');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('users.delete');
});

it('uses the method attribute when present', function () {
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(StudentController::class);
    $method = $class->getMethod('export');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('students.export')
        ->and($definition->label)->toBe('Export Students')
        ->and($definition->description)->toBe('Download a CSV of students');
});

it('uses underscores by default when the method is snake_case', function () {
    config()->set('permission-kit.use_dots', 'auto');

    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(StudentController::class);
    $method = $class->getMethod('update_lock_state');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('students.update_lock_state');
});

it('uses dots by default when the method is camelCase', function () {
    config()->set('permission-kit.use_dots', 'auto');

    // Needs a fixture with a camelCase method — add one or reuse.
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(StudentController::class);
    $method = new ReflectionMethod(StudentController::class, 'updateLockState');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('students.update.lock.state');
});

it('handles acronyms in camelCase method names', function () {
    config()->set('permission-kit.use_dots', false);

    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(StudentController::class);
    $method = new ReflectionMethod(StudentController::class, 'getHTMLContent');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('students.get_html_content');
});

it('applies the context prefix', function () {
    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(UserController::class);
    $method = $class->getMethod('index');

    $definition = $builder->build($class, $method, new Context('central', 'central', [], [], []));

    expect($definition->name)->toBe('central.users.view');
});

it('honours config overrides by controller name', function () {
    config()->set('permission-kit.overrides', [
        UserController::class => [
            'index' => 'users.list',
        ],
    ]);

    $builder = new PermissionNameBuilder;
    $class = new ReflectionClass(UserController::class);
    $method = $class->getMethod('index');

    $definition = $builder->build($class, $method, new Context('default', null, [], [], []));

    expect($definition->name)->toBe('users.list');
});
