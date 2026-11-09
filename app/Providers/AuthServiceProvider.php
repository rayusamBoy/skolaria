<?php

namespace App\Providers;

use App\Helpers\Usr;
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
        /**
         * Tenant "Locked Master Data" Enforcement
         * ----------------------------------------
         * When the application is running in `request_data` identification mode
         * (i.e., the tenant is being resolved from the request rather than a subdomain),
         * we must enforce per‑tenant restrictions on which master‑data tables can be
         * modified. This is crucial for tenants whose master data is centrally managed
         * (e.g., a franchise or school group). Such tenants should not be able to
         * add or modify records in the locked tables — only read them.
         *
         * The `Gate::before` callback runs *before* any Spatie permission check,
         * so we can short‑circuit write abilities on locked tables.
         */
        if (config('tenancy.active_identification_mode') === 'request_data') {
            Gate::before(function ($user, $ability) {
                // Resolve the tenant currently in scope
                $tenant = tenancy()->tenant;

                // Central side (no active tenant): nothing to enforce, fall through
                if (! $tenant) {
                    return null; // let the normal Spatie permission check proceed
                }

                // List of tables this tenant has locked (cannot be modified locally)
                $lockedTables = $tenant->locked_master_tables ?? [];

                if (empty($lockedTables)) {
                    return null;
                }

                // Map each syncable master table to the permission prefix it uses
                // (e.g., 'subjects' => 'subjects', 'my_classes' => 'my_classes', …)
                $tablePermissionMap = (new SyncMasterDataService)->getSyncableMappedTables();

                foreach ($lockedTables as $table) {
                    $prefix = $tablePermissionMap[$table] ?? null;

                    // Ignore tables that have no permission prefix mapping
                    if (! $prefix) {
                        continue;
                    }

                    // If the requested ability targets this table (e.g., "subjects.create"),
                    // determine whether it is a write action.
                    if (str_starts_with($ability, "$prefix.")) {
                        $verb = substr($ability, \strlen($prefix) + 1);

                        // Safe verbs that only read data — allowed on locked tables.
                        $readVerbs = ['view', 'show', 'list', 'index', 'manage'];
                        // NOTE: 'manage' is included because the sidebar group's
                        // "index" action maps to "manage" in your current method map.

                        if (! \in_array($verb, $readVerbs)) {
                            return false; // write action on a locked table → deny
                        }
                    }
                }

                // Nothing matched; let the normal Spatie check proceed
                return null;
            });
        }

        Gate::before(function ($user, $ability) {
            if ($ability === 'deleteLogFolder' || $ability === 'deleteLogFile') {
                return false;
            }

            if ($user?->hasAnyRole(Usr::getTenancyAwareRootRoles())) {
                return true;
            }
        });

        Gate::define('viewLogViewer', fn (?User $user) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFile', fn (?User $user, LogFile $file) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
        Gate::define('downloadLogFolder', fn (?User $user, LogFolder $folder) => $user?->hasAnyRole(Usr::getTenancyAwareRootRoles()));
    }
}
