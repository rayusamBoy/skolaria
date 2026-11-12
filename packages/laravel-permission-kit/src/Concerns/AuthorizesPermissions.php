<?php

declare(strict_types=1);

namespace PermissionKit\Concerns;

use PermissionKit\Attributes\Permission;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Facades\PermissionKit;
use PermissionKit\Services\PermissionNameBuilder;
use PermissionKit\Support\Context;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Automatically enforce the resolved permission for the current controller
 * action. Enforcement runs through EnforceControllerPermissions middleware
 * for any controller that uses this trait.
 *
 * Opt out per method or per class with #[SkipPermission].
 *
 * bootAuthorizesPermissions() remains available for manual invocation if you
 * prefer to disable the automatic middleware and call it from a constructor.
 */
trait AuthorizesPermissions
{
    protected function bootAuthorizesPermissions(): void
    {
        if (! config('permission-kit.enforce', true)) {
            return;
        }

        $action = request()->route()?->getActionMethod();
        if (! $action) {
            return;
        }

        if ($this->authorizesPermissionsShouldSkip($action)) {
            return;
        }

        $permission = $this->authorizesPermissionsResolve($action);

        if ($permission === null) {
            return;
        }

        $granted = \is_array($permission)
            ? PermissionKit::canAny($permission)
            : PermissionKit::can($permission);

        if (! $granted) {
            throw new HttpException(403);
        }
    }

    protected function authorizesPermissionsShouldSkip(string $action): bool
    {
        try {
            $method = new ReflectionMethod($this, $action);
        } catch (\ReflectionException) {
            return true;
        }

        if (! empty($method->getAttributes(SkipPermission::class))) {
            return true;
        }

        if (! empty((new ReflectionClass($this))->getAttributes(SkipPermission::class))) {
            return true;
        }

        return false;
    }

    /**
     * @return string|array<int, string>|null
     */
    protected function authorizesPermissionsResolve(string $action): string|array|null
    {
        // 1. Route action attribute
        $route_permission = request()->route()?->getAction('permission');
        if (! empty($route_permission)) {
            return $route_permission;
        }

        // 2. Method attribute
        try {
            $method = new ReflectionMethod($this, $action);
            $attributes = $method->getAttributes(Permission::class);
            if (! empty($attributes)) {
                return $attributes[0]->newInstance()->name;
            }
        } catch (\ReflectionException) {
            return null;
        }

        // 3. Scanner lookup (delegates to the same NamingStrategy the sync uses)
        return app(PermissionNameBuilder::class)
            ->build(new ReflectionClass($this), $method, Context::fromConfig(
                app(ContextResolver::class)->resolve(),
                config('permission-kit.contexts.'.app(ContextResolver::class)->resolve(), [])
            ))
            ->name;
    }
}
