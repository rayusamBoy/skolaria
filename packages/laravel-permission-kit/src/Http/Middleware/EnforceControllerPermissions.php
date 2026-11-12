<?php

declare(strict_types=1);

namespace PermissionKit\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PermissionKit\Attributes\Permission;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Concerns\AuthorizesPermissions;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Facades\PermissionKit;
use PermissionKit\Services\PermissionManifest;
use PermissionKit\Services\PermissionNameBuilder;
use PermissionKit\Support\Context;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Enforces the implicit permission for any controller action where the
 * controller uses the AuthorizesPermissions trait.
 *
 * Runs as part of the configured middleware groups (default: web). The
 * controller is never instantiated here — reflection is enough, and the
 * dispatcher will construct it normally later.
 */
class EnforceControllerPermissions
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('permission-kit.enforce', true)) {
            return $next($request);
        }

        $route = $request->route();
        if (! $route) {
            return $next($request);
        }

        $controllerClass = $route->getControllerClass();
        if (! $controllerClass || ! class_exists($controllerClass)) {
            return $next($request);
        }

        if (! \in_array(AuthorizesPermissions::class, class_uses_recursive($controllerClass), true)) {
            return $next($request);
        }

        $action = $route->getActionMethod();
        if (! $action) {
            return $next($request);
        }

        try {
            $methodReflection = new ReflectionMethod($controllerClass, $action);
        } catch (\ReflectionException) {
            return $next($request);
        }

        // Skip when opted out at method or class level.
        if (! empty($methodReflection->getAttributes(SkipPermission::class))) {
            return $next($request);
        }

        if (! empty((new ReflectionClass($controllerClass))->getAttributes(SkipPermission::class))) {
            return $next($request);
        }

        $permission = $this->resolvePermission($request, $controllerClass, $methodReflection);

        if ($permission === null) {
            return $next($request);
        }

        $granted = \is_array($permission)
            ? PermissionKit::canAny($permission)
            : PermissionKit::can($permission);

        if (! $granted) {
            $this->handleMissingPermission($permission);

            return $next($request); // only reached in debug mode
        }

        return $next($request);
    }

    protected function handleMissingPermission(string|array $permission): void
    {
        $isRegistered = ! \is_array($permission) && \Spatie\Permission\Models\Permission::query()
            ->where('name', $permission)
            ->where('guard_name', config('permission-kit.guard', 'web'))
            ->exists();

        if (! $isRegistered && config('permission-kit.log_unregistered_permissions', true)) {
            logger()->warning(
                '[permission-kit] Permission resolved but not registered in DB: '
                .(\is_array($permission) ? implode(', ', $permission) : $permission)
                .'. Run `php artisan permission-kit:sync`.'
            );

            if (config('app.debug')) {
                return;
            }
        }

        throw new HttpException(403);
    }

    /**
     * Resolve the permission the same way the scanner does.
     *
     * @return string|array<int, string>|null
     */
    protected function resolvePermission(
        Request $request,
        string $controllerClass,
        ReflectionMethod $method
    ): string|array|null {
        // Route action attribute
        $routePermission = $request->route()?->getAction('permission');
        if (! empty($routePermission)) {
            return $routePermission;
        }

        // Method attribute
        $attributes = $method->getAttributes(Permission::class);
        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->name;
        }

        $contextKey = app(ContextResolver::class)->resolve();
        $shortName = (new ReflectionClass($controllerClass))->getShortName();

        // Cache lookup
        if (config('permission-kit.cache_resolved_names', true)) {
            $version = Cache::get('permission_kit.cache_version', 1);
            $cacheKey = "permission_kit.name:v{$version}:{$contextKey}:{$shortName}:{$method->getName()}";

            return Cache::remember(
                $cacheKey,
                (int) config('permission-kit.cache_ttl', 86400),
                fn (): mixed => $this->buildName($controllerClass, $method, $contextKey, $shortName)
            );
        }

        return $this->buildName($controllerClass, $method, $contextKey, $shortName);
    }

    /**
     * Resolve the permission name via the manifest (preferred) or a live scan.
     */
    protected function buildName(string $controllerClass, ReflectionMethod $method, string $contextKey, string $shortName): string
    {
        // Manifest lookup
        $manifest = app(PermissionManifest::class);
        if ($manifest->exists()) {
            $name = $manifest->find($contextKey, $shortName, $method->getName());
            if ($name !== null) {
                return $name;
            }
        }

        // Live scan
        $contextConfig = (array) config("permission-kit.contexts.{$contextKey}", []);

        return app(PermissionNameBuilder::class)
            ->build(
                new ReflectionClass($controllerClass),
                $method,
                Context::fromConfig($contextKey, $contextConfig)
            )
            ->name;
    }
}
