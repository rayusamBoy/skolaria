<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->boolean('is_blocked')->default(false);
            $table->string('religion', 13);
            $table->string('email', 100)->nullable()->unique();
            $table->string('code', 100)->unique();
            $table->string('username', 100)->nullable()->unique();
            $table->string('occupation')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('photo_path')->default('global_assets/images/user.png');
            $table->string('primary_identification_number')->nullable()->unique();
            $table->string('secondary_identification_number')->nullable()->unique();
            $table->string('primary_mobile', 16)->nullable();
            $table->string('secondary_mobile', 16)->nullable();
            $table->unsignedInteger('blood_group_id')->nullable()->index();
            $table->unsignedBigInteger('state_id')->nullable()->index();
            $table->unsignedBigInteger('lga_id')->nullable()->index();
            $table->unsignedBigInteger('nationality_id')->nullable()->index();
            $table->string('address')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->json('notification_preferences')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamp('password_updated_at')->nullable();
            $table->string('twofa_secret_code', 100)->nullable();
            $table->json('twofa_recovery_codes')->nullable();
            $table->string('message_media_heading_color', 10)->default('inherit');
            $table->json('firebase_device_tokens')->nullable();
            $table->json('hidden_alert_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
