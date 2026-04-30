<?php

use App\Models\Attendance;
use App\Models\Membership;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;

test('user detail page shows history tabs and membership filter options', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $package = Package::create([
        'name' => 'Monthly',
        'price' => 800.00,
        'duration' => 30,
        'granted_days' => 30,
        'description' => 'Monthly membership',
    ]);

    $membership = Membership::create([
        'user_id' => $member->id,
        'package_id' => $package->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
        'remaining_days' => 5,
        'status' => 'active',
        'price' => 800.00,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.view', $member));

    $response->assertOk();
    $response->assertSee('Membership History');
    $response->assertSee('Attendance History');
    $response->assertSee('#'.$membership->id.' - '.$package->name, false);
});

test('user membership history endpoint returns latest memberships first', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $package = Package::create([
        'name' => 'Quarterly',
        'price' => 1500.00,
        'duration' => 90,
        'granted_days' => 90,
        'description' => 'Quarterly membership',
    ]);

    Membership::create([
        'user_id' => $member->id,
        'package_id' => $package->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-03-31',
        'remaining_days' => 0,
        'status' => 'inactive',
        'price' => 1500.00,
    ]);

    $latestMembership = Membership::create([
        'user_id' => $member->id,
        'created_by_user_id' => $admin->id,
        'package_id' => $package->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-06-30',
        'remaining_days' => 30,
        'status' => 'active',
        'price' => 1500.00,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.membership_history.data', [
        'user' => $member,
        'draw' => 1,
        'start' => 0,
        'length' => 10,
    ]));

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.start_date', '2026-04-01')
        ->assertJsonPath('data.0.created_by', $admin->getName());

    expect($response->json('data.0.id'))->toContain('#'.$latestMembership->id);
});

test('user attendance history endpoint filters by membership', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();
    $package = Package::create([
        'name' => 'Bi-Monthly',
        'price' => 1200.00,
        'duration' => 60,
        'granted_days' => 60,
        'description' => 'Bi-monthly membership',
    ]);

    $firstMembership = Membership::create([
        'user_id' => $member->id,
        'package_id' => $package->id,
        'start_date' => '2026-02-01',
        'end_date' => '2026-03-31',
        'remaining_days' => 10,
        'status' => 'active',
        'price' => 1200.00,
    ]);

    $secondMembership = Membership::create([
        'user_id' => $member->id,
        'package_id' => $package->id,
        'start_date' => '2026-04-01',
        'end_date' => '2026-05-31',
        'remaining_days' => 20,
        'status' => 'inactive',
        'price' => 1200.00,
    ]);

    Attendance::create([
        'membership_id' => $firstMembership->id,
        'entry_date' => Carbon::create(2026, 2, 10, 6, 0, 0, 'UTC'),
    ]);

    Attendance::create([
        'membership_id' => $secondMembership->id,
        'entry_date' => Carbon::create(2026, 4, 10, 6, 0, 0, 'UTC'),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.attendance_history.data', [
        'user' => $member,
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'membership_id' => $firstMembership->id,
    ]));

    $response->assertOk()
        ->assertJsonCount(1, 'data');

    expect($response->json('data.0.membership_id'))->toContain('#'.$firstMembership->id);
    expect($response->json('data.0.record_status'))->toContain('Recorded');
});
