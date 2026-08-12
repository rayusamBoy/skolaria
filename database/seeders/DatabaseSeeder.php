<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tenancy specific seeders
        $this->call(TenancyExamCategoriesTableSeeder::class);
        $this->call(TenancySkillsTableSeeder::class);
        $this->call(TenancySettingsTableSeeder::class);
        $this->call(TenancyPermissionsTableSeeder::class);
        $this->call(TenancyRolesTableSeeder::class);
        $this->call(TenancyUsersTableSeeder::class);
        $this->call(TenancyStaffRecordsTableSeeder::class);
        $this->call(TenancyClassTypesTableSeeder::class);
        $this->call(TenancyMyClassesTableSeeder::class);
        $this->call(TenancySectionsTableSeeder::class);
        $this->call(TenancyStudentRecordsTableSeeder::class);
        $this->call(ParentRelativesTableSeeder::class);
    }
}
