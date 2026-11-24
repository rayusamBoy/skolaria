
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

it('resolves domains to their correct tenants', function () {
    $tenantA = null;
    $tenantB = null;

    try {
        $tenantA = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_resolution_a_'.Str::uuid().'.sqlite',
        ]);

        $tenantB = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_resolution_b_'.Str::uuid().'.sqlite',
        ]);

        $domainA = strtolower('tenant-a-'.Str::random(8).'.test');
        $domainB = strtolower('tenant-b-'.Str::random(8).'.test');
        $unknownDomain = strtolower('unknown-'.Str::random(8).'.test');

        $tenantA->domains()->create(['domain' => $domainA]);
        $tenantB->domains()->create(['domain' => $domainB]);

        $middleware = app(InitializeTenancyByDomain::class);

        $resolvedTenantA = null;

        $middleware->initializeTenancy(
            Request::create("http://{$domainA}/"),
            function () use (&$resolvedTenantA) {
                $resolvedTenantA = tenancy()->tenant->getKey();

                return response('Tenant A');
            },
            $domainA
        );

        expect($resolvedTenantA)->toBe($tenantA->getKey())
            ->and(tenancy()->initialized)->toBeTrue();

        tenancy()->end();

        $resolvedTenantB = null;

        $middleware->initializeTenancy(
            Request::create("http://{$domainB}/"),
            function () use (&$resolvedTenantB) {
                $resolvedTenantB = tenancy()->tenant->getKey();

                return response('Tenant B');
            },
            $domainB
        );

        expect($resolvedTenantB)->toBe($tenantB->getKey())
            ->and(tenancy()->initialized)->toBeTrue();

        tenancy()->end();

        expect(fn () => $middleware->initializeTenancy(
            Request::create("http://{$unknownDomain}/"),
            fn () => response('Unexpected tenant'),
            $unknownDomain
        ))->toThrow(TenantCouldNotBeIdentifiedOnDomainException::class);

        expect(tenancy()->initialized)->toBeFalse();
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        foreach ([$tenantA, $tenantB] as $tenant) {
            if ($tenant && Tenant::withTrashed()->whereKey($tenant->getKey())->exists()) {
                Tenant::withoutEvents(fn () => $tenant->forceDelete());
            }
        }
    }
});
