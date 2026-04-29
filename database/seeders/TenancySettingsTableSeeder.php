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
                    'type' => 'current_session',
                    'description' => date("Y") - 1 . '-' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            1 =>
                array(
                    'type' => 'system_title',
                    'description' => 'TITLE',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            2 =>
                array(
                    'type' => 'system_name',
                    'description' => 'YOUR SCHOOL NAME',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            3 =>
                array(
                    'type' => 'term_ends',
                    'description' => '01/07' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            4 =>
                array(
                    'type' => 'term_begins',
                    'description' => '01/01/' . date('Y'),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            5 =>
                array(
                    'type' => 'phone',
                    'description' => '0123456789',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            6 =>
                array(
                    'type' => 'address',
                    'description' => 'Address',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            7 =>
                array(
                    'type' => 'system_email',
                    'description' => 'example@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            8 =>
                array(
                    'type' => 'alt_email',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            9 =>
                array(
                    'type' => 'email_host',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            10 =>
                array(
                    'type' => 'email_pass',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            11 =>
                array(
                    'type' => 'lock_exam',
                    'description' => '0',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            12 =>
                array(
                    'type' => 'logo',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            13 =>
                array(
                    'type' => 'next_term_fees_j',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            14 =>
                array(
                    'type' => 'next_term_fees_pn',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            15 =>
                array(
                    'type' => 'next_term_fees_pe',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            16 =>
                array(
                    'type' => 'next_term_fees_n',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            17 =>
                array(
                    'type' => 'next_term_fees_se',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            18 =>
                array(
                    'type' => 'next_term_fees_ase',
                    'description' => '0000',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            19 =>
                array(
                    'type' => 'login_and_related_pages_bg',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            20 =>
                array(
                    'type' => 'admin_email',
                    'description' => 'admin@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            21 =>
                array(
                    'type' => 'admin_whatsapp_link',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            22 =>
                array(
                    'type' => 'admin_x_link',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            23 =>
                array(
                    'type' => 'admin_facebook_link',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            24 =>
                array(
                    'type' => 'admin_github_link',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            25 =>
                array(
                    'type' => 'analytics_enabled',
                    'description' => true,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            26 =>
                array(
                    'type' => 'google_analytic_tag_id',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            27 =>
                array(
                    'type' => 'google_analytic_property_id',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            28 =>
                array(
                    'type' => 'google_analytic_service_account_credential_file',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            29 =>
                array(
                    'type' => 'login_and_related_pgs_txts_and_bg_colors',
                    'description' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            30 =>
                array(
                    'type' => 'enable_email_notification',
                    'description' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            31 =>
                array(
                    'type' => 'enable_sms_notification',
                    'description' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            32 =>
                array(
                    'type' => 'enable_push_notification',
                    'description' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),

        ));
    }
}

