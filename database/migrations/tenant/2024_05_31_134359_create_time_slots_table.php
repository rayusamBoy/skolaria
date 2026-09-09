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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ttr_id')->index();
            $table->tinyInteger('hour_from');
            $table->tinyInteger('min_from');
            $table->enum('meridian_from', ['AM', 'PM']);
            $table->tinyInteger('hour_to');
            $table->string('min_to', 2);
            $table->enum('meridian_to', ['AM', 'PM']);
            $table->string('time_from', 100);
            $table->string('time_to', 100);
            $table->time('timestamp_from');
            $table->time('timestamp_to');
            $table->string('full', 100);
            $table->timestamps();

            $table->unique(['timestamp_from', 'timestamp_to', 'ttr_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
