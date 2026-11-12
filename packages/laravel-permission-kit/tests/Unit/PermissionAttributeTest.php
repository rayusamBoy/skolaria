<?php

use PermissionKit\Attributes\Permission;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Tests\Fixtures\Http\Controllers\StudentController;

it('reads a permission attribute', function () {
    $reflection = new ReflectionMethod(StudentController::class, 'export');
    $attributes = $reflection->getAttributes(Permission::class);

    expect($attributes)->toHaveCount(1);

    $instance = $attributes[0]->newInstance();

    expect($instance->name)->toBe('students.export')
        ->and($instance->label)->toBe('Export Students')
        ->and($instance->description)->toBe('Download a CSV of students');
});

it('reads a skip attribute', function () {
    $reflection = new ReflectionMethod(StudentController::class, 'ping');
    $attributes = $reflection->getAttributes(SkipPermission::class);

    expect($attributes)->toHaveCount(1);
});
