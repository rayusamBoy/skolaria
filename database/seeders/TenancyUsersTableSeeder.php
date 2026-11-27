<?php

namespace Database\\Seeders;

use App\\User;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\Hash;

class TenancyUsersTableSeeder extends Seeder
{
    /**
     * Create predictable demonstration accounts in non-production environments.
     *
     * Production tenants receive their first real administrator through the
     * school-owner invitation flow, never through a shared default password.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $users = [
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@sms.com',
                'code' => 'SUPERADMIN',
                'password' => 'superadmin',
                'role' => config('roles.default_roles.tenant.superadmin'),
                'lga_id' => 120781,
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@sms.com',
                'code' => 'ADMIN',
                'password' => 'admin',
                'role' => config('roles.default_roles.tenant.admin'),
                'lga_id' => 120780,
            ],
            [
                'name' => 'Teacher',
                'username' => 'teacher',
                'email' => 'teacher@sms.com',
                'code' => 'TEACHER',
                'password' => 'teacher',
                'role' => config('roles.default_roles.tenant.teacher'),
                'lga_id' => 120780,
            ],
            [
                'name' => 'Accountant',
                'username' => 'accountant',
                'email' => 'accountant@sms.com',
                'code' => 'ACCOUNTANT',
                'password' => 'accountant',
                'role' => config('roles.default_roles.tenant.accountant'),
                'lga_id' => 120780,
            ],
            [
                'name' => 'Librarian',
                'username' => 'librarian',
                'email' => 'librarian@sms.com',
                'code' => 'LIBRARIAN',
                'password' => 'librarian',
                'role' => config('roles.default_roles.tenant.librarian'),
                'lga_id' => 120780,
            ],
            [
                'name' => 'Parent',
                'username' => 'parent',
                'email' => 'parent@sms.com',
                'code' => 'PARENT',
                'password' => 'parent',
                'role' => config('roles.default_roles.tenant.parent'),
                'lga_id' => 120780,
            ],
            [
                'name' => 'Student',
                'username' => 'student',
                'email' => 'student@sms.com',
                'code' => 'STUDENT',
                'password' => 'student',
                'role' => config('roles.default_roles.tenant.student'),
                'lga_id' => 120780,
            ],
        ];

        foreach ($users as $attributes) {
            $role = $attributes['role'];
            $password = $attributes['password'];

            unset($attributes['role'], $attributes['password']);

            $user = User::firstOrCreate(
                ['username' => $attributes['username']],
                [
                    ...$attributes,
                    'is_blocked' => false,
                    'religion' => 'Islam',
                    'occupation' => null,
                    'date_of_birth' => null,
                    'gender' => 'Male',
                    'photo_path' => 'global_assets/images/user.png',
                    'primary_identification_number' => $attributes['code'],
                    'secondary_identification_number' => $attributes['code'],
                    'primary_mobile' => '+255123456789',
                    'secondary_mobile' => null,
                    'blood_group_id' => 2,
                    'state_id' => 4081,
                    'nationality_id' => 220,
                    'address' => 'address',
                    'email_verified_at' => null,
                    'password' => Hash::make($password),
                    'remember_token' => null,
                    'password_updated_at' => null,
                    'twofa_secret_code' => null,
                    'twofa_recovery_codes' => null,
                ]
            );

            $user->assignRole($role);
        }
    }
}
