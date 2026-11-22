
<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;

uses(RefreshDatabase::class);

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
});

it('deletes tenant resources when a tenant is permanently deleted', function () {
    $directory = storage_path('framework/testing-tenants');

    File::ensureDirectoryExists($directory);

    SQLiteDatabaseManager::$path = $directory;

    $tenantId = (string) Str::uuid();
    $databaseName = 'deletion_' . $tenantId . '.sqlite';
    $databasePath = $directory . DIRECTORY_SEPARATOR . $databaseName;

    $themeDir = resource_path(
        'views/pages/students/id_cards/themes/'
        . config('tenancy.database.prefix')
        . $tenantId
        . '/'
    );

    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => $tenantId,
            'tenancy_db_name' => $databaseName,
        ]);

        File::ensureDirectoryExists($themeDir);
        File::put($themeDir . 'test-theme.php', '<?php // test');

        expect(File::exists($databasePath))->toBeTrue()
            ->and(File::isDirectory($themeDir))->toBeTrue();

        $tenant->forceDelete();

        expect(File::exists($databasePath))->toBeFalse()
            ->and(File::isDirectory($themeDir))->toBeFalse()
            ->and(Tenant::withTrashed()->find($tenantId))->toBeNull();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if ($tenant && Tenant::withTrashed()->whereKey($tenantId)->exists()) {
            Tenant::withoutEvents(fn () => $tenant->forceDelete());
        }

        File::deleteDirectory($themeDir);

        if (File::exists($databasePath)) {
            File::delete($databasePath);
        }
    }
});
