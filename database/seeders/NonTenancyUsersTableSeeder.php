<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NonTenancyUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        $root_user = User::create([
            'name' => 'Root User',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'rootuser@sms.com',
            'code' => 'ROOTUSER',
            'username' => 'rootuser',
            'work' => 'NULL',
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => NULL,
            'secondary_id' => NULL,
            'phone' => '+255 1234 567 81',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120781,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('root'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'message_media_heading_color' => '#818b6c',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $root_user->assignRole(config('roles.root_roles.central.root'));
    }
}