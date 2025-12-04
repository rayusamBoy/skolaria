<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenancyRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $default_guard = config('auth.defaults.guard');

        \DB::table('roles')->delete();

        $super_admin_role = Role::create(['name' => 'Super Admin', 'guard_name' => $default_guard]);
        $admin_role = Role::create(['name' => 'Admin', 'guard_name' => $default_guard]);
        $teacher_role = Role::create(['name' => 'Teacher', 'guard_name' => $default_guard]);
        $parent_role = Role::create(['name' => 'Parent', 'guard_name' => $default_guard]);
        $student_role = Role::create(['name' => 'Student', 'guard_name' => $default_guard]);

        $super_admin_role->givePermissionTo(Permission::all());
    }
}
