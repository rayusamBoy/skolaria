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
        Schema::create('staff_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->boolean('allow_staff_data_edit')->default(true);
            $table->string('place_of_living', 100)->nullable();
            $table->string('designation')->nullable();
            $table->string('code', 100)->nullable()->unique();
            $table->date('employment_date')->nullable();
            $table->integer('employment_number')->nullable()->unique('employment_number');
            $table->date('confirmation_date')->nullable();
            $table->string('licence_number', 150)->nullable()->unique('licence_no');
            $table->string('file_number')->nullable();
            $table->string('bank_account_number', 100)->nullable()->unique('bank_account_number');
            $table->string('bank_name')->nullable();
            $table->string('tin_number', 150)->nullable()->unique('tin_no');
            $table->string('social_security_number', 190)->nullable()->unique('social_security_number');
            $table->string('education_level')->nullable();
            $table->string('college_attended')->nullable();
            $table->string('year_graduated')->nullable();
            $table->text('subjects_studied')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_records');
    }
};
