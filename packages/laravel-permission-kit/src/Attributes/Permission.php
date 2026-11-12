<?php

declare(strict_types=1);

namespace PermissionKit\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Permission
{
    public function __construct(
        public string $name,
        public ?string $label = null,
        public ?string $description = null,
    ) {}
}
