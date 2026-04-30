<?php

namespace App\Repositories;

use App\Models\Invoice;
use App\Models\MerchandiseSaleLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccountantDashboardRepository
{
    /**
     * Build accountant-only finance and stock dashboard data.
     *
     * @return array<string, mixed>
     */
    public function getDashboardSummary(): array
    {
        $timezone = 'Africa/Addis_Ababa';
        $now = now($timezone);
        $monthStart = $now->copy()->startOfMonth()->utc();
        $monthEnd = $now->copy()->endOfMonth()->utc();

        $receivablesQuery = $this->getOutstandingInvoicesQuery()->whereRaw($this->outstandingAmountExpression().' > 0');
        $overdueReceivablesQuery = $this->getOutstandingInvoicesQuery()
            ->whereRaw($this->outstandingAmountExpression().' > 0')
            ->where('due_date', '<', $now->copy()->startOfDay()->utc());

        $cashCollectedThisMonth = $this->sumCompletedPayments($monthStart, $monthEnd, [
            'payment_type' => 'payment',
            'payment_method' => 'cash',
        ]);
        $bankCollectedThisMonth = $this->sumCompletedPayments($monthStart, $monthEnd, [
            'payment_type' => 'payment',
            'payment_method' => 'bank',
        ]);
        $refundsThisMonth = abs($this->sumCompletedPayments($monthStart, $monthEnd, [
            'payment_type' => 'refund',
        ]));

        $inventoryValue = (float) Product::query()
            ->selectRaw('COALESCE(SUM(stock_quantity * unit_price), 0) as inventory_value')
            ->value('inventory_value');

        $membersCount = (int) User::query()->where('role', 'member')->count();
        $activeProductsCount = (int) Product::query()->where('is_active', true)->count();
        $lowStockProductsCount = (int) Product::query()->where('is_active', true)->lowStock()->count();
        $stockUnitsOnHand = (int) Product::query()->sum('stock_quantity');

        return [
            'generated_at' => $now,
            'headline' => [
                'net_revenue_this_month' => $this->sumCompletedPayments($monthStart, $monthEnd),
                'receivables_total' => (float) (clone $receivablesQuery)->sum(DB::raw($this->outstandingAmountExpression())),
                'inventory_value_on_hand' => $inventoryValue,
                'low_stock_products_count' => $lowStockProductsCount,
            ],
            'quick_stats' => [
                'cash_collected_this_month' => $cashCollectedThisMonth,
                'bank_collected_this_month' => $bankCollectedThisMonth,
                'refunds_this_month' => $refundsThisMonth,
                'stock_units_on_hand' => $stockUnitsOnHand,
                'members_count' => $membersCount,
                'active_products_count' => $activeProductsCount,
                'overdue_receivables_count' => (int) (clone $overdueReceivablesQuery)->count(),
                'overdue_receivables_total' => (float) (clone $overdueReceivablesQuery)->sum(DB::raw($this->outstandingAmountExpression())),
            ],
            'revenue_by_month' => $this->getRevenueByMonth(6, $timezone),
            'payment_mix_chart' => [
                'labels' => [__('Cash collections'), __('Bank collections'), __('Refunds')],
                'data' => [
                    round($cashCollectedThisMonth, 2),
                    round($bankCollectedThisMonth, 2),
                    round($refundsThisMonth, 2),
                ],
            ],
            'stock_movement_by_month' => $this->getStockMovementByMonth(6, $timezone),
            'stock_health_chart' => [
                'labels' => [__('Healthy active'), __('Low stock'), __('Inactive')],
                'data' => [
                    (int) Product::query()->where('is_active', true)->whereColumn('stock_quantity', '>', 'low_stock_threshold')->count(),
                    $lowStockProductsCount,
                    (int) Product::query()->where('is_active', false)->count(),
                ],
            ],
            'receivables_watchlist' => $this->getReceivablesWatchlist($timezone, 6),
            'top_products_this_month' => $this->getTopProductsThisMonth($monthStart, $monthEnd, 6),
            'recent_activity' => $this->getRecentFinancialActivity(6),
            'source_breakdown' => [
                'membership_revenue_this_month' => $this->sumCompletedPaymentsBySource($monthStart, $monthEnd, 'membership'),
                'merchandise_revenue_this_month' => $this->sumCompletedPaymentsBySource($monthStart, $monthEnd, 'merchandise'),
                'membership_receivables' => (float) $this->sumOutstandingInvoicesBySource('membership'),
                'merchandise_receivables' => (float) $this->sumOutstandingInvoicesBySource('merchandise'),
            ],
        ];
    }

    /**
     * @return array<int, array{label:string,membership_total:float,merchandise_total:float,total:float}>
     */
    private function getRevenueByMonth(int $months, string $timezone): array
    {
        $months = max(1, min(24, $months));
        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $cursor = now($timezone)->subMonths($i);
            $start = $cursor->copy()->startOfMonth()->utc();
            $end = $cursor->copy()->endOfMonth()->utc();
            $membershipTotal = $this->sumCompletedPaymentsBySource($start, $end, 'membership');
            $merchandiseTotal = $this->sumCompletedPaymentsBySource($start, $end, 'merchandise');

            $series[] = [
                'label' => $cursor->translatedFormat('M Y'),
                'membership_total' => round($membershipTotal, 2),
                'merchandise_total' => round($merchandiseTotal, 2),
                'total' => round($membershipTotal + $merchandiseTotal, 2),
            ];
        }

        return $series;
    }

    /**
     * @return array<int, array{label:string,restock_quantity:int,sale_quantity:int,adjustment_net:int}>
     */
    private function getStockMovementByMonth(int $months, string $timezone): array
    {
        $months = max(1, min(24, $months));
        $series = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $cursor = now($timezone)->subMonths($i);
            $start = $cursor->copy()->startOfMonth()->utc();
            $end = $cursor->copy()->endOfMonth()->utc();

            $restockQuantity = (int) StockMovement::query()
                ->where('reason', 'restock')
                ->whereBetween('created_at', [$start, $end])
                ->sum('quantity_change');

            $saleQuantity = abs((int) StockMovement::query()
                ->where('reason', 'sale')
                ->whereBetween('created_at', [$start, $end])
                ->sum('quantity_change'));

            $adjustmentNet = (int) StockMovement::query()
                ->where('reason', 'adjustment')
                ->whereBetween('created_at', [$start, $end])
                ->sum('quantity_change');

            $series[] = [
                'label' => $cursor->translatedFormat('M Y'),
                'restock_quantity' => $restockQuantity,
                'sale_quantity' => $saleQuantity,
                'adjustment_net' => $adjustmentNet,
            ];
        }

        return $series;
    }

    /**
     * @return Collection<int, Invoice>
     */
    private function getReceivablesWatchlist(string $timezone, int $limit = 6): Collection
    {
        $today = now($timezone)->startOfDay();

        return $this->getOutstandingInvoicesQuery()
            ->whereRaw($this->outstandingAmountExpression().' > 0')
            ->with(['membership.user:id,first_name,last_name,phone', 'customer:id,first_name,last_name,phone'])
            ->orderBy('due_date')
            ->orderByDesc(DB::raw($this->outstandingAmountExpression()))
            ->limit($limit)
            ->get()
            ->each(function (Invoice $invoice) use ($today) {
                $dueDate = $invoice->due_date
                    ? Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')
                    : null;

                $invoice->setAttribute(
                    'days_overdue',
                    $dueDate && $dueDate->lt($today) ? $dueDate->diffInDays($today) : 0
                );
            });
    }

    /**
     * @return Collection<int, MerchandiseSaleLine>
     */
    private function getTopProductsThisMonth(Carbon $monthStart, Carbon $monthEnd, int $limit = 6): Collection
    {
        return MerchandiseSaleLine::query()
            ->selectRaw('product_id, SUM(quantity) as quantity_sold, SUM(line_total) as revenue_total')
            ->with('product:id,name,sku,stock_quantity,low_stock_threshold')
            ->whereHas('invoice', function (Builder $query) use ($monthStart, $monthEnd) {
                $query->where('invoice_source', 'merchandise')
                    ->whereBetween('issued_date', [$monthStart, $monthEnd]);
            })
            ->groupBy('product_id')
            ->orderByDesc('quantity_sold')
            ->orderByDesc('revenue_total')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Payment>
     */
    private function getRecentFinancialActivity(int $limit = 6): Collection
    {
        return Payment::query()
            ->with([
                'invoice:id,invoice_number,invoice_source,user_id',
                'invoice.customer:id,first_name,last_name',
                'membership.user:id,first_name,last_name',
                'createdBy:id,first_name,last_name',
            ])
            ->where('status', 'completed')
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    private function sumCompletedPayments(?Carbon $start = null, ?Carbon $end = null, array $filters = []): float
    {
        $query = Payment::query()->where('status', 'completed');

        if ($start && $end) {
            $query->whereBetween('payment_date', [$start, $end]);
        }

        if (! empty($filters['payment_type'])) {
            $query->where('payment_type', $filters['payment_type']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['invoice_source'])) {
            $query->whereHas('invoice', function (Builder $invoiceQuery) use ($filters) {
                $invoiceQuery->where('invoice_source', $filters['invoice_source']);
            });
        }

        return (float) $query->sum('amount');
    }

    private function sumCompletedPaymentsBySource(Carbon $start, Carbon $end, string $invoiceSource): float
    {
        return $this->sumCompletedPayments($start, $end, [
            'invoice_source' => $invoiceSource,
        ]);
    }

    private function sumOutstandingInvoicesBySource(string $invoiceSource): float
    {
        return (float) $this->getOutstandingInvoicesQuery()
            ->where('invoice_source', $invoiceSource)
            ->whereRaw($this->outstandingAmountExpression().' > 0')
            ->sum(DB::raw($this->outstandingAmountExpression()));
    }

    private function getOutstandingInvoicesQuery(): Builder
    {
        return Invoice::query()
            ->leftJoinSub($this->completedPaymentTotalsSubquery(), 'payment_totals', function ($join) {
                $join->on('payment_totals.invoice_id', '=', 'invoices.id');
            })
            ->select('invoices.*')
            ->selectRaw($this->outstandingAmountExpression().' as outstanding_amount');
    }

    private function completedPaymentTotalsSubquery(): Builder
    {
        return Payment::query()
            ->selectRaw('invoice_id, COALESCE(SUM(CASE WHEN status = ? THEN amount ELSE 0 END), 0) as completed_total', ['completed'])
            ->groupBy('invoice_id');
    }

    private function outstandingAmountExpression(): string
    {
        return 'CASE WHEN invoices.amount - COALESCE(payment_totals.completed_total, 0) > 0 THEN invoices.amount - COALESCE(payment_totals.completed_total, 0) ELSE 0 END';
    }
}
