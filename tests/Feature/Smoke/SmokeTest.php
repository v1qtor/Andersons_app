<?php

test('the root url redirects guests to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});

test('the login page loads successfully', function () {
    $this->get('/login')->assertStatus(200);
});
