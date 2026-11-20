<?php

declare(strict_types=1);

namespace PermissionKit\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use PermissionKit\Support\PermissionDefinition;
use PermissionKit\Support\SyncReport;
use Spatie\Permission\PermissionRegistrar;

class PermissionSyncService
{
    public function __construct(
        protected PermissionRegistrar $registrar,
        protected string $guard_name = 'web',
    ) {}

    protected function supportsMetadata(): bool
    {
        // Removed the class properties so it re-checks on every database switch.
        // Schema::hasColumn is fast enough for a sync command.
        return Schema::hasColumn('permissions', 'label')
            && Schema::hasColumn('permissions', 'description');
    }

    protected function syncOne(PermissionDefinition $def, SyncReport $report, bool $dry_run, string $mode): void
    {
        $permission_class = $this->registrar->getPermissionClass();

        $existing = $permission_class::query()
            ->where('name', $def->name)
            ->where('guard_name', $this->guard_name)
            ->first();

        $with_meta = $this->supportsMetadata();

        if ($existing === null) {
            $report->created[] = $def->name;

            if (! $dry_run) {
                $attributes = [
                    'name' => $def->name,
                    'guard_name' => $this->guard_name,
                ];

                if ($with_meta) {
                    $attributes['label'] = $def->label;
                    $attributes['description'] = $def->description;
                }

                // Use query()->create() to bypass Spatie's cached create() method
                // which throws "PermissionAlreadyExists" if the cache is dirty.
                $permission_class::query()->create($attributes);
            }

            return;
        }

        if ($mode === 'preserve') {
            $report->unchanged[] = $def->name;

            return;
        }

        $needs_update = $mode === 'overwrite';

        if (! $needs_update && $with_meta) {
            $needs_update = $existing->label !== $def->label
                || $existing->description !== $def->description;
        }

        if ($needs_update) {
            $report->updated[] = $def->name;

            if (! $dry_run) {
                $update = [];

                if ($with_meta) {
                    $update['label'] = $def->label;
                    $update['description'] = $def->description;
                }

                if (! empty($update)) {
                    $existing->update($update);
                }
            }
        } else {
            $report->unchanged[] = $def->name;
        }
    }

    /**
     * @param  Collection<int, PermissionDefinition>|iterable  $definitions
     */
    public function sync(iterable $definitions, bool $dry_run = false, ?string $mode = null, bool $prune = false): SyncReport
    {
        $mode ??= (string) config('permission-kit.sync_mode', 'update');
        $report = new SyncReport;
        $seen_names = [];

        // Clear the cache BEFORE we start to ensure we are working with fresh DB state.
        // This prevents the "PermissionAlreadyExists" exception during Tenant creation.
        $this->registrar->forgetCachedPermissions();

        foreach ($definitions as $definition) {
            $seen_names[] = $definition->name;
            $this->syncOne($definition, $report, $dry_run, $mode);
        }

        if ($prune && config('permission-kit.allow_prune', false) && ! $dry_run) {
            $this->prune($seen_names, $report);
        }

        if (! $dry_run) {
            // Clear again at the end so the application boots with the new permissions
            $this->registrar->forgetCachedPermissions();
        }

        return $report;
    }

    /**
     * @param  array<int, string>  $keep_names
     */
    protected function prune(array $keep_names, SyncReport $report): void
    {
        $permission_class = $this->registrar->getPermissionClass();

        $orphans = $permission_class::query()
            ->where('guard_name', $this->guard_name)
            ->whereNotIn('name', $keep_names)
            ->get();

        foreach ($orphans as $orphan) {
            $report->orphaned[] = $orphan->name;

            // Do not delete permissions still attached to a role/user.
            if ($orphan->roles()->exists() || $orphan->users()->exists()) {
                continue;
            }

            $report->pruned[] = $orphan->name;
            $orphan->delete();
        }
    }
}
