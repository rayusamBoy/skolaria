<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the tenant database.
     *
     * Production receives foundational data only. Demonstration records and
     * accounts are reserved for development and testing environments.
     */
    public function run(): void
    {
        $this->call(TenancyExamCategoriesTableSeeder::class);
        $this->call(TenancySkillsTableSeeder::class);
        $this->call(TenancySettingsTableSeeder::class);

        Artisan::call('permission-kit:sync', ['--context' => 'tenant']);

        $this->call(TenancyRolesTableSeeder::class);

        if (app()->environment('production')) {
            return;
        }

        $this->call(TenancyUsersTableSeeder::class);
        $this->call(TenancyStaffRecordsTableSeeder::class);
        $this->call(TenancyClassTypesTableSeeder::class);
        $this->call(TenancyMyClassesTableSeeder::class);
        $this->call(TenancySectionsTableSeeder::class);
        $this->call(TenancyStudentRecordsTableSeeder::class);
        $this->call(ParentRelativesTableSeeder::class);
    }
}
