<?php

use App\Models\GymClass;
use App\Models\User;
use App\Repositories\ClassScheduleRepository;
use Carbon\Carbon;

test('trainer cannot have two overlapping sessions', function () {
    $repo = app(ClassScheduleRepository::class);

    $trainer = User::factory()->create(['role' => 'trainer']);
    $gc = GymClass::create([
        'name' => 'Yoga',
        'capacity' => 10,
        'duration_minutes' => 60,
        'is_active' => true,
    ]);

    $start = Carbon::parse('2026-06-15 10:00:00');
    $end = Carbon::parse('2026-06-15 11:00:00');

    $repo->store([
        'gym_class_id' => $gc->id,
        'trainer_id' => $trainer->id,
        'starts_at' => $start->toDateTimeString(),
        'ends_at' => $end->toDateTimeString(),
    ]);

    expect(fn () => $repo->store([
        'gym_class_id' => $gc->id,
        'trainer_id' => $trainer->id,
        'starts_at' => $start->copy()->addMinutes(30)->toDateTimeString(),
        'ends_at' => $end->copy()->addHour()->toDateTimeString(),
    ]))->toThrow(\Exception::class);
});

test('trainer can have adjacent non-overlapping sessions', function () {
    $repo = app(ClassScheduleRepository::class);

    $trainer = User::factory()->create(['role' => 'trainer']);
    $gc = GymClass::create([
        'name' => 'Spin',
        'capacity' => 8,
        'duration_minutes' => 45,
        'is_active' => true,
    ]);

    $repo->store([
        'gym_class_id' => $gc->id,
        'trainer_id' => $trainer->id,
        'starts_at' => '2026-06-15 10:00:00',
        'ends_at' => '2026-06-15 11:00:00',
    ]);

    $second = $repo->store([
        'gym_class_id' => $gc->id,
        'trainer_id' => $trainer->id,
        'starts_at' => '2026-06-15 11:00:00',
        'ends_at' => '2026-06-15 12:00:00',
    ]);

    expect($second->id)->toBeGreaterThan(0);
});
