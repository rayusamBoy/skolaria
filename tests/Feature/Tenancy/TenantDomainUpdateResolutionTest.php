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

it('resolves the new domain after a tenant domain is changed', function () {
    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'domain_update_'.Str::uuid().'.sqlite',
        ]);

        $oldDomain = strtolower('old-'.Str::random(8).'.test');
        $newDomain = strtolower('new-'.Str::random(8).'.test');

        $domain = $tenant->domains()->create([
            'domain' => $oldDomain,
        ]);

        $domain->update([
            'domain' => $newDomain,
        ]);

        $middleware = app(InitializeTenancyByDomain::class);

        $resolvedTenant = null;

        $middleware->initializeTenancy(
            Request::create("http://{$newDomain}/"),
            function () use (&$resolvedTenant) {
                $resolvedTenant = tenancy()->tenant->getKey();

                return response('Tenant resolved');
            },
            $newDomain
        );

        expect($resolvedTenant)->toBe($tenant->getKey());

        tenancy()->end();

        expect(fn () => $middleware->initializeTenancy(
            Request::create("http://{$oldDomain}/"),
            fn () => response('Unexpected tenant'),
            $oldDomain
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
