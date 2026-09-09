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

        \DB::table('staff_records')->insert([
            0 => [
                'user_id' => \DB::table('users')->where('username', 'superadmin')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => 'An IT person',
                'code' => 'SUPERADMIN',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'number_of_periods' => null,
                'education_level' => 'Degree',
                'college_attended' => 'Mzumbe University',
                'year_graduated' => '2020',
                'subjects_studied' => 'Information and Computer Studies, Basic Mathematics, Additional Mathematics',
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'user_id' => \DB::table('users')->where('username', 'admin')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => null,
                'code' => 'ADMIN',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'number_of_periods' => null,
                'education_level' => 'Degree',
                'college_attended' => 'State University of Zanzibar (SUZA)',
                'year_graduated' => '2010',
                'subjects_studied' => 'Civics, History',
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'user_id' => \DB::table('users')->where('username', 'teacher')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => null,
                'code' => 'TEACHER',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'number_of_periods' => null,
                'education_level' => 'Degree',
                'college_attended' => 'State University of Zanzibar (SUZA)',
                'year_graduated' => '2010',
                'subjects_studied' => 'Civics, History',
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'user_id' => \DB::table('users')->where('username', 'accountant')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => null,
                'code' => 'ACCOUNTANT',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'number_of_periods' => null,
                'education_level' => 'Degree',
                'college_attended' => 'State University of Zanzibar (SUZA)',
                'year_graduated' => '2010',
                'subjects_studied' => 'Civics, History',
                'created_at' => null,
                'updated_at' => null,
            ],
            4 => [
                'user_id' => \DB::table('users')->where('username', 'librarian')->value('id'),
                'allow_staff_data_edit' => true,
                'place_of_living' => 'Zanzibar Town',
                'designation' => null,
                'code' => 'LIBRARIAN',
                'employment_date' => null,
                'employment_number' => null,
                'confirmation_date' => null,
                'licence_number' => null,
                'file_number' => null,
                'bank_account_number' => null,
                'bank_name' => null,
                'tin_number' => null,
                'social_security_number' => null,
                'number_of_periods' => null,
                'education_level' => 'Degree',
                'college_attended' => 'State University of Zanzibar (SUZA)',
                'year_graduated' => '2010',
                'subjects_studied' => 'Civics, History',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
