<?php

declare(strict_types=1);

namespace PermissionKit;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use PermissionKit\Context\DefaultContextResolver;
use PermissionKit\Contracts\ContextResolver;
use PermissionKit\Contracts\NamingStrategy;
use PermissionKit\Contracts\Scanner;
use PermissionKit\Http\Middleware\EnforceControllerPermissions;
use PermissionKit\Http\Middleware\EnsurePermission;
use PermissionKit\Services\ControllerScanner;
use PermissionKit\Services\PermissionManifest;
use PermissionKit\Services\PermissionNameBuilder;
use PermissionKit\Services\PermissionSyncService;
use Spatie\Permission\PermissionRegistrar;

class PermissionKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/permission-kit.php', 'permission-kit');

        $this->app->singleton(PermissionKit::class, fn (Application $app) => new PermissionKit($app));

        $this->app->singleton(ContextResolver::class, function (Application $app) {
            $class = config('permission-kit.context_resolver');

            return $class ? $app->make($class) : new DefaultContextResolver;
        });

        $this->app->bind(NamingStrategy::class, PermissionNameBuilder::class);
        $this->app->bind(Scanner::class, ControllerScanner::class);

        $this->app->singleton(PermissionSyncService::class, fn (Application $app) => new PermissionSyncService(
            $app->make(PermissionRegistrar::class),
            (string) config('permission-kit.guard', 'web'),
        ));

        $this->app->singleton(PermissionManifest::class, function ($app) {
            $path = (string) config('permission-kit.manifest_path', '');

            return new PermissionManifest($path);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/permission-kit.php' => config_path('permission-kit.php'),
        ], 'permission-kit-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations/2025_01_01_000001_add_label_and_description_to_permissions_table.php' => database_path('migrations/2025_01_01_000001_add_label_and_description_to_permissions_table.php'),
        ], 'permission-kit-migrations');

        $this->commands([
            Console\InstallCommand::class,
            Console\ScanCommand::class,
            Console\SyncCommand::class,
            Console\PruneCommand::class,
            Console\ClearCacheCommand::class,
        ]);

        if (config('permission-kit.register_middleware', true)) {
            $this->app->make('router')->aliasMiddleware('permission.kit', EnsurePermission::class);
        }

        // Automatically enforce trait-based permissions by appending the
        // middleware to the configured groups. Only routes whose controller
        // actually uses the trait will be inspected.
        if (config('permission-kit.enforce', true)) {
            /** @var \Illuminate\Foundation\Http\Kernel $kernel */
            $kernel = $this->app->make(Kernel::class);
            $groups = (array) config('permission-kit.enforce_middleware_groups', ['web']);

            foreach ($groups as $group) {
                $kernel->appendMiddlewareToGroup($group, EnforceControllerPermissions::class);
            }
        }
    }
}
