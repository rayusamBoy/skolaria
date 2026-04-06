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

        // For each top-level key inside tenant (e.g., 'users', 'roles'), collect nodes array
        $groups = [];
        foreach ($centralPerms as $groupName => $groupNodes) {
            $perms = [];
            foreach ($groupNodes as $groupNode) {
                $perms[] = $this->collectPermissionValues($groupNode);
            }
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
            foreach ($perms as $perm) {
                if (!\is_array($perm)) {
                    continue;
                }
                DB::table($table)->updateOrInsert(
                    ['name' => $perm['name'], 'guard_name' => $guard],
                    ['label' => $perm['label'], 'description' => $perm['description'], 'created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info('Central permissions seeded.');
    }

    /**
     * Recursively collect permission strings from the permission configuration.
     *
     * Supports both the old structure (string permission names or boolean flags)
     * and the new structure where each permission is an array with a 'permission' key.
     *
     * @param mixed $node
     * @return string[]
     */
    protected function collectPermissionValues($node): array
    {
        $out = [];

        if (\is_array($node)) {
            // Check if this array represents a permission leaf (has 'permission', 'label', and 'description' keys)
            if (isset($node['permission']) && isset($node['label']) && isset($node['description']) && \is_string($node['permission']) && \is_string($node['label']) && \is_string($node['description'])) {
                $out['name'] = trim($node['permission']);
                $out['label'] = trim($node['label']);
                $out['description'] = trim($node['description']);
                return $out;
            }

            // Otherwise, recursively process each element
            foreach ($node as $value) {
                if (\is_array($value)) {
                    $out = [...$out, ...$this->collectPermissionValues($value)];
                } elseif (\is_string($value) && strpos($value, '.') !== false) {
                    $out[] = trim($value);
                }
            }
        } elseif (\is_string($node) && strpos($node, '.') !== false) {
            $out[] = trim($node);
        }

        return $out;
    }
}
