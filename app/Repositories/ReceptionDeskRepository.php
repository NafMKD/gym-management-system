<?php

namespace App\Repositories;

use App\Models\ClassBooking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReceptionDeskRepository
{
    /**
     * Class bookings for sessions starting today (front desk view).
     *
     * @return Collection<int, ClassBooking>
     */
    public function todaysBookings(int $limit = 25): Collection
    {
        $start = Carbon::today();
        $end = Carbon::today()->endOfDay();

        return ClassBooking::query()
            ->select('class_bookings.*')
            ->join('class_schedules', 'class_bookings.class_schedule_id', '=', 'class_schedules.id')
            ->whereNull('class_schedules.deleted_at')
            ->whereBetween('class_schedules.starts_at', [$start, $end])
            ->orderBy('class_schedules.starts_at')
            ->limit($limit)
            ->with(['schedule.gymClass', 'bookedBy', 'membership.user'])
            ->get();
    }
}
