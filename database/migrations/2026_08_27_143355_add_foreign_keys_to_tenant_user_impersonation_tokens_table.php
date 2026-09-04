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
        Schema::table('tenant_user_impersonation_tokens', function (Blueprint $table) {
            $table->foreign('tenant_id', 'tenant_user_impersonation_tokens_tenant_id_foreign')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_user_impersonation_tokens', function (Blueprint $table) {
            $table->dropForeign('tenant_user_impersonation_tokens_tenant_id_foreign');
        });
    }
};
