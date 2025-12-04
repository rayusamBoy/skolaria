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

        \DB::table('roles')->delete();

        $root_role = Role::create(['name' => 'Root', 'guard_name' => $default_guard]);
        $root_role->givePermissionTo(Permission::all());
    }
}
