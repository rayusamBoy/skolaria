<?php

declare(strict_types=1);

namespace PermissionKit\Context;

use PermissionKit\Contracts\ContextResolver;

class DefaultContextResolver implements ContextResolver
{
    public function resolve(): string
    {
        $contexts = array_keys(config('permission-kit.contexts', []));

        return $contexts[0] ?? 'default';
    }
}
