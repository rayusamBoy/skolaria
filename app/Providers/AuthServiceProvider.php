<?php

namespace App\Providers;

use App\Helpers\Usr;
use App\Services\FeatureModuleService;
use App\Services\SyncMasterDataService;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\LogFile;
use Opcodes\LogViewer\LogFolder;

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
        Gate::before(function ($user, $ability) {
            // 1. Hard denies – even root cannot bypass these
            if ($ability === 'deleteLogFolder' || $ability === 'deleteLogFile') {
                return false;
            }

            // 2. Disabled feature modules
            if (\is_string($ability) && str_contains($ability, '.')) {
                if (! app(FeatureModuleService::class)->isPermissionAllowed($ability)) {
                    return false;
                }
            }

            // 3. Root users bypass everything else
            if ($user?->hasAnyRole(Usr::getTenancyAwareRootRoles())) {
                return true;
            }

            // 4. Locked master tables (only in request_data identification mode)
            if (config('tenancy.active_identification_mode') === 'request_data') {
                $tenant = tenancy()->tenant;

                if ($tenant) {
                    $lockedTables = $tenant->locked_master_tables ?? [];

                    if (! empty($lockedTables)) {
                        $tablePermissionMap = (new SyncMasterDataService)->getSyncableMappedTables();
                        $readVerbs = ['view', 'show', 'list', 'index', 'manage'];

                        foreach ($lockedTables as $table) {
                            $prefix = $tablePermissionMap[$table] ?? null;
                            if (! $prefix) {
                                continue;
                            }

                            if (str_starts_with($ability, "$prefix.")) {
                                $verb = substr($ability, \strlen($prefix) + 1);
                                if (! \in_array($verb, $readVerbs, true)) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }

            // 5. Fall through to Spatie permission check
            return null;
        });

        Gate::define('viewLogViewer', fn (?User $user) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFile', fn (?User $user, LogFile $file) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFolder', fn (?User $user, LogFolder $folder) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
    }
}
