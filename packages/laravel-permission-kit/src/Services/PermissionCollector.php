<?php

declare(strict_types=1);

namespace PermissionKit\Services;

use Illuminate\Support\Collection;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Contracts\Scanner;
use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;

class PermissionCollector
{
    /** @var array<string, Collection<int, PermissionDefinition>>|null */
    protected ?array $cache = null;

    public function __construct(
        protected Scanner $scanner,
        protected ContextResolver $resolver,
    ) {}

    /**
     * @return Collection<int, PermissionDefinition>
     */
    public function collect(?string $context_key = null): Collection
    {
        $contexts = $this->contextsToScan($context_key);

        $all = collect();
        foreach ($contexts as $key => $config) {
            $all = $all->merge($this->scanContext($key, $config));
        }

        return $all->unique('name')->values();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function contextsToScan(?string $context_key): array
    {
        $contexts = config('permission-kit.contexts', []);

        if ($context_key === null) {
            return $contexts;
        }

        if (! isset($contexts[$context_key])) {
            return [];
        }

        return [$context_key => $contexts[$context_key]];
    }

    /**
     * @return Collection<int, PermissionDefinition>
     */
    protected function scanContext(string $key, array $config): Collection
    {
        $this->cache ??= [];

        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $context = Context::fromConfig($key, $config);

        return $this->cache[$key] = $this->scanner->scan($context);
    }
}
