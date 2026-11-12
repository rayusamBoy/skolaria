<?php

declare(strict_types=1);

namespace PermissionKit;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Services\PermissionCollector;
use Spatie\Permission\Models\Permission;

/**
 * Core entry point. Exposes a small, framework‑agnostic API delegating to Spatie.
 */
class PermissionKit
{
    public function __construct(protected Application $app) {}

    /**
     * Check a single permission. Delegates to Gate (which Spatie hooks into).
     */
    public function can(string $permission, $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Check if the user has any of the given permissions.
     */
    public function canAny(array $permissions, $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }

        foreach ($permissions as $p) {
            if ($user->can($p)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has all of the given permissions.
     */
    public function canAll(array $permissions, $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user) {
            return false;
        }

        foreach ($permissions as $p) {
            if (! $user->can($p)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Alias for can() for readability in some contexts.
     */
    public function isGranted(string $permission, $user = null): bool
    {
        return $this->can($permission, $user);
    }

    /**
     * List all permission names (optionally filtered by context prefix).
     */
    public function all(?string $context = null): Collection
    {
        $query = Permission::query();

        if ($context !== null) {
            $prefix = config("permission-kit.contexts.{$context}.prefix");
            if ($prefix) {
                $query->where('name', 'like', $prefix.'.%');
            }
        }

        return $query->pluck('name');
    }

    /**
     * Get permission names for a specific user.
     */
    public function forUser($user = null): array
    {
        $user ??= auth()->user();
        if (! $user) {
            return [];
        }

        return $user->getAllPermissions()->pluck('name')->all();
    }

    /**
     * Read the active context key.
     */
    public function currentContext(): string
    {
        return $this->app->make(ContextResolver::class)->resolve();
    }

    /**
     * Scan the configured controllers and return all discovered definitions
     * without touching the database. Useful for testing and dry runs.
     */
    public function scan(?string $context = null): Collection
    {
        return $this->app->make(PermissionCollector::class)->collect($context);
    }
}
