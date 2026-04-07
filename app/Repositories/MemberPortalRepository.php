<?php

namespace App\Repositories;

use App\Models\ClassBooking;
use App\Models\Membership;
use Illuminate\Support\Collection;

class MemberPortalRepository
{
    public function activeMembershipForUser(int $userId): ?Membership
    {
        return Membership::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->orderByDesc('end_date')
            ->with(['package:id,name'])
            ->first();
    }

    /**
     * Recent class bookings for memberships owned by this user (read-only history).
     *
     * @return Collection<int, ClassBooking>
     */
    public function recentClassBookingsForUser(int $userId, int $limit = 10): Collection
    {
        return ClassBooking::query()
            ->whereHas('membership', fn ($q) => $q->where('user_id', $userId))
            ->with(['schedule.gymClass', 'membership'])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
