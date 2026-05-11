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

        \DB::table('settings')->insert(array(
            0 =>
                array(
                    'id' => 1,
                    'key' => 'phone',
                    'value' => '0123456789',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            1 =>
                array(
                    'id' => 2,
                    'key' => 'system_name',
                    'value' => 'SCHOOL MANANGEMENT SYSTEM',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            2 =>
                array(
                    'id' => 3,
                    'key' => 'address',
                    'value' => 'Address',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            3 =>
                array(
                    'id' => 4,
                    'key' => 'system_email',
                    'value' => 'info@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            4 =>
                array(
                    'id' => 5,
                    'key' => 'alt_email',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            5 =>
                array(
                    'id' => 6,
                    'key' => 'email_host',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            6 =>
                array(
                    'id' => 7,
                    'key' => 'email_pass',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            7 =>
                array(
                    'id' => 8,
                    'key' => 'logo',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            8 =>
                array(
                    'id' => 9,
                    'key' => 'login_and_related_pages_bg',
                    'value' => Usr::getLoginAndRelatedPagesBgValue(),
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            9 =>
                array(
                    'id' => 10,
                    'key' => 'admin_email',
                    'value' => 'admin@example.domain',
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            10 =>
                array(
                    'id' => 11,
                    'key' => 'admin_whatsapp_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            11 =>
                array(
                    'id' => 12,
                    'key' => 'admin_x_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            12 =>
                array(
                    'id' => 13,
                    'key' => 'admin_facebook_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            13 =>
                array(
                    'id' => 14,
                    'key' => 'admin_github_link',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            14 =>
                array(
                    'id' => 15,
                    'key' => 'analytics_enabled',
                    'value' => true,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            15 =>
                array(
                    'id' => 16,
                    'key' => 'google_analytic_tag_id',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            16 =>
                array(
                    'id' => 17,
                    'key' => 'google_analytic_property_id',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            17 =>
                array(
                    'id' => 18,
                    'key' => 'google_analytic_service_account_credential_file',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            18 =>
                array(
                    'id' => 19,
                    'key' => 'login_and_related_pgs_txts_and_bg_colors',
                    'value' => NULL,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            19 =>
                array(
                    'id' => 20,
                    'key' => 'enable_email_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            21 =>
                array(
                    'id' => 21,
                    'key' => 'enable_sms_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
            22 =>
                array(
                    'id' => 22,
                    'key' => 'enable_push_notification',
                    'value' => 1,
                    'created_at' => NULL,
                    'updated_at' => NULL,
                ),
        ));
    }
}
