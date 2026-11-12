<?php

use PermissionKit\Support\Context;

it('builds from config with defaults', function () {
    $context = Context::fromConfig('tenant', [
        'prefix' => null,
        'namespaces' => ['App\\Http\\Controllers'],
    ]);

    expect($context->key)->toBe('tenant')
        ->and($context->prefix)->toBeNull()
        ->and($context->namespaces)->toBe(['App\\Http\\Controllers'])
        ->and($context->skip_names)->toBe([])
        ->and($context->skip_paths)->toBe([]);
});

it('applies the prefix when set', function () {
    $context = new Context('central', 'central', [], [], []);

    expect($context->applyPrefix('users.view'))->toBe('central.users.view');
});

it('returns the input untouched when prefix is null', function () {
    $context = new Context('default', null, [], [], []);

    expect($context->applyPrefix('users.view'))->toBe('users.view');
});
