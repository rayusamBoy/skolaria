<?php

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
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

it('resolves multiple domains to the same tenant', function () {
    $tenant = null;

    try {
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'tenancy_db_name' => 'multiple_domains_'.Str::uuid().'.sqlite',
        ]);

        $domains = [
            strtolower('school-'.Str::random(8).'.test'),
            strtolower('school-alias-'.Str::random(8).'.test'),
        ];

        foreach ($domains as $domain) {
            $tenant->domains()->create(['domain' => $domain]);
        }

        $middleware = app(InitializeTenancyByDomain::class);

        foreach ($domains as $domain) {
            $resolvedTenant = null;

            $middleware->initializeTenancy(
                Request::create("http://{$domain}/"),
                function () use (&$resolvedTenant) {
                    $resolvedTenant = tenancy()->tenant->getKey();

                    return response('Tenant resolved');
                },
                $domain
            );

            expect($resolvedTenant)->toBe($tenant->getKey())
                ->and(tenancy()->initialized)->toBeTrue();

            tenancy()->end();
        }
    } finally {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if ($tenant && Tenant::withTrashed()->whereKey($tenant->getKey())->exists()) {
            Tenant::withoutEvents(fn () => $tenant->forceDelete());
        }
    }
});
