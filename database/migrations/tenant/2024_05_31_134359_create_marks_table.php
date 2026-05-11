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
        Schema::create('marks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id')->index();
            $table->unsignedInteger('subject_id')->index();
            $table->unsignedInteger('my_class_id')->index();
            $table->unsignedInteger('section_id')->index();
            $table->unsignedInteger('exam_id')->index();
            $table->integer('exam_mark')->nullable();
            $table->integer('tex1')->nullable()->comment('term 1 exam marks');
            $table->integer('tex2')->nullable()->comment('term 2 exam marks');
            $table->integer('tex3')->nullable()->comment('term 3 exam marks');
            $table->unsignedTinyInteger('subject_position')->nullable();
            $table->integer('cumulative')->nullable();
            $table->integer('cumulative_average')->nullable();
            $table->unsignedInteger('grade_id')->nullable()->index('marks_grade_id_foreign');
            $table->string('year');
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'my_class_id', 'section_id', 'exam_id'], 'student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marks');
    }
};
