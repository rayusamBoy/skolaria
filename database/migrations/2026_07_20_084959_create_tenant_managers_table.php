<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_managers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('central_user_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('tenant_user_id')->nullable(); // local user ID inside tenant
            $table->timestamps();

            $table->unique(['central_user_id', 'tenant_id']);
            $table->foreign('central_user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_managers');
    }
};
