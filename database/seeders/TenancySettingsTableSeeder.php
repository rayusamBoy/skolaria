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

        \DB::table('settings')->insert(array(
            0 =>
                array(
                    'key' => 'current_session',
                    'value' => date("Y") - 1 . '-' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            1 =>
                array(
                    'key' => 'system_title',
                    'value' => 'TITLE',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            2 =>
                array(
                    'key' => 'system_name',
                    'value' => 'YOUR SCHOOL NAME',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            3 =>
                array(
                    'key' => 'term_ends',
                    'value' => '01/07' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            4 =>
                array(
                    'key' => 'term_begins',
                    'value' => '01/01/' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            5 =>
                array(
                    'key' => 'phone',
                    'value' => '0123456789',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            6 =>
                array(
                    'key' => 'address',
                    'value' => 'Address',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            7 =>
                array(
                    'key' => 'system_email',
                    'value' => 'example@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            8 =>
                array(
                    'key' => 'alt_email',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            9 =>
                array(
                    'key' => 'email_host',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            10 =>
                array(
                    'key' => 'email_pass',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            11 =>
                array(
                    'key' => 'lock_exam',
                    'value' => '0',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            12 =>
                array(
                    'key' => 'logo',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            13 =>
                array(
                    'key' => 'next_term_fees_j',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            14 =>
                array(
                    'key' => 'next_term_fees_pn',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            15 =>
                array(
                    'key' => 'next_term_fees_pe',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            16 =>
                array(
                    'key' => 'next_term_fees_n',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            17 =>
                array(
                    'key' => 'next_term_fees_se',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            18 =>
                array(
                    'key' => 'next_term_fees_ase',
                    'value' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            19 =>
                array(
                    'key' => 'login_and_related_pages_bg',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            20 =>
                array(
                    'key' => 'admin_email',
                    'value' => 'admin@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            21 =>
                array(
                    'key' => 'admin_whatsapp_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            22 =>
                array(
                    'key' => 'admin_x_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            23 =>
                array(
                    'key' => 'admin_facebook_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            24 =>
                array(
                    'key' => 'admin_github_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            25 =>
                array(
                    'key' => 'analytics_enabled',
                    'value' => true,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            26 =>
                array(
                    'key' => 'google_analytic_tag_id',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            27 =>
                array(
                    'key' => 'google_analytic_property_id',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            28 =>
                array(
                    'key' => 'google_analytic_service_account_credential_file',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            29 =>
                array(
                    'key' => 'login_and_related_pgs_txts_and_bg_colors',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            30 =>
                array(
                    'key' => 'enable_email_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            31 =>
                array(
                    'key' => 'enable_sms_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            32 =>
                array(
                    'key' => 'enable_push_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),

        ));
    }
}

