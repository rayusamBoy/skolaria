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
        
        \DB::table('parent_relatives')->insert(array (
            0 => 
            array (
                'user_id' => \DB::table('users')->where('username', 'parent')->value('id'),
                'name' => 'Relative Name',
                'primary_mobile' => '+255111122233',
                'secondary_mobile' => NULL,
                'relation' => 'Brother',
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}