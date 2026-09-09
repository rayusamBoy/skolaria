<?php

namespace App\Providers;

use App\Helpers\Usr;
use App\Services\SyncMasterDataService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (config('tenancy.active_identification_mode') === 'request_data') {
            Gate::before(function ($user, $ability) {
                $tenant = tenancy()->tenant;
                if (! $tenant) {
                    return null; // central side – no lock enforcement
                }

                $lockedTables = $tenant->locked_master_tables ?? [];
                if (empty($lockedTables)) {
                    return null;
                }

                $tablePermissionMap = (new SyncMasterDataService)->getSyncableMappedTables();

                foreach ($lockedTables as $table) {
                    $prefix = $tablePermissionMap[$table] ?? null;
                    if (! $prefix) {
                        continue;
                    }

                    // If ability starts with "{$prefix}.", check if it’s a write action
                    if (str_starts_with($ability, "$prefix.")) {
                        $verb = substr($ability, \strlen($prefix) + 1);
                        // Safe verbs that only read data
                        $readVerbs = ['view', 'show', 'list', 'index']; // adjust to your mapping
                        if (! \in_array($verb, $readVerbs)) {
                            return false; // deny
                        }
                    }
                }

                return null; // let normal Spatie check proceed
            });
        }

        Gate::before(static fn ($user, string $ability) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()) ? true : null);
    }
}
