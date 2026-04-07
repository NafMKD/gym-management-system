<?php

namespace App\Repositories;

use App\Models\ClassBooking;
use App\Models\TrainerCommissionEntry;
use App\Models\TrainerProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainerCommissionRepository
{
    /**
     * Create a session-linked row when a member attended and the trainer has a default rate &gt; 0.
     */
    public function recordForAttendedBooking(ClassBooking $booking): void
    {
        DB::transaction(function () use ($booking) {
            if ($booking->status !== 'attended') {
                return;
            }

            $exists = TrainerCommissionEntry::query()
                ->where('class_booking_id', $booking->id)
                ->where('source', TrainerCommissionEntry::SOURCE_SESSION)
                ->exists();

            if ($exists) {
                return;
            }

            $booking->loadMissing('schedule.trainer');
            $schedule = $booking->schedule;
            if (! $schedule || ! $schedule->trainer_id) {
                return;
            }

            $profile = TrainerProfile::query()->where('user_id', $schedule->trainer_id)->first();
            if (! $profile || (float) $profile->commission_per_session <= 0) {
                return;
            }

            $earnedDate = $schedule->ends_at ?? $schedule->starts_at ?? Carbon::now();

            TrainerCommissionEntry::create([
                'trainer_id' => $schedule->trainer_id,
                'source' => TrainerCommissionEntry::SOURCE_SESSION,
                'class_booking_id' => $booking->id,
                'amount' => $profile->commission_per_session,
                'earned_at' => $earnedDate instanceof Carbon ? $earnedDate->toDateString() : Carbon::parse($earnedDate)->toDateString(),
                'notes' => null,
                'recorded_by_user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Remove session commission when attendance is reversed (e.g. status changed from attended).
     */
    public function removeSessionCommissionForBooking(ClassBooking $booking): void
    {
        TrainerCommissionEntry::withTrashed()
            ->where('class_booking_id', $booking->id)
            ->where('source', TrainerCommissionEntry::SOURCE_SESSION)
            ->get()
            ->each->forceDelete();
    }

    /**
     * Manual adjustment / bonus entry (not tied to a booking).
     *
     * @param array<string, mixed> $attributes
     */
    public function storeManual(array $attributes): TrainerCommissionEntry
    {
        return DB::transaction(function () use ($attributes) {
            $row = TrainerCommissionEntry::create([
                'trainer_id' => (int) $attributes['trainer_id'],
                'source' => TrainerCommissionEntry::SOURCE_MANUAL,
                'class_booking_id' => null,
                'amount' => (float) $attributes['amount'],
                'earned_at' => $attributes['earned_at'] ?? now()->toDateString(),
                'notes' => $attributes['notes'] ?? null,
                'recorded_by_user_id' => Auth::id(),
            ]);

            return $row;
        });
    }

    /**
     * @param array{trainer_id?:int,start_date?:string,end_date?:string} $filters
     */
    public function getFilteredQuery(array $filters): Builder
    {
        return TrainerCommissionEntry::query()
            ->with(['trainer', 'classBooking.schedule.gymClass', 'recordedBy'])
            ->when(! empty($filters['trainer_id']), fn (Builder $q) => $q->where('trainer_id', $filters['trainer_id']))
            ->when(
                ! empty($filters['start_date']) && ! empty($filters['end_date']),
                fn (Builder $q) => $q->whereBetween('earned_at', [$filters['start_date'], $filters['end_date']])
            );
    }
}
