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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreign('category_id', 'tickets_category_id_foreign')->references(['id'])->on('ticket_categories')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('assigned_to', 'tickets_assigned_to_foreign')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('SET NULL');
            $table->foreign('tenant_id', 'tickets_tenant_id_foregin')->references(['id'])->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign('tickets_category_id_foreign');
            $table->dropForeign('tickets_assigned_to_foreign');
            $table->dropForeign('tickets_tenant_id_foregin');
        });
    }
};
