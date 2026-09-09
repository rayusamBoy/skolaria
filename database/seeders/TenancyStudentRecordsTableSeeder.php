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

        \DB::table('student_records')->insert([
            0 => [
                'user_id' => \DB::table('users')->where('username', 'student')->value('id'),
                'primary_school_name' => 'school',
                'secondary_school_name' => '',
                'birth_certificate' => null,
                'disability' => null,
                'food_taboos' => null,
                'chronic_health_problems' => null,
                'parent_status' => 'Not Orphan',
                'house_number' => '',
                'my_class_id' => 1,
                'section_id' => 1,
                'admission_number' => 'STUDENT',
                'my_parent_id' => 5,
                'dorm_id' => null,
                'dorm_room_no' => null,
                'session' => date('Y') - 1 .'-'.date('Y'),
                'age' => null,
                'date_admitted' => now(),
                'is_graduated' => false,
                'graduated_session' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);
    }
}
