<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancyClassTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('class_types')->delete();
        
        \DB::table('class_types')->insert(array (
            0 => 
            array (
                'name' => 'Primary',
                'code' => 'PE',
                'minimum_subjects_required' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ),
            1 => 
            array (
                'name' => 'Secondary',
                'code' => 'SE',
                'minimum_subjects_required' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ),
            2 => 
            array (
                'name' => 'Tertiary (Advance)',
                'code' => 'ASE',
                'minimum_subjects_required' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ),
        ));
    }
}