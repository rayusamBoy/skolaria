<?php

declare(strict_types=1);

namespace PermissionKit\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PermissionKit\Facades\PermissionKit;
use PermissionKit\PermissionKitServiceProvider;
use PermissionKit\Tests\Fixtures\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PermissionServiceProvider::class,
            PermissionKitServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'PermissionKit' => PermissionKit::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Auth model = fixture User (uses Spatie's HasRoles trait)
        $app['config']->set('auth.providers.users.model', User::class);

        // Scanner must read the package's composer.json so it can find
        // the fixture PSR-4 mappings (PermissionKit\Tests\ → tests/).
        $app['config']->set('permission-kit.composer_json_path', dirname(__DIR__).'/composer.json');

        // Context config for the fixture controllers.
        $app['config']->set('permission-kit.contexts', [
            'default' => [
                'prefix' => null,
                'namespaces' => ['PermissionKit\\Tests\\Fixtures\\Http\\Controllers'],
                'skip_names' => ['SkipMeController', 'AbstractController'],
                'skip_paths' => ['Fixtures/Http/Controllers/Skip', 'Fixtures/Http/Controllers/Ignored'],
            ],
        ]);

        $app['config']->set('permission-kit.enforce', true);
        $app['config']->set('permission-kit.use_dots', false);
        $app['config']->set('permission-kit.allow_prune', false);
        $app['config']->set('permission-kit.sync_mode', 'update');
        $app['config']->set('permission-kit.context_resolver', null);

        // Stub Spatie's default table names — already the defaults, but explicit.
        $app['config']->set('permission.table_names', [
            'roles' => 'roles',
            'permissions' => 'permissions',
            'model_has_permissions' => 'model_has_permissions',
            'model_has_roles' => 'model_has_roles',
            'role_has_permissions' => 'role_has_permissions',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $this->loadLaravelMigrations();

        // Load Spatie's own migration from its stub file — keeps tests in sync
        // with whatever Spatie version composer installed.
        $this->loadSpatieMigrationsFromStub();

        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
    }

    /**
     * Copy Spatie's migration stub to a build directory as a real .php migration,
     * then load that directory. Reflection-based lookup so it works regardless of
     * the current working directory or the vendor location.
     */
    protected function loadSpatieMigrationsFromStub(): void
    {
        $stub = $this->findSpatieMigrationStub();

        if ($stub === null) {
            throw new \RuntimeException(
                'Spatie permission migration stub not found. Is spatie/laravel-permission installed?'
            );
        }

        $targetDir = dirname(__DIR__).'/.build/spatie-migrations';

        if (! is_dir($targetDir) && ! mkdir($targetDir, 0755, true) && ! is_dir($targetDir)) {
            throw new \RuntimeException("Could not create directory: {$targetDir}");
        }

        // Fixed timestamp so migration ordering is deterministic.
        $target = "$targetDir/2024_01_01_000000_create_permission_tables.php";

        // Always overwrite — guarantees the tests run against the currently installed version.
        if (! copy($stub, $target)) {
            throw new \RuntimeException("Failed to copy Spatie migration stub to {$target}");
        }

        $this->loadMigrationsFrom($targetDir);
    }

    /**
     * Locate Spatie's create_permission_tables.php.stub by walking up from its
     * Permission model class file. Survives package moves and custom vendor paths.
     */
    protected function findSpatieMigrationStub(): ?string
    {
        $reflection = new \ReflectionClass(Permission::class);

        // .../vendor/spatie/laravel-permission/src/Models/Permission.php
        $packageRoot = dirname($reflection->getFileName(), 3);

        $candidates = [
            "$packageRoot/database/migrations/create_permission_tables.php.stub",
            "$packageRoot/database/migrations/create_permission_tables.stub",
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
