<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancyStudentRecordsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('student_records')->delete();
        
        \DB::table('student_records')->insert(array (
            0 => 
            array (
                'user_id' => \DB::table('users')->where('username', 'student')->value('id'),
                'primary_school_name' => 'school',
                'secondary_school_name' => '',
                'birth_certificate' => NULL,
                'disability' => NULL,
                'food_taboos' => NULL,
                'chronic_health_problems' => NULL,
                'parent_status' => 'Not Orphan',
                'house_number' => '',
                'my_class_id' => 1,
                'section_id' => 1,
                'admission_number' => 'STUDENT',
                'my_parent_id' => 5,
                'dorm_id' => NULL,
                'dorm_room_no' => NULL,
                'session' => date("Y") - 1 . '-' . date('Y'),
                'age' => NULL,
                'date_admitted' => now(),
                'is_graduated' => false,
                'graduated_session' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        )); 
    }
}