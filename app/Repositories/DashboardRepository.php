<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\Membership;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardRepository
{
    /**
     * KPIs and chart series for the admin dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardSummary(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $membershipCounts = Membership::query()
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $membershipByStatus = array_merge(
            ['active' => 0, 'inactive' => 0, 'cancelled' => 0],
            $membershipCounts
        );

        return [
            'membership_by_status' => $membershipByStatus,
            'memberships_total' => array_sum($membershipByStatus),
            'new_memberships_this_month' => Membership::query()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'unpaid_invoices_count' => Invoice::query()->where('status', 'unpaid')->count(),
            'revenue_this_month' => (float) Payment::query()
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('amount'),
            'revenue_all_time' => (float) Payment::query()
                ->where('status', 'completed')
                ->sum('amount'),
            'membership_status_chart' => [
                'labels' => [__('Active'), __('Inactive'), __('Cancelled')],
                'data' => [
                    (int) $membershipByStatus['active'],
                    (int) $membershipByStatus['inactive'],
                    (int) $membershipByStatus['cancelled'],
                ],
            ],
            'revenue_by_month' => $this->getCompletedPaymentTotalsByMonth(6),
            'recent_memberships' => $this->getRecentMemberships(5),
            'recent_payments' => $this->getRecentPayments(5),
        ];
    }

    /**
     * Sum of completed payment amounts per calendar month for the last N months (including current).
     *
     * @return array<int, array{label: string, year_month: string, total: float}>
     */
    public function getCompletedPaymentTotalsByMonth(int $months): array
    {
        $months = max(1, min(24, $months));
        $out = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            $total = (float) Payment::query()
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$start, $end])
                ->sum('amount');
            $out[] = [
                'label' => $start->translatedFormat('M Y'),
                'year_month' => $start->format('Y-m'),
                'total' => $total,
            ];
        }

        return $out;
    }

    /**
     * @return Collection<int, Membership>
     */
    public function getRecentMemberships(int $limit = 5): Collection
    {
        return Membership::query()
            ->with(['user:id,first_name,last_name', 'package:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getRecentPayments(int $limit = 5): Collection
    {
        return Payment::query()
            ->with(['invoice:id,invoice_number', 'membership.user:id,first_name,last_name'])
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->limit($limit)
            ->get();
    }
}
