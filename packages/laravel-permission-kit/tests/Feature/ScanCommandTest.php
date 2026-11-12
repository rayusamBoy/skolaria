<?php

use PermissionKit\Services\PermissionCollector;

it('discovers users controller permissions', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $names = $definitions->pluck('name')->all();

    expect($names)->toContain('users.view')
        ->and($names)->toContain('users.create')
        ->and($names)->toContain('users.delete');
});

it('discovers explicit permission attributes', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $names = $definitions->pluck('name')->all();

    expect($names)->toContain('students.export');
});

it('skips methods with skip permission attribute', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $methods = $definitions->pluck('method')->all();

    expect($methods)->not->toContain('ping');
});

it('skips controllers in skip_names', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $controllers = $definitions->pluck('controller')->unique()->all();

    expect($controllers)->not->toContain('SkipMeController');
});

it('skips abstract controllers', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $controllers = $definitions->pluck('controller')->unique()->all();

    expect($controllers)->not->toContain('AbstractController');
});

it('skips paths matching skip_paths', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $controllers = $definitions->pluck('controller')->unique()->all();

    expect($controllers)->not->toContain('SkippedPathController');
    expect($controllers)->not->toContain('IgnoredController');
});

it('ignores classes without Controller suffix', function () {
    $definitions = app(PermissionCollector::class)->collect();

    $controllers = $definitions->pluck('controller')->unique()->all();

    expect($controllers)->not->toContain('PlainHelper');
});

it('prints a console table', function () {
    $this->artisan('permission-kit:scan')
        ->expectsOutputToContain('users.view')
        ->assertSuccessful();
});

it('prints JSON output', function () {
    $this->artisan('permission-kit:scan', ['--format' => 'json'])
        ->expectsOutputToContain('"name"')
        ->assertSuccessful();
});

it('prints PHP snippet', function () {
    $this->artisan('permission-kit:scan', ['--format' => 'php'])
        ->expectsOutputToContain("'permission'  => 'users.view'")
        ->assertSuccessful();
});
