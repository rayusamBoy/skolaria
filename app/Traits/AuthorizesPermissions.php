<?php

namespace App\Traits;

use Illuminate\Support\Facades\Gate;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Supports:
 * - route-level permission via route action ['permission' => '...']
 * - PHP 8 attribute: #[\App\Auth\Attributes\Permission('users.create')]
 * - explicit $permissionMap['methodName'] = 'users.create' OR ['permission' => 'users.create', ...]
 * - wildcard map entries: 'create_*' => 'users.create'
 * - permissionPrefix auto-mapping: protected $permissionPrefix = 'users';
 */
trait AuthorizesPermissions
{
    /**
     * Optionally set in controllers:
     * protected array $permissionMap = [ 'index' => 'users.view', 'create_*' => 'users.create' ];
     * protected string $permissionPrefix = 'users';
     */
    public function bootPermissionMap(): void
    {
        $this->enforceResolvedPermission();
    }

    protected function getCurrentAction(): ?string
    {
        $route = request()->route();
        return $route?->getActionMethod();
    }

    protected function enforceResolvedPermission(): void
    {
        $action = $this->getCurrentAction();
        if (!$action) {
            return;
        }

        $permission = $this->resolvePermissionForAction($action);

        if ($permission === null) {
            return; // no rule for this action
        }

        // permission may be string or array
        if (\is_array($permission)) {
            if (!$this->userHasAny($permission)) {
                $this->forbidden();
            }
            return;
        }

        if (!$this->userHas($permission)) {
            $this->forbidden();
        }
    }

    /**
     * Resolve permission using precedence:
     * 1. route action ['permission']
     * 2. PHP 8 attribute #[Permission(...)]
     * 3. exact match in $permissionMap
     * 4. wildcard matches in $permissionMap (fnmatch)
     * 5. auto map with $permissionPrefix (e.g. 'users' + '.' + normalizedAction)
     *
     * @return string|array|null
     */
    protected function resolvePermissionForAction(string $action)
    {
        $route = request()->route();
        $actionArr = $route ? $route->getAction() : [];

        // 1) Route action explicit
        if (!empty($actionArr) && \array_key_exists('permission', $actionArr) && $actionArr['permission']) {
            return $actionArr['permission']; // can be string or array
        }

        // 2) PHP 8 attribute on method
        if (class_exists(ReflectionMethod::class)) {
            try {
                $ref = new ReflectionMethod($this, $action);
                $attributes = $ref->getAttributes(\App\Auth\Attributes\Permission::class);
                if (!empty($attributes)) {
                    $instance = $attributes[0]->newInstance();
                    return $instance->value ?? null;
                }
            } catch (\ReflectionException $e) {
                // method not found – skip
            }
        }

        // Helper to extract permission string from possible array value
        $extractPermissionString = function ($perm) {
            if (\is_array($perm) && isset($perm['permission'])) {
                return $perm['permission'];
            }
            return $perm;
        };

        // 3) explicit exact mapping
        if (property_exists($this, 'permissionMap') && \is_array($this->permissionMap)) {
            if (\array_key_exists($action, $this->permissionMap)) {
                $mapped = $this->permissionMap[$action];
                return $extractPermissionString($mapped);
            }
        }

        // 4) wildcard/pattern mapping
        if (property_exists($this, 'permissionMap') && \is_array($this->permissionMap)) {
            foreach ($this->permissionMap as $pattern => $perm) {
                if ($pattern === $action) {
                    continue;
                }
                $matches = false;
                if (function_exists('fnmatch')) {
                    $matches = @fnmatch($pattern, $action);
                } else {
                    $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
                    $matches = (bool) preg_match($regex, $action);
                }
                if ($matches) {
                    return $extractPermissionString($perm);
                }
            }
        }

        // 5) auto-map using $permissionPrefix
        if (property_exists($this, 'permissionPrefix') && $this->permissionPrefix) {
            $normalized = $this->normalizeActionNameForPermission($action);
            if ($normalized) {
                return "{$this->permissionPrefix}.{$normalized}";
            }
        }

        return null;
    }

    /**
     * Normalize controller action names to permission verbs.
     */
    protected function normalizeActionNameForPermission(string $action): ?string
    {
        $map = [
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'update',
            'update' => 'update',
            'destroy' => 'delete',
        ];

        if (isset($map[$action])) {
            return $map[$action];
        }

        if (str_contains($action, '_')) {
            $parts = explode('_', $action);
            $first = $parts[0];
            if (isset($map[$first])) {
                return $map[$first];
            }
        }

        return null;
    }

    /**
     * Check if the current user has a given permission (via Gate).
     */
    protected function userHas(string $permission): bool
    {
        return Gate::allows($permission);
    }

    /**
     * Check if the current user has any of the given permissions (via Gate).
     */
    protected function userHasAny(array $permissions): bool
    {
        return Gate::any($permissions);
    }

    /**
     * Throw a 403 HTTP exception.
     */
    protected function forbidden(): void
    {
        throw new HttpException(403);
    }

    /**
     * Convenience method to require a permission, optionally on a model.
     */
    protected function requirePermission(string $permission, $model = null): void
    {
        if ($model !== null) {
            $this->authorize($permission, $model);
            return;
        }

        if (!$this->userHas($permission)) {
            $this->forbidden();
        }
    }

    /**
     * Check if the user can do something (returns boolean, does not throw).
     */
    protected function canDo(string $permission, $model = null): bool
    {
        if ($model !== null) {
            try {
                $this->authorize($permission, $model);
                return true;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return $this->userHas($permission);
    }
}
