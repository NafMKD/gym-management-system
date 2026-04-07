<?php

namespace App\Repositories;

use App\Models\ClassSchedule;
use App\Models\TrainerCommissionEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TrainerDashboardRepository
{
    /**
     * Upcoming and same-day sessions for this trainer.
     *
     * @return Collection<int, ClassSchedule>
     */
    public function upcomingSessionsForTrainer(int $trainerId, int $limit = 20): Collection
    {
        $from = Carbon::now()->startOfDay();

        return ClassSchedule::query()
            ->where('trainer_id', $trainerId)
            ->where('starts_at', '>=', $from)
            ->orderBy('starts_at')
            ->limit($limit)
            ->with(['gymClass', 'bookings'])
            ->get();
    }

    /**
     * @return array{month_total: float, month_label: string}
     */
    public function commissionSnapshotForTrainer(int $trainerId): array
    {
        $start = Carbon::now()->startOfMonth();
        $total = (float) TrainerCommissionEntry::query()
            ->where('trainer_id', $trainerId)
            ->where('earned_at', '>=', $start->toDateString())
            ->sum('amount');

        return [
            'month_total' => $total,
            'month_label' => $start->translatedFormat('F Y'),
        ];
    }
}
