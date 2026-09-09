<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancyExamCategoriesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('exam_categories')->delete();

        \DB::table('exam_categories')->insert([
            0 => [
                'id' => 1,
                'name' => 'Weekly',
            ],
            1 => [
                'id' => 2,
                'name' => 'Monthly',
            ],
            2 => [
                'id' => 3,
                'name' => 'Mid-Term',
            ],
            3 => [
                'id' => 4,
                'name' => 'Terminal',
            ],
            4 => [
                'id' => 5,
                'name' => 'Annual',
            ],
            5 => [
                'id' => 6,
                'name' => 'Mock',
            ],
            6 => [
                'id' => 7,
                'name' => 'Pre-mock',
            ],
        ]);

    }
}
