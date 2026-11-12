<?php

declare(strict_types=1);

namespace PermissionKit\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PermissionContext
{
    public function __construct(public string $key) {}
}
