<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'phone' => '0912345678',
        'gender' => 'Male',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register without email', function () {
    $response = $this->post('/register', [
        'first_name' => 'No',
        'last_name' => 'Email',
        'email' => '',
        'phone' => '0798765432',
        'gender' => 'Female',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertDatabaseHas('users', [
        'phone' => '0798765432',
        'email' => null,
    ]);
});
