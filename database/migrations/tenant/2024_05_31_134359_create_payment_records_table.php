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
        Schema::create('payment_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('payment_id')->index();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('reference_number', 100)->nullable()->unique();
            $table->integer('amount_paid')->nullable();
            $table->integer('balance')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->string('year');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
