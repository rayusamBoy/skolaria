<?php

declare(strict_types=1);

namespace PermissionKit\Support;

final class Context
{
    /**
     * @param  array<int, string>  $namespaces
     * @param  array<int, string>  $skip_names
     * @param  array<int, string>  $skip_paths
     * @param  array<int, string>  $exclude_namespaces
     * @param  array<int, string>  $extra_classes
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $prefix,
        public readonly array $namespaces,
        public readonly array $skip_names,
        public readonly array $skip_paths,
        public readonly array $exclude_namespaces = [],
        public readonly array $extra_classes = [],
    ) {}

    public static function fromConfig(string $key, array $config): self
    {
        return new self(
            key: $key,
            prefix: $config['prefix'] ?? null,
            namespaces: (array) ($config['namespaces'] ?? []),
            skip_names: (array) ($config['skip_names'] ?? []),
            skip_paths: (array) ($config['skip_paths'] ?? []),
            exclude_namespaces: (array) ($config['exclude_namespaces'] ?? []),
            extra_classes: (array) ($config['extra_classes'] ?? []),
        );
    }

    public function applyPrefix(string $name): string
    {
        return $this->prefix ? "{$this->prefix}.{$name}" : $name;
    }
}
