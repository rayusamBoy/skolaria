
<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

it('creates and associates a domain with its tenant', function () {
    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_creation_'.Str::uuid().'.sqlite',
        ]);

        $domain = 'Tenant-'.Str::random(8).'.Test';
        $normalizedDomain = strtolower($domain);

        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        $central = DB::connection(
            config('tenancy.database.central_connection')
        );

        $savedDomain = $central->table('domains')
            ->where('domain', $normalizedDomain)
            ->first();

        expect($savedDomain)->not->toBeNull()
            ->and($savedDomain->tenant_id)->toBe($tenant->getKey())
            ->and($savedDomain->domain)->toBe($normalizedDomain);
    } finally {
        if ($tenant && Tenant::withTrashed()->whereKey($tenant->getKey())->exists()) {
            Tenant::withoutEvents(fn () => $tenant->forceDelete());
        }
    }
});
