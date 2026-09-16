<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenancySettingsTableSeeder extends Seeder
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
                'key' => 'current_session',
                'value' => date('Y').'-'.date('Y') + 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            1 => [
                'key' => 'system_title',
                'value' => 'TITLE',
                'created_at' => null,
                'updated_at' => null,
            ],
            2 => [
                'key' => 'system_name',
                'value' => 'YOUR SCHOOL NAME',
                'created_at' => null,
                'updated_at' => null,
            ],
            3 => [
                'key' => 'term_ends',
                'value' => date('Y').'-07-01',
                'created_at' => null,
                'updated_at' => null,
            ],
            4 => [
                'key' => 'term_begins',
                'value' => date('Y').'-01-01',
                'created_at' => null,
                'updated_at' => null,
            ],
            5 => [
                'key' => 'phone',
                'value' => '0123456789',
                'created_at' => null,
                'updated_at' => null,
            ],
            6 => [
                'key' => 'address',
                'value' => 'Address',
                'created_at' => null,
                'updated_at' => null,
            ],
            7 => [
                'key' => 'system_email',
                'value' => 'example@example.domain',
                'created_at' => null,
                'updated_at' => null,
            ],
            8 => [
                'key' => 'alt_email',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            9 => [
                'key' => 'email_host',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            10 => [
                'key' => 'email_pass',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            11 => [
                'key' => 'lock_exam',
                'value' => '0',
                'created_at' => null,
                'updated_at' => null,
            ],
            12 => [
                'key' => 'logo',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            13 => [
                'key' => 'next_term_fees_j',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            14 => [
                'key' => 'next_term_fees_pn',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            15 => [
                'key' => 'next_term_fees_pe',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            16 => [
                'key' => 'next_term_fees_n',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            17 => [
                'key' => 'next_term_fees_se',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            18 => [
                'key' => 'next_term_fees_ase',
                'value' => '0000',
                'created_at' => null,
                'updated_at' => null,
            ],
            19 => [
                'key' => 'login_and_related_pages_bg',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            20 => [
                'key' => 'admin_email',
                'value' => 'admin@example.domain',
                'created_at' => null,
                'updated_at' => null,
            ],
            21 => [
                'key' => 'admin_whatsapp_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            22 => [
                'key' => 'admin_x_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            23 => [
                'key' => 'admin_facebook_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            24 => [
                'key' => 'admin_github_link',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            25 => [
                'key' => 'analytics_enabled',
                'value' => true,
                'created_at' => null,
                'updated_at' => null,
            ],
            26 => [
                'key' => 'google_analytic_tag_id',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            27 => [
                'key' => 'google_analytic_property_id',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            28 => [
                'key' => 'google_analytic_service_account_credential_file',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            29 => [
                'key' => 'login_and_related_pgs_txts_and_bg_colors',
                'value' => null,
                'created_at' => null,
                'updated_at' => null,
            ],
            30 => [
                'key' => 'enable_email_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            31 => [
                'key' => 'enable_sms_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],
            32 => [
                'key' => 'enable_push_notification',
                'value' => 1,
                'created_at' => null,
                'updated_at' => null,
            ],

        ]);
    }
}
