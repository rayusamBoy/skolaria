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
        Schema::create('promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('remarks')->nullable();
            $table->unsignedInteger('previous_my_class_id')->index();
            $table->unsignedInteger('previous_section_id')->index();
            $table->unsignedInteger('new_my_class_id')->index();
            $table->unsignedInteger('new_section_id')->index();
            $table->boolean('is_graduated');
            $table->string('from_session');
            $table->string('to_session');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
