<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NonTenancyPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $centralPerms = config('permissions.names.central');

        if (empty($centralPerms)) {
            $this->command->error("Permissions not found in config('permissions.names.central')");
            return;
        }

        if (!\is_array($centralPerms)) {
            $this->command->error("Permissions file must return an array. Found: " . \gettype($centralPerms));
            return;
        }

        // For each top-level key inside central (e.g., 'users', 'roles'), collect permission strings
        $groups = [];
        foreach ($centralPerms as $groupName => $groupNode) {
            $perms = $this->collectPermissionValues($groupNode);
            $perms = array_values(array_unique($perms));
            if (!empty($perms)) {
                $groups[$groupName] = $perms;
            }
        }

        if (empty($groups)) {
            $this->command->info('No central permissions found to seed.');
            return;
        }

        $this->command->info('Seeding central permission groups: ' . implode(', ', array_keys($groups)));

        $guard = config('auth.defaults.guard', 'web');
        $table = config('permission.table_names.permissions', 'permissions');
        $now = now();

        foreach ($groups as $groupName => $perms) {
            sort($perms);
            $this->command->info(\sprintf(" - group '%s' (%d permissions)", $groupName, \count($perms)));
            foreach ($perms as $name) {
                if (!\is_string($name) || strpos($name, '.') === false) {
                    continue;
                }
                DB::table($table)->updateOrInsert(
                    ['name' => $name, 'guard_name' => $guard],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Central permissions seeded.');
    }

    /**
     * Same collector as tenant seeder.
     *
     * @param mixed $node
     * @return string[]
     */
    protected function collectPermissionValues($node)
    {
        $out = [];

        if (\is_array($node)) {
            foreach ($node as $k => $v) {
                if (\is_string($v) && strpos($v, '.') !== false) {
                    $out[] = trim($v);
                    continue;
                }
                if (\is_string($k) && strpos($k, '.') !== false && ($v === true || $v === 1 || $v === '1')) {
                    $out[] = trim($k);
                    continue;
                }
                if (\is_array($v)) {
                    $out = array_merge($out, $this->collectPermissionValues($v));
                }
            }
        } elseif (\is_string($node) && strpos($node, '.') !== false) {
            $out[] = trim($node);
        }

        return $out;
    }
}
