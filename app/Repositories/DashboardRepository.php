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

        $revenueMonthMembership = $this->sumCompletedPaymentsByInvoiceSources($startOfMonth, $endOfMonth, ['membership']);
        $revenueMonthMerchandise = $this->sumCompletedPaymentsByInvoiceSources($startOfMonth, $endOfMonth, ['merchandise']);
        $revenueAllMembership = $this->sumCompletedPaymentsByInvoiceSourcesAllTime(['membership']);
        $revenueAllMerchandise = $this->sumCompletedPaymentsByInvoiceSourcesAllTime(['merchandise']);

        return [
            'membership_by_status' => $membershipByStatus,
            'memberships_total' => array_sum($membershipByStatus),
            'new_memberships_this_month' => Membership::query()
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count(),
            'unpaid_invoices_count' => Invoice::query()->where('status', 'unpaid')->count(),
            'unpaid_merchandise_invoices_count' => Invoice::query()
                ->where('status', 'unpaid')
                ->where('invoice_source', 'merchandise')
                ->count(),
            'revenue_this_month' => $revenueMonthMembership + $revenueMonthMerchandise,
            'revenue_this_month_membership' => $revenueMonthMembership,
            'revenue_this_month_merchandise' => $revenueMonthMerchandise,
            'revenue_all_time' => $revenueAllMembership + $revenueAllMerchandise,
            'revenue_all_time_membership' => $revenueAllMembership,
            'revenue_all_time_merchandise' => $revenueAllMerchandise,
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
            $membershipTotal = $this->sumCompletedPaymentsByInvoiceSources($start, $end, ['membership']);
            $merchandiseTotal = $this->sumCompletedPaymentsByInvoiceSources($start, $end, ['merchandise']);
            $out[] = [
                'label' => $start->translatedFormat('M Y'),
                'year_month' => $start->format('Y-m'),
                'total' => $membershipTotal + $merchandiseTotal,
                'membership_total' => $membershipTotal,
                'merchandise_total' => $merchandiseTotal,
            ];
        }

        return $out;
    }

    /**
     * Sum completed payment amounts in a date range for invoices with given `invoice_source` values.
     */
    private function sumCompletedPaymentsByInvoiceSources(Carbon $start, Carbon $end, array $invoiceSources): float
    {
        return (float) Payment::query()
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$start, $end])
            ->whereHas('invoice', function ($q) use ($invoiceSources) {
                $q->whereIn('invoice_source', $invoiceSources);
            })
            ->sum('amount');
    }

    /**
     * Sum completed payments (all time) for given invoice sources.
     */
    private function sumCompletedPaymentsByInvoiceSourcesAllTime(array $invoiceSources): float
    {
        return (float) Payment::query()
            ->where('status', 'completed')
            ->whereHas('invoice', function ($q) use ($invoiceSources) {
                $q->whereIn('invoice_source', $invoiceSources);
            })
            ->sum('amount');
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
            ->with([
                'invoice:id,invoice_number,invoice_source,user_id',
                'invoice.customer:id,first_name,last_name',
                'membership.user:id,first_name,last_name',
            ])
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->limit($limit)
            ->get();
    }
}
