<?php

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

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

it('does not resolve a soft-deleted tenant by domain', function () {
    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'soft_deleted_domain_'.Str::uuid().'.sqlite',
        ]);

        $domain = strtolower('deleted-'.Str::random(8).'.test');

        $tenant->domains()->create(['domain' => $domain]);

        $tenant->delete();

        $middleware = app(InitializeTenancyByDomain::class);

        expect(fn () => $middleware->initializeTenancy(
            Request::create("http://{$domain}/"),
            fn () => response('Unexpected tenant'),
            $domain
        ))->toThrow(TenantCouldNotBeIdentifiedOnDomainException::class);

        expect(tenancy()->initialized)->toBeFalse();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if ($tenant && Tenant::withTrashed()->whereKey($tenant->getKey())->exists()) {
            Tenant::withoutEvents(fn () => $tenant->forceDelete());
        }
    }
});
