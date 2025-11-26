<?php
namespace App\Http\Controllers\Concerns;

use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Supports:
 * - route-level permission via route action ['permission' => '...']
 * - PHP 8 attribute: #[\App\Auth\Attributes\Permission('users.create')]
 * - explicit $permissionMap['methodName'] = 'users.create'
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
        if (property_exists($this, 'permissionMap') && is_array($this->permissionMap)) {
            $this->enforceResolvedPermission();
        } else {
            // still attempt prefix-based auto-mapping if prefix set
            $this->enforceResolvedPermission();
        }
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
        if (is_array($permission)) {
            if (!$this->userHasAny($permission))
                $this->forbidden();
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
     */
    protected function resolvePermissionForAction(string $action)
    {
        $route = request()->route();
        $actionArr = $route ? $route->getAction() : [];
        // 1) Route action explicit
        if (!empty($actionArr) && array_key_exists('permission', $actionArr) && $actionArr['permission']) {
            return $actionArr['permission'];
        }

        // 2) PHP 8 attribute on method (optional)
        if (class_exists(ReflectionMethod::class)) {
            try {
                $ref = new ReflectionMethod($this, $action);
                $attributes = $ref->getAttributes(\App\Auth\Attributes\Permission::class);
                if (!empty($attributes)) {
                    $instance = $attributes[0]->newInstance();
                    return $instance->value ?? null;
                }
            } catch (\ReflectionException $e) {
                // method not found via reflection - skip
            }
        }

        // 3) explicit exact mapping
        if (property_exists($this, 'permissionMap') && is_array($this->permissionMap) && array_key_exists($action, $this->permissionMap)) {
            return $this->permissionMap[$action];
        }

        // 4) wildcard/pattern mapping (use fnmatch)
        if (property_exists($this, 'permissionMap') && is_array($this->permissionMap)) {
            foreach ($this->permissionMap as $pattern => $perm) {
                // skip exact keys (already checked)
                if ($pattern === $action)
                    continue;

                // support '*' wildcard patterns like 'create_*' or '*_bulk'
                // fnmatch is not available on Windows by default - fall back to regex
                $matches = false;
                if (function_exists('fnmatch')) {
                    $matches = @fnmatch($pattern, $action);
                } else {
                    // convert wildcard to regex
                    $regex = '/^' . str_replace('\*', '.*', preg_quote($pattern, '/')) . '$/i';
                    $matches = (bool) preg_match($regex, $action);
                }

                if ($matches) {
                    return $perm;
                }
            }
        }

        // 5) auto-map using $permissionPrefix
        if (property_exists($this, 'permissionPrefix') && $this->permissionPrefix) {
            // normalize action such as create_user -> create
            // common map: index -> view, create/store -> create, edit/update -> update, destroy -> delete
            $normalized = $this->normalizeActionNameForPermission($action);
            if ($normalized) {
                return "{$this->permissionPrefix}.{$normalized}";
            }
        }

        return null;
    }

    /**
     * A small normalization helper:
     * create_user => create
     * store_user => create
     * edit_user => update
     * update_user => update
     * index => view
     * show => view
     * destroy => delete
     * custom -> return null
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

        // exact match first
        if (isset($map[$action]))
            return $map[$action];

        // try splitting by underscore and taking first segment
        if (str_contains($action, '_')) {
            $parts = explode('_', $action);
            $first = $parts[0];
            if (isset($map[$first]))
                return $map[$first];
        }

        return null;
    }

    protected function userHas(string $permission): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo($permission);
    }

    protected function userHasAny(array $permissions): bool
    {
        $user = auth()->user();
        return $user && method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($permissions);
    }

    protected function forbidden(): void
    {
        throw new HttpException(403);
    }

    // convenience for per-method checks after model binding
    protected function requirePermission(string $permission, $model = null): void
    {
        if ($model !== null) {
            $this->authorize($permission, $model);
            return;
        }

        if (!$this->userHas($permission))
            $this->forbidden();
    }

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
