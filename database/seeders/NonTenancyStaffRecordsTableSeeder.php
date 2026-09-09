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

        \DB::table('staff_records')->insert([
            0 => [
                'user_id' => \DB::table('users')->where('username', 'rootuser')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => 'An IT person',
                'code' => 'ITGUY',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'education_level' => 'Degree',
                'college_attended' => 'Mzumbe University',
                'year_graduated' => '2018',
                'subjects_studied' => 'Information and Computer Studies, Basic Mathematics, Additional Mathematics',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);

    }
}
