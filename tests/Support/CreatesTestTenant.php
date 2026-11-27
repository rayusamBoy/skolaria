<?php

declare(strict_types=1);

namespace Tests\\Support;

use App\\Models\\Tenant;
use Illuminate\\Support\\Facades\\Artisan;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\File;
use Illuminate\\Support\\Str;
use Stancl\\Tenancy\\Database\\TenantDatabaseManagers\\SQLiteDatabaseManager;

trait CreatesTestTenant
{
    protected array $testTenants = [];

    protected array $testTenantDatabasePaths = [];

    protected array $testTenantStoragePaths = [];

    protected function createTestTenant(): Tenant
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        $directory = storage_path('framework/testing-tenants');

        File::ensureDirectoryExists($directory);

        $id = (string) Str::uuid();
        $databaseName = "test_tenant_{$id}.sqlite";
        $databasePath = $directory.DIRECTORY_SEPARATOR.$databaseName;

        $tenant = Tenant::withoutEvents(function () use ($id, $databaseName) {
            return Tenant::create([
                'id' => $id,
                'tenancy_db_name' => $databaseName,
            ]);
        });

        SQLiteDatabaseManager::$path = $directory;

        $manager = new SQLiteDatabaseManager;
        $manager->createDatabase($tenant);

        $this->testTenants[] = $tenant;
        $this->testTenantDatabasePaths[] = $databasePath;

        config([
            'cache.default' => 'file',

            'cache.stores.file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data'),
                'lock_path' => storage_path('framework/cache/data'),
            ],

            'tenancy.cache.stores' => ['file'],
        ]);

        tenancy()->initialize($tenant);

        $this->testTenantStoragePaths[] = storage_path();

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => database_path('migrations/tenant'),
            '--realpath' => true,
            '--force' => true,
        ]);

        return $tenant;
    }

    protected function destroyTestTenant(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        foreach ($this->testTenants as $tenant) {
            Tenant::withoutEvents(fn () => $tenant->delete());
        }

        // Close SQLite PDO handles before unlinking files (required on Windows).
        DB::purge('tenant');

        foreach ($this->testTenantDatabasePaths as $path) {
            File::delete([
                $path,
                $path.'-journal',
                $path.'-wal',
                $path.'-shm',
            ]);
        }

        foreach ($this->testTenantStoragePaths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }

        $this->testTenants = [];
        $this->testTenantDatabasePaths = [];
        $this->testTenantStoragePaths = [];
    }

    protected function createAdditionalTestTenant(): Tenant
    {
        return $this->createTestTenant();
    }
}
