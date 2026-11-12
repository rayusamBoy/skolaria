<?php

declare(strict_types=1);

namespace PermissionKit\Support;

final class PermissionDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $label,
        public readonly ?string $description,
        public readonly string $context,
        public readonly string $controller,
        public readonly string $method,
    ) {}
}
