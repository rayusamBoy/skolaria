<?php

use App\Models\Tenant;
use App\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;

const AUTH_TENANT_A = '01900000-0000-7000-8000-000000000011';
const AUTH_TENANT_B = '01900000-0000-7000-8000-000000000012';
define('TENANT_DATABASE_PATH', dirname(__DIR__, 3).'/storage/framework/testing/tenant-databases');
define('ORIGINAL_SQLITE_PATH', SQLiteDatabaseManager::$path);

beforeEach(function () {
    if (! is_dir(TENANT_DATABASE_PATH)) {
        mkdir(TENANT_DATABASE_PATH, 0777, true);
    }

    SQLiteDatabaseManager::$path = TENANT_DATABASE_PATH;

    config([
        'tenancy.bootstrappers' => [
            DatabaseTenancyBootstrapper::class,
        ],
    ]);

    Schema::create('tenants', function ($table) {
        $table->uuid('id')->primary();
        $table->timestamps();
        $table->json('data')->nullable();
        $table->softDeletes();
    });

    Tenant::withoutEvents(function () {
        Tenant::create(['id' => AUTH_TENANT_A]);
        Tenant::create(['id' => AUTH_TENANT_B]);
    });

    foreach ([AUTH_TENANT_A, AUTH_TENANT_B] as $tenantId) {
        $tenant = Tenant::findOrFail($tenantId);

        $tenant->database()->manager()->createDatabase($tenant);

        tenancy()->initialize($tenant);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => database_path('migrations/tenant'),
            '--realpath' => true,
            '--force' => true,
        ]);

        tenancy()->end();
    }
});

afterEach(function () {
    tenancy()->end();

    SQLiteDatabaseManager::$path = ORIGINAL_SQLITE_PATH;

    foreach ([AUTH_TENANT_A, AUTH_TENANT_B] as $tenantId) {
        $file = TENANT_DATABASE_PATH.DIRECTORY_SEPARATOR.'tenant'.$tenantId.'.sqlite';

        if (file_exists($file)) {
            unlink($file);
        }
    }
});

it('does not authenticate a tenant user in another tenant database', function () {
    $tenantA = Tenant::findOrFail(AUTH_TENANT_A);
    $tenantB = Tenant::findOrFail(AUTH_TENANT_B);

    tenancy()->initialize($tenantA);

    $user = User::create([
        'name' => 'Tenant A User',
        'religion' => 'Islam',
        'code' => 'TENANT-A-USER',
        'username' => 'tenant_a_user',
        'password' => Hash::make('password'),
    ]);

    $userId = $user->id;

    tenancy()->end();

    $this->withCookie('tenant', AUTH_TENANT_A)
        ->actingAs($user, 'web');

    $this->get('/dashboard')
        ->assertRedirect('/my-account/profile/edit');

    tenancy()->initialize($tenantB);

    expect(User::find($userId))->toBeNull();
});
