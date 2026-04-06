<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenancyUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('users')->delete();

        $super_admin = User::create([
            'name' => 'Super Admin',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'superadmin@sms.com',
            'code' => 'SUPERADMIN',
            'username' => 'superadmin',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 81',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120781,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('superadmin'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'admin@sms.com',
            'code' => 'ADMIN',
            'username' => 'admin',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('admin'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $teacher = User::create([
            'name' => 'Teacher',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'teacher@sms.com',
            'code' => 'TEACHER',
            'username' => 'teacher',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('teacher'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $accountant = User::create([
            'name' => 'Accountant',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'accountant@sms.com',
            'code' => 'ACCOUNTANT',
            'username' => 'accountant',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('accountant'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $librarian = User::create([
            'name' => 'Librarian',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'librarian@sms.com',
            'code' => 'LIBRARIAN',
            'username' => 'librarian',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('librarian'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $parent = User::create([
            'name' => 'Parent',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'parent@sms.com',
            'code' => 'PARENT',
            'username' => 'parent',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('parent'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $student = User::create([
            'name' => 'Student',
            'blocked' => 0,
            'religion' => 'Islam',
            'email' => 'student@sms.com',
            'code' => 'STUDENT',
            'username' => 'student',
            'work' => NULL,
            'dob' => '01/11/1111',
            'gender' => 'Male',
            'photo' => 'global_assets/images/user.png',
            'primary_id' => '',
            'secondary_id' => '',
            'phone' => '+255 1234 567 89',
            'phone2' => NULL,
            'bg_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nal_id' => 220,
            'address' => 'address',
            'email_verified_at' => NULL,
            'password' => Hash::make('student'),
            'remember_token' => NULL,
            'password_updated_at' => NULL,
            'twofa_secret_code' => NULL,
            'twofa_recovery_codes' => NULL,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => NULL,
        ]);

        $super_admin->assignRole(config('roles.default_roles.tenant.superadmin'));
        $admin->assignRole(config('roles.default_roles.tenant.admin'));
        $teacher->assignRole(config('roles.default_roles.tenant.teacher'));
        $accountant->assignRole(config('roles.default_roles.tenant.accountant'));
        $librarian->assignRole(config('roles.default_roles.tenant.librarian'));
        $parent->assignRole(config('roles.default_roles.tenant.parent'));
        $student->assignRole(config('roles.default_roles.tenant.student'));
    }
}