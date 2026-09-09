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
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'superadmin@sms.com',
            'code' => 'SUPERADMIN',
            'username' => 'superadmin',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'SUPERADMIN',
            'secondary_identification_number' => 'SUPERADMIN',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120781,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('superadmin'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'admin@sms.com',
            'code' => 'ADMIN',
            'username' => 'admin',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'ADMIN',
            'secondary_identification_number' => 'ADMIN',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('admin'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $teacher = User::create([
            'name' => 'Teacher',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'teacher@sms.com',
            'code' => 'TEACHER',
            'username' => 'teacher',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'TEACHER',
            'secondary_identification_number' => 'TEACHER',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('teacher'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $accountant = User::create([
            'name' => 'Accountant',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'accountant@sms.com',
            'code' => 'ACCOUNTANT',
            'username' => 'accountant',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'ACCOUNTANT',
            'secondary_identification_number' => 'ACCOUNTANT',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('accountant'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $librarian = User::create([
            'name' => 'Librarian',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'librarian@sms.com',
            'code' => 'LIBRARIAN',
            'username' => 'librarian',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'LIBRARIAN',
            'secondary_identification_number' => 'LIBRARIAN',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('librarian'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $parent = User::create([
            'name' => 'Parent',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'parent@sms.com',
            'code' => 'PARENT',
            'username' => 'parent',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'PARENT',
            'secondary_identification_number' => 'PARENT',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('parent'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $student = User::create([
            'name' => 'Student',
            'is_blocked' => false,
            'religion' => 'Islam',
            'email' => 'student@sms.com',
            'code' => 'STUDENT',
            'username' => 'student',
            'occupation' => null,
            'date_of_birth' => null,
            'gender' => 'Male',
            'photo_path' => 'global_assets/images/user.png',
            'primary_identification_number' => 'STUDENT',
            'secondary_identification_number' => 'STUDENT',
            'primary_mobile' => '+255123456789',
            'secondary_mobile' => null,
            'blood_group_id' => 2,
            'state_id' => 4081,
            'lga_id' => 120780,
            'nationality_id' => 220,
            'address' => 'address',
            'email_verified_at' => null,
            'password' => Hash::make('student'),
            'remember_token' => null,
            'password_updated_at' => null,
            'twofa_secret_code' => null,
            'twofa_recovery_codes' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
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
