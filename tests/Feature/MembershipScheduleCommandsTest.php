<?php

use App\Mail\MembershipExpiringSoonMail;
use App\Models\Membership;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

test('memberships:process-expiry sets inactive when calendar end date has passed', function () {
    Carbon::setTestNow(Carbon::parse('2025-06-15'));

    $user = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $user->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-06-10',
        'remaining_days' => 5,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $this->artisan('memberships:process-expiry')->assertSuccessful();

    expect($membership->fresh()->status)->toBe('inactive');

    Carbon::setTestNow();
});

test('memberships:process-expiry sets inactive when remaining_days is zero', function () {
    Carbon::setTestNow(Carbon::parse('2025-06-10'));

    $user = User::factory()->create();
    $membership = Membership::create([
        'user_id' => $user->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-12-31',
        'remaining_days' => 0,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $this->artisan('memberships:process-expiry')->assertSuccessful();

    expect($membership->fresh()->status)->toBe('inactive');

    Carbon::setTestNow();
});

test('memberships:notify-expiring sends mail for memberships ending in configured days', function () {
    Mail::fake();

    Carbon::setTestNow(Carbon::parse('2025-06-01'));

    $user = User::factory()->create(['email' => 'member@example.com']);
    Membership::create([
        'user_id' => $user->id,
        'start_date' => '2025-01-01',
        'end_date' => '2025-06-08',
        'remaining_days' => 10,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $this->artisan('memberships:notify-expiring')->assertSuccessful();

    Mail::assertSent(MembershipExpiringSoonMail::class, function (MembershipExpiringSoonMail $mail) use ($user) {
        return $mail->hasTo($user->email);
    });

    Carbon::setTestNow();
});
