<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NonTenancyRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $default_guard = config('auth.defaults.guard');

        Role::query()->delete();

        // Load roles from config
        $rootRoles = (array) array_values(config('roles.root_roles.central', []));

        // Create all default roles
        foreach ($rootRoles as $roleName) {
            $roleName = (string) $roleName;
            $createdRole = Role::create([
                'name' => $roleName,
                'guard_name' => $default_guard,
            ]);

            $createdRole->givePermissionTo(Permission::all());
        }
    }
}
