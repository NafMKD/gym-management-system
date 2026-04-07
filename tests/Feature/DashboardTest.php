<?php

use App\Models\User;

test('admin dashboard shows KPIs for authenticated admin', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.home'));

    $response->assertOk();
    $response->assertSee(__('Dashboard'), false);
    $response->assertSee(__('Active memberships'), false);
});

test('admin can download memberships csv', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.export.memberships_csv'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
});
