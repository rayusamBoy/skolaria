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
        Schema::create('user_registration_invites', function (Blueprint $table) {
            $table->id();
            $table->string('token', 100)->unique();
            $table->json('roles');                        // preselected roles
            $table->json('prefilled_data')->nullable();   // admin‑preselected fields
            $table->json('submitted_data')->nullable();   // data submitted by recipient
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, expired, submitted
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_registration_invites');
    }
};
