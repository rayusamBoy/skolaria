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
        Schema::table('divisions', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('remarks');
            $table->boolean('is_active')->default(true)->after('is_locked');

            $table->index(['class_type_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropIndex(['class_type_id', 'is_active']);
            $table->dropColumn(['is_locked', 'is_active']);
        });
    }
};
