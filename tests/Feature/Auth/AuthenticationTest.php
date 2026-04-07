<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'phone' => $user->phone,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'phone' => $user->phone,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('login rejects invalid phone format', function () {
    User::factory()->create(['phone' => '0911111111']);

    $response = $this->from('/login')->post('/login', [
        'phone' => '0812345678',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('phone');
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
