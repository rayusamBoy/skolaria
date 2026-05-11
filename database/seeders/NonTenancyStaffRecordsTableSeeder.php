<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class NonTenancyStaffRecordsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('staff_records')->delete();
        
        \DB::table('staff_records')->insert(array (
            0 => 
            array (
                'user_id' => \DB::table('users')->where('username', 'rootuser')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'role' => 'An IT person',
                'code' => 'ITGUY',
                'employment_date' => NULL,
                'employment_number' => NULL,
                'confirmation_date' => NULL,
                'licence_number' => NULL,
                'file_number' => NULL,
                'bank_account_number' => NULL,
                'bank_name' => NULL,
                'tin_number' => NULL,
                'social_security_number' => NULL,
                'education_level' => 'Degree',
                'college_attended' => 'Mzumbe University',
                'year_graduated' => '2018',
                'subjects_studied' => 'Information and Computer Studies, Basic Mathematics, Additional Mathematics',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}