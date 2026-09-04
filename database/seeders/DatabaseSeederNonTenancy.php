<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeederNonTenancy extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Universal seeders       
        $this->call(WorldSeeder::class);
        $this->call(BloodGroupsTableSeeder::class);
        // Not tenancy specific seeders
        $this->call(NonTenancySettingsTableSeeder::class);
        $this->call(NonTenancyRolesTableSeeder::class);
        $this->call(NonTenancyPermissionsTableSeeder::class);
        $this->call(NonTenancyUsersTableSeeder::class);
        $this->call(NonTenancyStaffRecordsTableSeeder::class);
    }
}
