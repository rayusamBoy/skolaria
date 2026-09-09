<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ParentRelativesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('parent_relatives')->delete();

        \DB::table('parent_relatives')->insert([
            0 => [
                'user_id' => \DB::table('users')->where('username', 'parent')->value('id'),
                'name' => 'Relative Name',
                'primary_mobile' => '+255111122233',
                'secondary_mobile' => null,
                'relation' => 'Brother',
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);

    }
}
