<?php

use App\Models\User;

/**
 * Phase 3 — desk shell: reception uses layouts.reception for shared /admin/* desk work
 * (no AdminLTE sidebar / duplicate top-bar scan for this role).
 */
test('reception desk pages render reception shell without admin sidebar', function () {
    $reception = User::factory()->create(['role' => 'reception']);

    $response = $this->actingAs($reception)->get(route('admin.users.list'));

    $response->assertOk()
        ->assertSee(__('Front desk'), false);

    $html = $response->getContent() ?? '';
    expect($html)->not->toContain('main-sidebar')
        ->and($html)->not->toContain('data-widget="pushmenu"');
});

test('reception can open typical shift desk route sequence', function () {
    $reception = User::factory()->create(['role' => 'reception']);
    $this->actingAs($reception);

    $routes = [
        'reception.home',
        'admin.users.list',
        'admin.users.add',
        'admin.memberships.list',
        'admin.merchandise.checkout',
        'admin.merchandise.history',
        'admin.class_bookings.list',
        'admin.attendance.scan',
    ];

    foreach ($routes as $name) {
        $this->get(route($name))->assertOk();
    }
});

test('admin top bar still includes scan shortcut', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.home'))
        ->assertOk()
        ->assertSee(__('Scan ID'), false);
});
