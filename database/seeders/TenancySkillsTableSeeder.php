<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancySkillsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('skills')->delete();

        \DB::table('skills')->insert([
            0 => [
                'id' => 1,
                'name' => 'PUNCTUALITY',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'id' => 2,
                'name' => 'NEATNESS',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'id' => 3,
                'name' => 'HONESTY',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'id' => 4,
                'name' => 'RELIABILITY',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            4 => [
                'id' => 5,
                'name' => 'RELATIONSHIP WITH OTHERS',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            5 => [
                'id' => 6,
                'name' => 'POLITENESS',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            6 => [
                'id' => 7,
                'name' => 'ALERTNESS',
                'skill_type' => 'AF',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            7 => [
                'id' => 8,
                'name' => 'HANDWRITING',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            8 => [
                'id' => 9,
                'name' => 'GAMES & SPORTS',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            9 => [
                'id' => 10,
                'name' => 'DRAWING & ARTS',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            10 => [
                'id' => 11,
                'name' => 'PAINTING',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            11 => [
                'id' => 12,
                'name' => 'CONSTRUCTION',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            12 => [
                'id' => 13,
                'name' => 'MUSICAL SKILLS',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            13 => [
                'id' => 14,
                'name' => 'FLEXIBILITY',
                'skill_type' => 'PS',
                'class_type_id' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);

    }
}
