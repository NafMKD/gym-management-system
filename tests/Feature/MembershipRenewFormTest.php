<?php

use App\Models\Membership;
use App\Models\Package;
use App\Models\User;

test('renew membership form includes selected package duration metadata for prefilled renewals', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $package = Package::create([
        'name' => 'Quarterly',
        'price' => 1500.00,
        'duration' => 90,
        'granted_days' => 90,
        'description' => 'Quarterly membership',
    ]);

    $membership = Membership::create([
        'user_id' => $member->id,
        'package_id' => $package->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-03-31',
        'remaining_days' => 0,
        'status' => 'inactive',
        'price' => 1500.00,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.memberships.add', [
        'renew_from' => $membership->id,
    ]));

    $response->assertOk();
    $response->assertSee($member->getName());
    $response->assertSee('data-duration="'.$package->duration.'"', false);
    $response->assertSee($package->name);
});
