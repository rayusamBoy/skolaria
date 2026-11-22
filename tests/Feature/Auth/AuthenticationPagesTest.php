<?php

use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('settings', function ($table) {
        $table->increments('id');
        $table->string('key');
        $table->string('value')->nullable();
        $table->timestamps();
    });
});

it('allows guests to view the login page', function () {
    $this->get('/login')
        ->assertOk();
});

it('allows guests to view the password reset request page', function () {
    $this->get('/password/reset')
        ->assertOk();
});
