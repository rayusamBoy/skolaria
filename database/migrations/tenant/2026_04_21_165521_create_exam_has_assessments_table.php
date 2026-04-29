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
        Schema::create('exam_has_assessment', function (Blueprint $table) {
            $table->unsignedInteger('exam_id');
            $table->unsignedInteger('assessment_id');

            $table->foreign('exam_id', "exam_has_assessment_ibfk_1")->references(['id'])->on('exams')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('assessment_id', "exam_has_assessment_ibfk_2")->references(['id'])->on('assessments')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_has_assessment');
    }
};
