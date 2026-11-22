
<?php

use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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

it('deletes tenant domains without affecting other tenants', function () {
    $tenantA = null;
    $tenantB = null;

    $centralConnection = config('tenancy.database.central_connection');

    try {
        $tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_deletion_a_' . Str::uuid() . '.sqlite',
        ]);

        $tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_deletion_b_' . Str::uuid() . '.sqlite',
        ]);

        $domainA = Str::lower('tenant-a-' . Str::random(8) . '.test');
        $domainB = Str::lower('tenant-b-' . Str::random(8) . '.test');

        $tenantA->domains()->create(['domain' => $domainA]);
        $tenantB->domains()->create(['domain' => $domainB]);

        $central = DB::connection($centralConnection);

        expect($central->table('domains')->where('domain', $domainA)->exists())->toBeTrue()
            ->and($central->table('domains')->where('domain', $domainB)->exists())->toBeTrue();

        $tenantA->forceDelete();

        expect($central->table('domains')->where('domain', $domainA)->exists())->toBeFalse()
            ->and($central->table('domains')->where('domain', $domainB)->exists())->toBeTrue()
            ->and(Tenant::withTrashed()->whereKey($tenantA->getKey())->exists())->toBeFalse()
            ->and(Tenant::withTrashed()->whereKey($tenantB->getKey())->exists())->toBeTrue();
    } finally {
        foreach ([$tenantA, $tenantB] as $tenant) {
            if ($tenant && Tenant::withTrashed()->whereKey($tenant->getKey())->exists()) {
                Tenant::withoutEvents(fn () => $tenant->forceDelete());
            }
        }
    }
});
