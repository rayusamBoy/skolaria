<?php

declare(strict_types=1);

namespace PermissionKit\Contracts;

interface ContextResolver
{
    /**
     * Return the active context key (must exist in config('permission-kit.contexts')).
     */
    public function resolve(): string;
}
