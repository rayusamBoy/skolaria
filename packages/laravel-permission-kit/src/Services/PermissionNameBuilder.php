<?php

declare(strict_types=1);

namespace PermissionKit\Services;

use Illuminate\Support\Str;
use PermissionKit\Attributes\Permission;
use PermissionKit\Contracts\NamingStrategy;
use PermissionKit\Support\Context;
use PermissionKit\Support\PermissionDefinition;
use ReflectionClass;
use ReflectionMethod;

class PermissionNameBuilder implements NamingStrategy
{
    public function build(ReflectionClass $class, ReflectionMethod $method, Context $context, bool $apply_prefix = true): PermissionDefinition
    {
        $method_name = $method->getName();

        // Method attribute override (Attributes are absolute and never prefixed)
        $attributes = $method->getAttributes(Permission::class);
        if (! empty($attributes)) {
            $instance = $attributes[0]->newInstance();

            return new PermissionDefinition(
                name: $instance->name,
                label: $instance->label,
                description: $instance->description,
                context: $context->key,
                controller: $class->getShortName(),
                method: $method_name,
            );
        }

        // Config overrides by full class name, short class name, or resource name
        $resource = $this->resourceFromController($class->getShortName());
        $overrides = config('permission-kit.overrides', []);

        $override = $overrides[$class->getName()][$method_name]
            ?? $overrides[$class->getShortName()][$method_name]
            ?? $overrides[$resource][$method_name]
            ?? null;

        if ($override !== null) {
            if (\is_array($override)) {
                $base_name = $this->buildName($resource, $method_name);
                $name = $override['permission'] ?? ($apply_prefix ? $context->applyPrefix($base_name) : $base_name);

                return new PermissionDefinition(
                    name: $name,
                    label: $override['label'] ?? null,
                    description: $override['description'] ?? null,
                    context: $context->key,
                    controller: $class->getShortName(),
                    method: $method_name,
                );
            }

            // String override
            return new PermissionDefinition(
                name: (string) $override,
                label: null,
                description: null,
                context: $context->key,
                controller: $class->getShortName(),
                method: $method_name,
            );
        }

        // Auto mapping
        $base_name = $this->buildName($resource, $method_name);
        $name = $apply_prefix ? $context->applyPrefix($base_name) : $base_name;

        return new PermissionDefinition(
            name: $name,
            label: Str::headline(str_replace('.', ' ', $name)),
            description: null,
            context: $context->key,
            controller: $class->getShortName(),
            method: $method_name,
        );
    }

    /**
     * Build the permission name for a controller method.
     */
    protected function buildName(string $resource, string $method_name): string
    {
        $segments = $this->toSegments($method_name);
        $use_dots = $this->detectDotStyle($method_name);
        $separator = $use_dots ? '.' : '_';

        $method_map = config('permission-kit.method_map', []);
        $verb = $segments[0] ?? '';
        $tail = \array_slice($segments, 1);

        if ($verb !== '' && isset($method_map[$verb])) {
            $combined = array_merge([$method_map[$verb]], $tail);

            return "$resource.".implode($separator, $combined);
        }

        $suffix = implode($separator, $segments);

        return "{$resource}.{$suffix}";
    }

    /**
     * Split a method name into lowercase word segments, handling both
     * snake_case (update_lock_state), camelCase (updateLockState), and
     * acronyms (getHTMLContent → get_html_content).
     *
     * @return array<int, string>
     */
    protected function toSegments(string $method_name): array
    {
        // Insert a delimiter between a run of uppercase letters and a
        //    following mixed-case word: HTMLContent → HTML_Content
        $snake = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $method_name);

        // Insert a delimiter between a lowercase letter or digit and an
        //    uppercase letter: updateLockState → update_Lock_State
        $snake = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $snake);

        // Lowercase everything: UPDATE_LOCK_STATE → update_lock_state
        $snake = strtolower($snake);

        // Split on any run of non-alphanumeric characters
        $parts = preg_split('/[^a-z0-9]+/', $snake) ?: [];

        return array_values(array_filter($parts, fn ($p) => $p !== ''));
    }

    /**
     * Decide whether the separator between segments should be dots or
     * underscores, honouring the config setting.
     */
    protected function detectDotStyle(string $method_name): bool
    {
        $config = config('permission-kit.use_dots', false);

        if ($config === 'auto') {
            // camelCase → dots; snake_case → underscores.
            // A single-word method ("store") falls back to underscores
            // because there is nothing to join.
            return (bool) preg_match('/[a-z][A-Z]/', $method_name);
        }

        return (bool) $config;
    }

    /**
     * Convert "UserController" → "users".
     */
    protected function resourceFromController(string $short_name): string
    {
        $base = preg_replace('/Controller$/', '', $short_name);

        return Str::snake(Str::plural($base));
    }
}
