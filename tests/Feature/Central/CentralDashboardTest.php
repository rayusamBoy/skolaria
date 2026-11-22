<?php

it('redirects guests from the central dashboard to login', function () {
    $this->get('/central/dashboard')
        ->assertRedirect('/login');
});
