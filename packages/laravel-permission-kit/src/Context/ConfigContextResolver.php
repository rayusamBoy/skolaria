<?php

declare(strict_types=1);

namespace PermissionKit\Context;

use PermissionKit\Contracts\ContextResolver;

/**
 * Simplest configurable resolver: reads a config key that the host app
 * can toggle (e.g. in a tenancy middleware).
 */
class ConfigContextResolver implements ContextResolver
{
    public function resolve(): string
    {
        return (string) config('permission-kit.active_context', array_key_first(config('permission-kit.contexts')));
    }
}
