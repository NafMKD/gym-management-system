<?php

use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Membership;
use App\Models\TrainerCommissionEntry;
use App\Models\TrainerProfile;
use App\Models\User;
use Carbon\Carbon;

test('marking booking attended creates session commission when trainer profile rate is set', function () {
    $admin = User::factory()->admin()->create();
    $trainer = User::factory()->create(['role' => 'trainer']);
    TrainerProfile::create([
        'user_id' => $trainer->id,
        'commission_per_session' => 40,
    ]);

    $member = User::factory()->create(['role' => 'member']);
    $membership = Membership::create([
        'user_id' => $member->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'remaining_days' => 30,
        'status' => 'active',
        'price' => 100.00,
    ]);

    $gc = GymClass::create([
        'name' => 'PT',
        'capacity' => 5,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

    $start = Carbon::parse('2026-07-01 10:00:00');
    $end = Carbon::parse('2026-07-01 11:00:00');

    $schedule = ClassSchedule::create([
        'gym_class_id' => $gc->id,
        'trainer_id' => $trainer->id,
        'starts_at' => $start,
        'ends_at' => $end,
    ]);

    $booking = ClassBooking::create([
        'class_schedule_id' => $schedule->id,
        'membership_id' => $membership->id,
        'status' => 'confirmed',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.class_bookings.mark_attended', $booking))
        ->assertRedirect();

    $this->assertDatabaseHas('trainer_commission_entries', [
        'trainer_id' => $trainer->id,
        'class_booking_id' => $booking->id,
        'source' => TrainerCommissionEntry::SOURCE_SESSION,
        'amount' => 40,
    ]);
});

test('manual commission entry is stored', function () {
    $admin = User::factory()->admin()->create();
    $trainer = User::factory()->create(['role' => 'trainer']);

    $this->actingAs($admin)
        ->post(route('admin.trainer_commissions.store'), [
            'trainer_id' => $trainer->id,
            'amount' => 100.50,
            'earned_at' => '2026-03-15',
            'notes' => 'Bonus',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('trainer_commission_entries', [
        'trainer_id' => $trainer->id,
        'source' => TrainerCommissionEntry::SOURCE_MANUAL,
        'amount' => 100.50,
    ]);
});
