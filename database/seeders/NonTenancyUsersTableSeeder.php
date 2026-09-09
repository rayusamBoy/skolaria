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
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'rootuser@sms.com',
            'code' => 'ROOTUSER',
            'username' => 'rootuser',
            'occupation' => 'NULL',
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => null,
            'secondary_identification_number' => null,
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120781,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('root'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'message_media_heading_color' => '#818b6c',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $root_user->assignRole(config('roles.root_roles.central.root'));
    }
}
