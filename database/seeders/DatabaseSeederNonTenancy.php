<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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
        Artisan::call('permission-kit:sync', ['--context' => 'central']);
        $this->call(NonTenancyRolesTableSeeder::class);
        $this->call(NonTenancyUsersTableSeeder::class);
        $this->call(NonTenancyStaffRecordsTableSeeder::class);
    }
}
