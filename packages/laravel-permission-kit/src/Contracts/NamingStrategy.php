<?php

declare(strict_types=1);

namespace PermissionKit\Contracts;

use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;
use ReflectionClass;
use ReflectionMethod;

interface NamingStrategy
{
    public function build(ReflectionClass $class, ReflectionMethod $method, Context $context, bool $apply_prefix = true): PermissionDefinition;
}
