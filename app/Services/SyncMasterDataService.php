<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class SyncMasterDataService
{
    // Maps each table to its corresponding permission prefix.
    // Tables are ordered by dependency to satisfy foreign key constraints.
    protected array $tables = [
        'exam_categories' => 'exam_categories',
        'class_types' => 'class_types',
        'my_classes' => 'my_classes',
        'sections' => 'sections',
        'grades' => 'grades',
        'divisions' => 'divisions',
        'subjects' => 'subjects',
        'subject_records' => 'subject_records',
        'exams' => 'exams',
        'time_table_records' => 'time_table_records',
        'time_slots' => 'time_slots',
        'skills' => 'skills',
    ];

    public function getSyncableTables(): array
    {
        return array_keys($this->tables);
    }

    public function getSyncableMappedTables(): array
    {
        return $this->tables;
    }

    public function sync(
        Tenant $sourceTenant,
        array $targetTenantIds,
        array $tablesToSync,
        bool $lockAfterSync = false,
        array $excludeLockTenantIds = []
    ): void {
        // Fetch all data from source tenant
        tenancy()->initialize($sourceTenant);
        $tableData = [];
        foreach ($tablesToSync as $table) {
            $tableData[$table] = DB::table($table)->get()->map(fn($row) => (array) $row)->toArray();
        }
        tenancy()->end();

        // Push data to each target tenant
        $targetTenants = Tenant::whereIn('id', $targetTenantIds)->get();
        $orderedTables = $this->orderTables($tablesToSync);

        foreach ($targetTenants as $targetTenant) {
            tenancy()->initialize($targetTenant);
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($orderedTables as $table) {
                DB::table($table)->truncate();
                if (!empty($tableData[$table])) {
                    DB::table($table)->insert($tableData[$table]);
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            tenancy()->end();
        }

        // Optionally lock tables on target tenants
        if ($lockAfterSync) {
            foreach ($targetTenants as $targetTenant) {
                if (\in_array($targetTenant->id, $excludeLockTenantIds)) {
                    $targetTenant->update(['locked_master_tables' => []]); // Unlock if previously locked
                    continue;
                }

                $existingLocked = $targetTenant->locked_master_tables ?? [];

                $targetTenant->update([
                    'locked_master_tables' => array_unique([
                        ...$existingLocked,
                        ...$tablesToSync,
                    ]),
                ]);
            }
        } else {
            foreach ($targetTenants as $targetTenant) {
                $targetTenant->update(['locked_master_tables' => []]);
            }
        }
    }

    protected function orderTables(array $tables): array
    {
        return array_values(array_intersect($this->tables, $tables));
    }
}
