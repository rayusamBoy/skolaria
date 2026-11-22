<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
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
;

it('provisions a tenant database and runs tenant migrations when a tenant is created', function () {
    $directory = storage_path('framework/testing-tenants');

    File::ensureDirectoryExists($directory);

    SQLiteDatabaseManager::$path = $directory;

    $databaseName = 'provisioning_' . Str::uuid() . '.sqlite';
    $databasePath = $directory . DIRECTORY_SEPARATOR . $databaseName;

    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => $databaseName,
        ]);

        $tenantStoragePath = tenancy()->run(
            $tenant,
            fn() => storage_path()
        );

        expect(File::isDirectory($tenantStoragePath))->toBeTrue();

        expect(File::exists($databasePath))->toBeTrue();

        tenancy()->initialize($tenant);

        expect(Schema::connection('tenant')->hasTable('users'))->toBeTrue()
            ->and(Schema::connection('tenant')->hasTable('settings'))->toBeTrue()
            ->and(Schema::connection('tenant')->hasTable('sessions'))->toBeTrue()
            ->and(Schema::connection('tenant')->hasTable('migrations'))->toBeTrue();

        $connection = DB::connection('tenant');

        expect($connection->table('exam_categories')
            ->where('name', 'Weekly')
            ->exists())->toBeTrue()

            ->and($connection->table('settings')
                ->where('key', 'system_name')
                ->where('value', 'YOUR SCHOOL NAME')
                ->exists())->toBeTrue()

            ->and($connection->table('roles')
                ->where('name', 'Super Admin')
                ->exists())->toBeTrue()

            ->and($connection->table('roles')
                ->where('name', 'Admin')
                ->exists())->toBeTrue()

            ->and($connection->table('users')
                ->where('username', 'superadmin')
                ->exists())->toBeTrue()

            ->and($connection->table('users')
                ->where('username', 'student')
                ->exists())->toBeTrue()

            ->and($connection->table('class_types')
                ->where('code', 'SE')
                ->exists())->toBeTrue()

            ->and($connection->table('my_classes')
                ->where('name', 'Form One')
                ->exists())->toBeTrue()

            ->and($connection->table('sections')
                ->where('name', 'A')
                ->exists())->toBeTrue();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if ($tenant) {
            Tenant::withoutEvents(fn() => $tenant->forceDelete());
        }

        DB::purge('tenant');

        if (File::exists($databasePath)) {
            File::delete($databasePath);
        }
    }
});
