<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class NonTenancyRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $default_guard = config('auth.defaults.guard');

        // Load roles from config
        $defaultRoles = (array) array_values(config('roles.default_roles.central', []));

        // Clean existing roles
        Role::query()->delete();

        // Create roles
        foreach ($defaultRoles as $roleName) {
            $roleName = (string) $roleName;
            Role::create([
                'name' => $roleName,
                'guard_name' => $default_guard,
            ]);
        }
    }
}
