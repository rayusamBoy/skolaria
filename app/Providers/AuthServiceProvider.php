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
            // Hard denies – even root cannot bypass these.
            if ($ability === 'deleteLogFolder' || $ability === 'deleteLogFile') {
                return false;
            }

            // Disabled feature modules.
            if (is_string($ability) && str_contains($ability, '.')) {
                if (! app(FeatureModuleService::class)->isPermissionAllowed($ability)) {
                    return false;
                }
            }

            return null;
        });

        /*
         * Spatie registers its own Gate::before callback to grant assigned
         * permissions. An earlier callback can return true before another
         * Gate::before callback gets a chance to enforce table locks.
         *
         * Gate::after runs after the permission result has been calculated,
         * allowing locked master-table restrictions to remain authoritative.
         */
        Gate::after(function ($user, $ability, $result, $arguments) {
            if (! is_string($ability) || $result === false) {
                return null;
            }

            // Root users retain their full access.
            if ($user?->hasAnyRole(Usr::getTenancyAwareRootRoles())) {
                return null;
            }

            if (config('tenancy.active_identification_mode') !== 'request_data') {
                return null;
            }

            $tenant = tenancy()->tenant;

            if (! $tenant) {
                return null;
            }

            $lockedTables = $tenant->locked_master_tables ?? [];

            if (empty($lockedTables)) {
                return null;
            }

            $tablePermissionMap = (new SyncMasterDataService)->getSyncableMappedTables();
            $readVerbs = ['view', 'show', 'list', 'index', 'manage'];

            foreach ($lockedTables as $table) {
                $prefix = $tablePermissionMap[$table] ?? null;

                if (! $prefix || ! str_starts_with($ability, "$prefix.")) {
                    continue;
                }

                $verb = substr($ability, strlen($prefix) + 1);

                if (! in_array($verb, $readVerbs, true)) {
                    return false;
                }
            }

            return null;
        });

        Gate::define('viewLogViewer', fn (?User $user) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFile', fn (?User $user, LogFile $file) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFolder', fn (?User $user, LogFolder $folder) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
    }
}
