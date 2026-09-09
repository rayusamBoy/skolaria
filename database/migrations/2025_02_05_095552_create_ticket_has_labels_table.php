<?php

namespace Coderflex\LaravelTicket\Database\Factories;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_has_labels', function (Blueprint $table) {
            $table->foreignId('ticket_id')->references('id')->on('tickets')->onDelete('cascade')->onUpdate('restrict');
            $table->foreignId('label_id')->references('id')->on('ticket_labels')->onDelete('cascade')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_has_labels');
    }
};
