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
        Schema::create('grades', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 40);
            $table->unsignedInteger('class_type_id')->nullable()->index();
            $table->tinyInteger('mark_from');
            $table->tinyInteger('mark_to');
            $table->integer('point');
            $table->string('remarks', 40)->nullable();
            $table->timestamps();

            $table->unique(['name', 'class_type_id', 'remarks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
