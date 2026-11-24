
<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;

beforeEach(function () {
    config([
        'tenancy.active_identification_mode' => 'request_data',

        'cache.default' => 'file',

        'cache.stores.file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'tenancy.cache.stores' => ['file'],
    ]);

    Artisan::call('migrate', [
        '--database' => config('tenancy.database.central_connection'),
        '--path' => database_path('migrations'),
        '--realpath' => true,
        '--force' => true,
    ]);
});

it('deletes tenant storage when a tenant is permanently deleted', function () {
    $databaseDirectory = storage_path('framework/testing-tenants');

    File::ensureDirectoryExists($databaseDirectory);

    SQLiteDatabaseManager::$path = $databaseDirectory;

    $tenantId = (string) Str::uuid();
    $databaseName = 'storage_deletion_'.$tenantId.'.sqlite';
    $databasePath = $databaseDirectory.DIRECTORY_SEPARATOR.$databaseName;

    $tenant = null;
    $tenantStoragePath = null;

    try {
        $tenant = Tenant::withoutEvents(fn () => Tenant::create([
            'id' => $tenantId,
            'tenancy_db_name' => $databaseName,
        ]));

        $manager = new SQLiteDatabaseManager;
        $manager->createDatabase($tenant);

        $tenantStoragePath = tenancy()->run(
            $tenant,
            fn () => storage_path()
        );

        File::ensureDirectoryExists($tenantStoragePath);

        File::put(
            $tenantStoragePath.DIRECTORY_SEPARATOR.'deletion-test.txt',
            'Tenant storage'
        );

        expect(File::isDirectory($tenantStoragePath))->toBeTrue();

        $tenant->forceDelete();

        expect(File::exists($tenantStoragePath))->toBeFalse();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if ($tenant && Tenant::withTrashed()->whereKey($tenantId)->exists()) {
            Tenant::withoutEvents(fn () => $tenant->forceDelete());
        }

        if (File::exists($tenantStoragePath ?? '')) {
            File::deleteDirectory($tenantStoragePath);
        }

        if (File::exists($databasePath)) {
            File::delete($databasePath);
        }
    }
});
