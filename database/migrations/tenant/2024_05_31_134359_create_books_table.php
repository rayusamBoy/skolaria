<?php

use App\Helpers\Usr;
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
        Schema::create('books', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->unsignedInteger('my_class_id')->nullable()->index();
            $table->string('description')->nullable();
            $table->string('author')->nullable();
            $table->string('book_type')->nullable();
            $table->string('url')->nullable();
            $table->string('location')->nullable();
            $table->integer('total_copies')->nullable();
            $table->integer('issued_copies')->default(0);
            $table->string('status', 50)->default(array_key_first(Usr::getBookStatuses())); // In Library
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
