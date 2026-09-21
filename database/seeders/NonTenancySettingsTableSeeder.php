<?php

namespace Database\Seeders;

use App\Helpers\Usr;
use Illuminate\Database\Seeder;

class NonTenancySettingsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('settings')->delete();

        \DB::table('settings')->insert([
            0 => [
                'id' => 1,
                'key' => 'phone',
                'value' => '0123456789',
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'id' => 2,
                'key' => 'system_name',
                'value' => 'SCHOOL MANANGEMENT SYSTEM',
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'id' => 3,
                'key' => 'address',
                'value' => 'Address',
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'id' => 4,
                'key' => 'system_email',
                'value' => 'info@example.domain',
                'created_at' => null,
                'updated_at' => null,
            ],
            4 => [
                'id' => 5,
                'key' => 'alt_email',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            5 => [
                'id' => 6,
                'key' => 'email_host',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            6 => [
                'id' => 7,
                'key' => 'email_pass',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            7 => [
                'id' => 8,
                'key' => 'logo',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            8 => [
                'id' => 9,
                'key' => 'login_and_related_pages_bg',
                'value' => Usr::getLoginAndRelatedPagesBgValue(),
                'created_at' => null,
                'updated_at' => null,
            ],
            9 => [
                'id' => 10,
                'key' => 'admin_email',
                'value' => 'admin@example.domain',
                'created_at' => null,
                'updated_at' => null,
            ],
            10 => [
                'id' => 11,
                'key' => 'admin_whatsapp_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            11 => [
                'id' => 12,
                'key' => 'admin_x_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            12 => [
                'id' => 13,
                'key' => 'admin_facebook_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            13 => [
                'id' => 14,
                'key' => 'admin_github_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            14 => [
                'id' => 15,
                'key' => 'analytics_enabled',
                'value' => true,
                'created_at' => null,
                'updated_at' => null,
            ],
            15 => [
                'id' => 16,
                'key' => 'google_analytic_tag_id',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            16 => [
                'id' => 17,
                'key' => 'google_analytic_property_id',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            17 => [
                'id' => 18,
                'key' => 'google_analytic_service_account_credential_file',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            18 => [
                'id' => 19,
                'key' => 'login_and_related_pgs_txts_and_bg_colors',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            19 => [
                'id' => 20,
                'key' => 'enable_email_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            21 => [
                'id' => 21,
                'key' => 'enable_sms_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            22 => [
                'id' => 22,
                'key' => 'enable_push_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            23 => [
                'id' => 23,
                'key' => 'password_min_length',
                'value' => 8,
                'created_at' => null,
                'updated' => null,
            ],
            24 => [
                'id' => 24,
                'key' => 'password_require_confirmation',
                'value' => 1,
                'created_at' => null,
                'updated' => null,
            ],
            25 => [
                'id' => 25,
                'key' => 'password_require_mixed_case',
                'value' => 1,
                'created_at' => null,
                'updated' => null,
            ],
            26 => [
                'id' => 26,
                'key' => 'password_require_letters',
                'value' => 1,
                'created_at' => null,
                'updated' => null,
            ],
            27 => [
                'id' => 27,
                'key' => 'password_require_numbers',
                'value' => 1,
                'created_at' => null,
                'updated' => null,
            ],
            28 => [
                'id' => 28,
                'key' => 'password_require_symbols',
                'value' => 1,
                'created_at' => null,
                'updated' => null,
            ],
            29 => [
                'id' => 29,
                'key' => 'password_require_uncompromised',
                'value' => 0,
                'created_at' => null,
                'updated' => null,
            ],
            30 => [
                'id' => 30,
                'key' => 'password_history_count',
                'value' => 3,
                'created_at' => null,
                'updated' => null,
            ],
        ]);
    }
}
