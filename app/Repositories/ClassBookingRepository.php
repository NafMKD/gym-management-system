<?php

namespace App\Repositories;

use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClassBookingRepository extends BaseRepository
{
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $schedule = ClassSchedule::query()->with('gymClass')->findOrFail($attributes['class_schedule_id']);
            $membership = Membership::query()->findOrFail($attributes['membership_id']);

            if ($membership->status !== 'active') {
                throw new \Exception(__('Only active memberships can book a class.'));
            }

            $this->assertNoDuplicateActiveBooking($schedule->id, $membership->id);

            if ($schedule->ends_at->lte(Carbon::now())) {
                throw new \Exception(__('Cannot book a session that has already ended.'));
            }

            $capacity = $schedule->effectiveCapacity();
            $used = $schedule->activeBookingsCount();
            if ($used >= $capacity) {
                throw new \Exception(__('This session is full.'));
            }

            return ClassBooking::create([
                'class_schedule_id' => $schedule->id,
                'membership_id' => $membership->id,
                'booked_by_user_id' => $attributes['booked_by_user_id'] ?? null,
                'status' => $attributes['status'] ?? 'confirmed',
            ]);
        });
    }

    public function update(mixed $model, array $attributes): mixed
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var ClassBooking $model */
            if (isset($attributes['status'])) {
                $model->update(['status' => $attributes['status']]);
            }

            return $model->fresh();
        });
    }

    public function cancelBooking(ClassBooking $booking): ClassBooking
    {
        return DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);

            return $booking->fresh();
        });
    }

    protected function assertNoDuplicateActiveBooking(int $scheduleId, int $membershipId): void
    {
        $exists = ClassBooking::query()
            ->where('class_schedule_id', $scheduleId)
            ->where('membership_id', $membershipId)
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->exists();

        if ($exists) {
            throw new \Exception(__('This membership already has an active booking for this session.'));
        }
    }
}
