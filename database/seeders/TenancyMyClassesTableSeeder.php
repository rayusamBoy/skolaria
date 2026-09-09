<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancyMyClassesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('my_classes')->delete();

        \DB::table('my_classes')->insert([
            0 => [
                'name' => 'Form One',
                'class_type_id' => \DB::table('class_types')->where('code', 'SE')->value('id'),
                'created_at' => null,
                'updated_at' => null,
                'deleted_at' => null,
            ],
        ]);
    }
}
