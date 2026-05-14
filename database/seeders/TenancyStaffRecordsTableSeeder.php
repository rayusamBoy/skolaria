<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancyStaffRecordsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('staff_records')->delete();

        \DB::table('staff_records')->insert(array(
            0 =>
                array(
                    'user_id' => \DB::table('users')->where('username', 'superadmin')->value('id'),
                    'allow_staff_data_edit' => true,
                    'place_of_living' => 'Zanzibar Town',
                    'designation' => 'An IT person',
                    'code' => 'SUPERADMIN',
                    'employment_date' => NULL,
                    'employment_number' => NULL,
                    'confirmation_date' => NULL,
                    'licence_number' => NULL,
                    'file_number' => NULL,
                    'bank_account_number' => NULL,
                    'bank_name' => NULL,
                    'tin_number' => NULL,
                    'social_security_number' => NULL,
                    'number_of_periods' => NULL,
                    'education_level' => 'Degree',
                    'college_attended' => 'Mzumbe University',
                    'year_graduated' => '2020',
                    'subjects_studied' => 'Information and Computer Studies, Basic Mathematics, Additional Mathematics',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            1 =>
                array(
                    'user_id' => \DB::table('users')->where('username', 'admin')->value('id'),
                    'allow_staff_data_edit' => true,
                    'place_of_living' => 'Zanzibar Town',
                    'designation' => NULL,
                    'code' => 'ADMIN',
                    'employment_date' => NULL,
                    'employment_number' => NULL,
                    'confirmation_date' => NULL,
                    'licence_number' => NULL,
                    'file_number' => NULL,
                    'bank_account_number' => NULL,
                    'bank_name' => NULL,
                    'tin_number' => NULL,
                    'social_security_number' => NULL,
                    'number_of_periods' => NULL,
                    'education_level' => 'Degree',
                    'college_attended' => 'State University of Zanzibar (SUZA)',
                    'year_graduated' => '2010',
                    'subjects_studied' => 'Civics, History',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            2 =>
                array(
                    'user_id' => \DB::table('users')->where('username', 'teacher')->value('id'),
                    'allow_staff_data_edit' => true,
                    'place_of_living' => 'Zanzibar Town',
                    'designation' => NULL,
                    'code' => 'TEACHER',
                    'employment_date' => NULL,
                    'employment_number' => NULL,
                    'confirmation_date' => NULL,
                    'licence_number' => NULL,
                    'file_number' => NULL,
                    'bank_account_number' => NULL,
                    'bank_name' => NULL,
                    'tin_number' => NULL,
                    'social_security_number' => NULL,
                    'number_of_periods' => NULL,
                    'education_level' => 'Degree',
                    'college_attended' => 'State University of Zanzibar (SUZA)',
                    'year_graduated' => '2010',
                    'subjects_studied' => 'Civics, History',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            3 =>
                array(
                    'user_id' => \DB::table('users')->where('username', 'accountant')->value('id'),
                    'allow_staff_data_edit' => true,
                    'place_of_living' => 'Zanzibar Town',
                    'designation' => NULL,
                    'code' => 'ACCOUNTANT',
                    'employment_date' => NULL,
                    'employment_number' => NULL,
                    'confirmation_date' => NULL,
                    'licence_number' => NULL,
                    'file_number' => NULL,
                    'bank_account_number' => NULL,
                    'bank_name' => NULL,
                    'tin_number' => NULL,
                    'social_security_number' => NULL,
                    'number_of_periods' => NULL,
                    'education_level' => 'Degree',
                    'college_attended' => 'State University of Zanzibar (SUZA)',
                    'year_graduated' => '2010',
                    'subjects_studied' => 'Civics, History',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            4 =>
                array(
                    'user_id' => \DB::table('users')->where('username', 'librarian')->value('id'),
                    'allow_staff_data_edit' => true,
                    'place_of_living' => 'Zanzibar Town',
                    'designation' => NULL,
                    'code' => 'LIBRARIAN',
                    'employment_date' => NULL,
                    'employment_number' => NULL,
                    'confirmation_date' => NULL,
                    'licence_number' => NULL,
                    'file_number' => NULL,
                    'bank_account_number' => NULL,
                    'bank_name' => NULL,
                    'tin_number' => NULL,
                    'social_security_number' => NULL,
                    'number_of_periods' => NULL,
                    'education_level' => 'Degree',
                    'college_attended' => 'State University of Zanzibar (SUZA)',
                    'year_graduated' => '2010',
                    'subjects_studied' => 'Civics, History',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
        ));
    }
}