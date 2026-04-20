<?php

use App\Models\Membership;
use App\Models\User;
use App\Repositories\MembershipExtensionRequestRepository;
use Carbon\Carbon;

test('extension request approval extends membership dates in one place', function () {
    Carbon::setTestNow(null);

    $this->travelTo(Carbon::parse('2025-06-15'));

    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'remaining_days' => 4,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $this->actingAs($admin);

    $repo = app(MembershipExtensionRequestRepository::class);
    $request = $repo->store([
        'membership_id' => $membership->id,
        'requested_by' => $admin->id,
        'reason' => 'Medical travel',
        'requested_days' => 3,
    ]);

    $repo->approve($request, $admin->id);

    $membership->refresh();
    expect($membership->remaining_days)->toBe(7);
    expect($request->fresh()->status)->toBe('approved');

    $this->travelBack();
});

test('extension requests can be created and approved for active memberships past end date', function () {
    Carbon::setTestNow(null);

    $this->travelTo(Carbon::parse('2025-06-15'));

    $admin = User::factory()->admin()->create();
    $member = User::factory()->create(['role' => 'member']);

    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-06-10',
        'remaining_days' => 2,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $this->actingAs($admin);

    $repo = app(MembershipExtensionRequestRepository::class);
    $request = $repo->store([
        'membership_id' => $membership->id,
        'requested_by' => $admin->id,
        'reason' => 'Travel delay',
        'requested_days' => 3,
    ]);

    $repo->approve($request, $admin->id);

    $membership->refresh();
    expect($membership->end_date)->toBe('2025-06-13');
    expect($membership->remaining_days)->toBe(5);
    expect($request->fresh()->status)->toBe('approved');

    $this->travelBack();
});
