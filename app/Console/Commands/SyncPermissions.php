<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync
                            {--central : Sync only the central database}
                            {--all-tenants : Sync all tenants}
                            {--tenant=* : Sync specific tenant(s) by ID}
                            {--guard= : The guard to use (defaults to default guard)}
                            {--delete-existing : Delete all existing permissions before inserting}
                            {--update-existing : Update label and description of existing permissions when they differ}';

    protected $description = 'Synchronise permissions from the config into the database for central and/or tenant contexts';

    protected array $permissionActionPriority = [
        'edit' => 100,
        'index' => 100,
        'store' => 100,
        'destroy' => 100,
        'update' => 50,
        'create' => 50,
        'delete' => 50,
        'view' => 50,
    ];

    protected array $processedPermissions = [];

    public function handle(): int
    {
        $runCentral = $this->option('central') || ! $this->option('all-tenants') && empty($this->option('tenant'));
        $runAllTenants = $this->option('all-tenants');
        $specificTenants = $this->option('tenant');
        $deleteExisting = $this->option('delete-existing');
        $updateExisting = $this->option('update-existing');
        $guard = $this->option('guard') ?? config('auth.defaults.guard', 'web');

        // ---------- Central ----------
        if ($runCentral) {
            $this->info('Syncing central permissions...');
            $this->syncContext('permissions.names.central', 'central', $guard, $deleteExisting, $updateExisting);
        }

        // ---------- Tenants ----------
        if ($runAllTenants || ! empty($specificTenants)) {
            $tenants = $runAllTenants ? Tenant::all() : Tenant::whereIn('id', $specificTenants)->get();

            if ($tenants->isEmpty()) {
                $this->warn('No matching tenants found.');

                return 1;
            }

            foreach ($tenants as $tenant) {
                $this->info("Syncing tenant: {$tenant->id}");

                try {
                    tenancy()->initialize($tenant);
                    $this->syncContext('permissions.names.tenant', 'tenant', $guard, $deleteExisting, $updateExisting);
                } catch (\Throwable $e) {
                    $this->error("Failed to sync tenant {$tenant->id}: ".$e->getMessage());
                } finally {
                    tenancy()->end();
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('All done.');

        return 0;
    }

    /**
     * Sync permissions for a single context (central or a specific tenant).
     */
    protected function syncContext(string $configKey, string $label, string $guard, bool $deleteExisting, bool $updateExisting): void
    {
        $permissionsConfig = config($configKey, []);

        if (empty($permissionsConfig)) {
            $this->warn("No permissions found in config('{$configKey}').");

            return;
        }

        $table = config('permission.table_names.permissions', 'permissions');
        $now = now();

        if ($deleteExisting) {
            DB::table($table)->delete();
            $this->line("  Deleted existing {$label} permissions.");
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        $this->processGroup($permissionsConfig, $guard, $table, $now, $inserted, $updated, $skipped, $updateExisting);

        $this->info("  {$label} context: Inserted: {$inserted}, Updated: {$updated}, Skipped: {$skipped}");
    }

    /**
     * Recursively process a group of permissions (handles nested arrays).
     */
    protected function processGroup(
        array $group,
        string $guard,
        string $table,
        string $now,
        int &$inserted,
        int &$updated,
        int &$skipped,
        bool $updateExisting
    ): void {
        foreach ($group as $action => $item) {

            if (
                isset($item['permission'], $item['label'], $item['description'])
            ) {
                $permission = $item['permission'];

                $priority = $this->permissionActionPriority[$action] ?? 0;

                if (isset($this->processedPermissions[$permission])) {
                    $existing = $this->processedPermissions[$permission];

                    if ($priority <= $existing['priority']) {
                        $this->warn(
                            "    Ignoring duplicate permission {$permission} from action {$action}"
                        );

                        continue;
                    }

                    $this->warn(
                        "    Replacing permission {$permission}: {$existing['action']} → {$action}"
                    );
                }

                $this->processedPermissions[$permission] = [
                    'action' => $action,
                    'priority' => $priority,
                ];

                $this->upsertPermission(
                    $item,
                    $guard,
                    $table,
                    $now,
                    $inserted,
                    $updated,
                    $skipped,
                    $updateExisting
                );

            } elseif (\is_array($item)) {
                $this->processGroup(
                    $item,
                    $guard,
                    $table,
                    $now,
                    $inserted,
                    $updated,
                    $skipped,
                    $updateExisting
                );
            }
        }
    }

    /**
     * Insert a new permission or optionally update label/description of an existing one.
     */
    protected function upsertPermission(
        array $perm,
        string $guard,
        string $table,
        string $now,
        int &$inserted,
        int &$updated,
        int &$skipped,
        bool $updateExisting
    ): void {
        $name = $perm['permission'] ?? null;
        if (! $name) {
            return;
        }

        $label = $perm['label'] ?? $name;
        $description = $perm['description'] ?? '';

        $existing = DB::table($table)
            ->where('name', $name)
            ->where('guard_name', $guard)
            ->first();

        if (! $existing) {
            // Insert new
            DB::table($table)->insert([
                'name' => $name,
                'guard_name' => $guard,
                'label' => $label,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $inserted++;
            $this->line("    + {$name}");

            return;
        }

        // Permission already exists
        if ($updateExisting) {
            $newData = [];
            if ($existing->label != $label) {
                $newData['label'] = $label;
            }

            if ($existing->description != $description) {
                $newData['description'] = $description;
            }

            if (! empty($newData)) {
                DB::table($table)->where('id', $existing->id)->update($newData);
                $updated++;
                $this->line("    ~ {$name} (label/description updated)");

                return;
            }
        }

        $skipped++;
    }
}
