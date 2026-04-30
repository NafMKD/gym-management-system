@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => __('Accountant') . ' | ' . __('Home')])
    <style>
        .accountant-dashboard {
            display: grid;
            gap: 1rem;
        }

        .accountant-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 1rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.22), transparent 34%),
                linear-gradient(135deg, #123c69 0%, #1f6f78 48%, #f2a541 100%);
            box-shadow: 0 0.85rem 2.2rem rgba(18, 60, 105, 0.18);
        }

        .accountant-hero::after {
            content: '';
            position: absolute;
            inset: auto -6rem -7rem auto;
            width: 15rem;
            height: 15rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
        }

        .accountant-hero .card-body {
            position: relative;
            z-index: 1;
            padding: 1.35rem 1.5rem 1.4rem;
        }

        .accountant-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .accountant-hero-copy {
            max-width: 52rem;
            margin-top: 1rem;
        }

        .accountant-hero-copy h2 {
            margin: 0 0 0.55rem;
            font-size: 1.85rem;
            line-height: 1.1;
        }

        .accountant-hero-copy p {
            margin: 0;
            color: rgba(255, 255, 255, 0.86);
        }

        .accountant-hero-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem 1rem;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.88);
        }

        .accountant-kpi-grid,
        .accountant-mini-grid,
        .accountant-chart-grid,
        .accountant-table-grid {
            display: grid;
            gap: 1rem;
        }

        .accountant-side-stack {
            display: grid;
            gap: 1rem;
        }

        .accountant-kpi-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .accountant-mini-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .accountant-chart-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .accountant-table-grid {
            grid-template-columns: 1.2fr 1fr;
        }

        .accountant-card {
            border: 1px solid rgba(18, 60, 105, 0.08);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 0.45rem 1.5rem rgba(15, 23, 42, 0.06);
        }

        .accountant-kpi {
            position: relative;
            overflow: hidden;
            padding: 1.1rem 1.15rem 1.15rem;
        }

        .accountant-kpi::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 0.34rem;
            background: var(--kpi-accent, #1f6f78);
        }

        .accountant-kpi-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.8rem;
            height: 2.8rem;
            border-radius: 0.85rem;
            background: rgba(31, 111, 120, 0.1);
            color: var(--kpi-accent, #1f6f78);
            font-size: 1.15rem;
        }

        .accountant-kpi-label {
            display: block;
            margin-top: 0.95rem;
            color: #5f6c7b;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .accountant-kpi-value {
            display: block;
            margin-top: 0.2rem;
            color: #152238;
            font-size: 1.65rem;
            line-height: 1.15;
            font-weight: 700;
        }

        .accountant-kpi-note {
            display: block;
            margin-top: 0.35rem;
            color: #7a8797;
            font-size: 0.83rem;
        }

        .accountant-mini {
            padding: 1rem 1.05rem;
        }

        .accountant-mini-label {
            display: block;
            color: #66758a;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .accountant-mini-value {
            display: block;
            margin-top: 0.35rem;
            color: #182235;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .accountant-panel .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
            background: transparent;
            padding: 1rem 1.15rem 0.85rem;
        }

        .accountant-panel .card-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #162237;
        }

        .accountant-panel-subtitle {
            margin: 0.2rem 0 0;
            color: #6b7788;
            font-size: 0.84rem;
        }

        .accountant-panel .card-body {
            padding: 1rem 1.15rem 1.15rem;
        }

        .accountant-chart-wrap {
            min-height: 290px;
        }

        .accountant-chart-wrap canvas {
            width: 100% !important;
            height: 290px !important;
        }

        .accountant-breakdown {
            display: grid;
            gap: 0.9rem;
        }

        .accountant-breakdown-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 0.75rem;
        }

        .accountant-breakdown strong {
            color: #182235;
        }

        .accountant-breakdown small {
            color: #748195;
        }

        .accountant-progress {
            height: 0.55rem;
            border-radius: 999px;
            background: #eef2f6;
            overflow: hidden;
        }

        .accountant-progress > span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #1f6f78 0%, #2ea39a 100%);
        }

        .accountant-quick-links {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .accountant-link {
            display: block;
            padding: 0.9rem 1rem;
            border: 1px solid rgba(31, 111, 120, 0.1);
            border-radius: 0.85rem;
            color: #18314f;
            background: #f7fbfb;
            transition: 0.18s ease;
        }

        .accountant-link:hover {
            text-decoration: none;
            color: #123c69;
            border-color: rgba(31, 111, 120, 0.24);
            transform: translateY(-1px);
        }

        .accountant-link strong {
            display: block;
            font-size: 0.95rem;
        }

        .accountant-link span {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.82rem;
            color: #6e7c8f;
        }

        .accountant-table th {
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #66758a;
        }

        .accountant-table td {
            vertical-align: middle;
        }

        .accountant-table .accountant-money {
            white-space: nowrap;
            text-align: right;
            font-weight: 600;
            color: #18314f;
        }

        .accountant-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.24rem 0.6rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .accountant-status-pill.is-overdue {
            color: #8a3b12;
            background: #fde6d8;
        }

        .accountant-status-pill.is-open {
            color: #2f5f8f;
            background: #e4effa;
        }

        .accountant-badge {
            display: inline-block;
            padding: 0.18rem 0.52rem;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 600;
            background: #edf3f7;
            color: #45607c;
        }

        @media (max-width: 1199.98px) {
            .accountant-kpi-grid,
            .accountant-mini-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991.98px) {
            .accountant-chart-grid,
            .accountant-table-grid,
            .accountant-quick-links {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .accountant-kpi-grid,
            .accountant-mini-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Accountant reports') }}</h1>
                <p class="text-muted mb-0">{{ __('Live finance and inventory status for accountant-only oversight.') }}</p>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    @php
        $headline = $summary['headline'] ?? [];
        $quickStats = $summary['quick_stats'] ?? [];
        $sourceBreakdown = $summary['source_breakdown'] ?? [];
        $receivablesTotal = max(0.01, (float) ($headline['receivables_total'] ?? 0));
        $membershipReceivablesPercent = min(100, round(((float) ($sourceBreakdown['membership_receivables'] ?? 0) / $receivablesTotal) * 100, 1));
        $merchandiseReceivablesPercent = min(100, round(((float) ($sourceBreakdown['merchandise_receivables'] ?? 0) / $receivablesTotal) * 100, 1));
    @endphp

    <x-content class="content">
        <div class="accountant-dashboard">
            <div class="card accountant-hero">
                <div class="card-body">
                    <div class="accountant-hero-eyebrow">
                        <i class="fas fa-calculator"></i>
                        <span>{{ __('Accountant dashboard') }}</span>
                    </div>
                    <div class="accountant-hero-copy">
                        <h2>{{ __('Finance, receivables, merchandise, and stock health in one place.') }}</h2>
                        <p>{{ __('Use this dashboard to see revenue momentum, unpaid exposure, refund pressure, and the current inventory picture before drilling into the detailed reports.') }}</p>
                    </div>
                    <div class="accountant-hero-meta">
                        <span>{{ __('Generated at') }}: <strong>{{ ($summary['generated_at'] ?? now('Africa/Addis_Ababa'))->format('d/m/Y H:i') }}</strong></span>
                        <span>{{ __('Members on file') }}: <strong>{{ number_format($quickStats['members_count'] ?? 0) }}</strong></span>
                        <span>{{ __('Active products') }}: <strong>{{ number_format($quickStats['active_products_count'] ?? 0) }}</strong></span>
                        <span>{{ __('Overdue receivables') }}: <strong>{{ number_format($quickStats['overdue_receivables_count'] ?? 0) }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="accountant-kpi-grid">
                <div class="accountant-card accountant-kpi" style="--kpi-accent:#1f6f78;">
                    <span class="accountant-kpi-icon"><i class="fas fa-chart-line"></i></span>
                    <span class="accountant-kpi-label">{{ __('Net Revenue This Month') }}</span>
                    <span class="accountant-kpi-value">{{ __('Birr') }} {{ number_format($headline['net_revenue_this_month'] ?? 0, 2) }}</span>
                    <span class="accountant-kpi-note">{{ __('Completed payments minus refunds in the current month.') }}</span>
                </div>
                <div class="accountant-card accountant-kpi" style="--kpi-accent:#c06c2b;">
                    <span class="accountant-kpi-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                    <span class="accountant-kpi-label">{{ __('Receivables Outstanding') }}</span>
                    <span class="accountant-kpi-value">{{ __('Birr') }} {{ number_format($headline['receivables_total'] ?? 0, 2) }}</span>
                    <span class="accountant-kpi-note">{{ __('Open invoice balance still waiting to be collected.') }}</span>
                </div>
                <div class="accountant-card accountant-kpi" style="--kpi-accent:#38598b;">
                    <span class="accountant-kpi-icon"><i class="fas fa-boxes"></i></span>
                    <span class="accountant-kpi-label">{{ __('Stock On Hand Value') }}</span>
                    <span class="accountant-kpi-value">{{ __('Birr') }} {{ number_format($headline['inventory_value_on_hand'] ?? 0, 2) }}</span>
                    <span class="accountant-kpi-note">{{ __('Current product inventory valued at listed unit prices.') }}</span>
                </div>
                <div class="accountant-card accountant-kpi" style="--kpi-accent:#9b2c2c;">
                    <span class="accountant-kpi-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <span class="accountant-kpi-label">{{ __('Low Stock Items') }}</span>
                    <span class="accountant-kpi-value">{{ number_format($headline['low_stock_products_count'] ?? 0) }}</span>
                    <span class="accountant-kpi-note">{{ __('Active products at or below their low-stock threshold.') }}</span>
                </div>
            </div>

            <div class="accountant-mini-grid">
                <div class="accountant-card accountant-mini">
                    <span class="accountant-mini-label">{{ __('Cash Collected This Month') }}</span>
                    <span class="accountant-mini-value">{{ __('Birr') }} {{ number_format($quickStats['cash_collected_this_month'] ?? 0, 2) }}</span>
                </div>
                <div class="accountant-card accountant-mini">
                    <span class="accountant-mini-label">{{ __('Bank Collected This Month') }}</span>
                    <span class="accountant-mini-value">{{ __('Birr') }} {{ number_format($quickStats['bank_collected_this_month'] ?? 0, 2) }}</span>
                </div>
                <div class="accountant-card accountant-mini">
                    <span class="accountant-mini-label">{{ __('Refunds This Month') }}</span>
                    <span class="accountant-mini-value">{{ __('Birr') }} {{ number_format($quickStats['refunds_this_month'] ?? 0, 2) }}</span>
                </div>
                <div class="accountant-card accountant-mini">
                    <span class="accountant-mini-label">{{ __('Stock Units On Hand') }}</span>
                    <span class="accountant-mini-value">{{ number_format($quickStats['stock_units_on_hand'] ?? 0) }}</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-7">
                    <div class="card accountant-card accountant-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">{{ __('Source Breakdown') }}</h3>
                                <p class="accountant-panel-subtitle">{{ __('Where the month is earning and where unpaid exposure is sitting.') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accountant-breakdown">
                                <div>
                                    <div class="accountant-breakdown-row">
                                        <div>
                                            <strong>{{ __('Membership revenue this month') }}</strong>
                                            <small class="d-block">{{ __('Recurring membership collections.') }}</small>
                                        </div>
                                        <strong>{{ __('Birr') }} {{ number_format($sourceBreakdown['membership_revenue_this_month'] ?? 0, 2) }}</strong>
                                    </div>
                                </div>
                                <div>
                                    <div class="accountant-breakdown-row">
                                        <div>
                                            <strong>{{ __('Merchandise revenue this month') }}</strong>
                                            <small class="d-block">{{ __('POS and over-the-counter merchandise sales.') }}</small>
                                        </div>
                                        <strong>{{ __('Birr') }} {{ number_format($sourceBreakdown['merchandise_revenue_this_month'] ?? 0, 2) }}</strong>
                                    </div>
                                </div>
                                <div>
                                    <div class="accountant-breakdown-row">
                                        <div>
                                            <strong>{{ __('Membership receivables') }}</strong>
                                            <small class="d-block">{{ __('Outstanding invoices tied to memberships.') }}</small>
                                        </div>
                                        <strong>{{ __('Birr') }} {{ number_format($sourceBreakdown['membership_receivables'] ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="accountant-progress mt-2"><span style="width: {{ $membershipReceivablesPercent }}%;"></span></div>
                                </div>
                                <div>
                                    <div class="accountant-breakdown-row">
                                        <div>
                                            <strong>{{ __('Merchandise receivables') }}</strong>
                                            <small class="d-block">{{ __('Any merchandise invoices still not fully settled.') }}</small>
                                        </div>
                                        <strong>{{ __('Birr') }} {{ number_format($sourceBreakdown['merchandise_receivables'] ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="accountant-progress mt-2"><span style="width: {{ $merchandiseReceivablesPercent }}%; background: linear-gradient(90deg, #f2a541 0%, #f6c453 100%);"></span></div>
                                </div>
                                <div class="accountant-breakdown-row">
                                    <div>
                                        <strong>{{ __('Overdue balance') }}</strong>
                                        <small class="d-block">{{ __('Invoices past due and still open.') }}</small>
                                    </div>
                                    <strong>{{ __('Birr') }} {{ number_format($quickStats['overdue_receivables_total'] ?? 0, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card accountant-card accountant-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">{{ __('Quick Links') }}</h3>
                                <p class="accountant-panel-subtitle">{{ __('Jump straight into the detailed reports when you need to validate a number.') }}</p>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accountant-quick-links">
                                <a href="{{ route('admin.payments.revenue.list') }}" class="accountant-link">
                                    <strong>{{ __('Revenue overview') }}</strong>
                                    <span>{{ __('Follow month performance and transaction mix.') }}</span>
                                </a>
                                <a href="{{ route('admin.invoices.list') }}" class="accountant-link">
                                    <strong>{{ __('Invoice report') }}</strong>
                                    <span>{{ __('Review issue dates, creators, and unpaid balances.') }}</span>
                                </a>
                                <a href="{{ route('admin.payments.list') }}" class="accountant-link">
                                    <strong>{{ __('Payment report') }}</strong>
                                    <span>{{ __('Audit methods, banks, refunds, and creators.') }}</span>
                                </a>
                                <a href="{{ route('admin.products.list') }}" class="accountant-link">
                                    <strong>{{ __('Product report') }}</strong>
                                    <span>{{ __('Inspect stock value, low stock, and quantity movement.') }}</span>
                                </a>
                                <a href="{{ route('admin.merchandise.history') }}" class="accountant-link">
                                    <strong>{{ __('Sales report') }}</strong>
                                    <span>{{ __('Track merchandise sales by product and salesman.') }}</span>
                                </a>
                                <a href="{{ route('admin.users.list') }}" class="accountant-link">
                                    <strong>{{ __('User report') }}</strong>
                                    <span>{{ __('Monitor member growth and registration timing.') }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accountant-chart-grid">
                <div class="card accountant-card accountant-panel">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Revenue Trend') }}</h3>
                            <p class="accountant-panel-subtitle">{{ __('Last six months of completed payment revenue split by source.') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accountant-chart-wrap">
                            <canvas id="revenueTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card accountant-card accountant-panel">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Payment Mix This Month') }}</h3>
                            <p class="accountant-panel-subtitle">{{ __('Cash, bank collections, and refunds for the current month.') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accountant-chart-wrap">
                            <canvas id="paymentMixChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card accountant-card accountant-panel">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Stock Movement Trend') }}</h3>
                            <p class="accountant-panel-subtitle">{{ __('Monthly flow of restocks, sold units, and manual adjustments.') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accountant-chart-wrap">
                            <canvas id="stockMovementChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card accountant-card accountant-panel">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Stock Health Snapshot') }}</h3>
                            <p class="accountant-panel-subtitle">{{ __('Current mix of healthy active, low-stock, and inactive products.') }}</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="accountant-chart-wrap">
                            <canvas id="stockHealthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accountant-table-grid">
                <div class="card accountant-card accountant-panel">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">{{ __('Receivables Watchlist') }}</h3>
                            <p class="accountant-panel-subtitle">{{ __('The most urgent open invoices to follow up right now.') }}</p>
                        </div>
                        <a href="{{ route('admin.invoices.list') }}" class="btn btn-sm btn-outline-secondary">{{ __('Open invoices') }}</a>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover table-sm mb-0 accountant-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Source') }}</th>
                                    <th>{{ __('Due') }}</th>
                                    <th class="text-right">{{ __('Outstanding') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($summary['receivables_watchlist'] ?? collect()) as $invoice)
                                    @php
                                        $customerName = ($invoice->invoice_source ?? 'membership') === 'merchandise'
                                            ? ($invoice->customer?->getName() ?? __('Walk-in'))
                                            : ($invoice->membership?->user?->getName() ?? 'N/A');
                                        $isOverdue = (int) ($invoice->days_overdue ?? 0) > 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.invoices.view', $invoice) }}" class="font-weight-bold">{{ $invoice->invoice_number }}</a>
                                        </td>
                                        <td>{{ $customerName }}</td>
                                        <td><span class="accountant-badge">{{ ucfirst((string) ($invoice->invoice_source ?? 'membership')) }}</span></td>
                                        <td>
                                            @if($invoice->due_date)
                                                {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}
                                                <span class="d-block mt-1 accountant-status-pill {{ $isOverdue ? 'is-overdue' : 'is-open' }}">
                                                    <i class="fas {{ $isOverdue ? 'fa-exclamation-circle' : 'fa-clock' }}"></i>
                                                    {{ $isOverdue ? __(':days days overdue', ['days' => $invoice->days_overdue]) : __('Open') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="accountant-money">{{ __('Birr') }} {{ number_format((float) ($invoice->outstanding_amount ?? 0), 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ __('No open receivables right now.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="accountant-side-stack">
                    <div class="card accountant-card accountant-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">{{ __('Top Merchandise Products This Month') }}</h3>
                                <p class="accountant-panel-subtitle">{{ __('Best-moving items by sold quantity and revenue.') }}</p>
                            </div>
                            <a href="{{ route('admin.merchandise.history') }}" class="btn btn-sm btn-outline-secondary">{{ __('Open sales report') }}</a>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm mb-0 accountant-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('SKU') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th class="text-right">{{ __('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($summary['top_products_this_month'] ?? collect()) as $line)
                                        <tr>
                                            <td>{{ $line->product?->name ?? __('Deleted product') }}</td>
                                            <td>{{ $line->product?->sku ?? '-' }}</td>
                                            <td>{{ number_format((int) ($line->quantity_sold ?? 0)) }}</td>
                                            <td class="accountant-money">{{ __('Birr') }} {{ number_format((float) ($line->revenue_total ?? 0), 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">{{ __('No merchandise sales recorded this month.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card accountant-card accountant-panel">
                        <div class="card-header">
                            <div>
                                <h3 class="card-title">{{ __('Recent Financial Activity') }}</h3>
                                <p class="accountant-panel-subtitle">{{ __('Latest completed payments and refunds recorded in the system.') }}</p>
                            </div>
                            <a href="{{ route('admin.payments.list') }}" class="btn btn-sm btn-outline-secondary">{{ __('Open payments') }}</a>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover table-sm mb-0 accountant-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Invoice') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Method') }}</th>
                                        <th class="text-right">{{ __('Amount') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(($summary['recent_activity'] ?? collect()) as $payment)
                                        @php
                                            $typeLabel = ($payment->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment');
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.payments.view', $payment) }}" class="font-weight-bold">{{ $payment->invoice?->invoice_number ?? 'N/A' }}</a>
                                                <span class="d-block text-muted small">{{ $payment->createdBy?->getName() ?? __('Legacy / Unknown') }} - {{ \Carbon\Carbon::parse($payment->payment_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') }}</span>
                                            </td>
                                            <td><span class="accountant-badge">{{ $typeLabel }}</span></td>
                                            <td>
                                                {{ ucfirst((string) $payment->payment_method) }}
                                                @if($payment->payment_bank)
                                                    <span class="d-block text-muted small">{{ strtoupper((string) $payment->payment_bank) }}</span>
                                                @endif
                                            </td>
                                            <td class="accountant-money">{{ __('Birr') }} {{ number_format((float) $payment->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">{{ __('No recent financial activity found.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    var revenueByMonth = @json($summary['revenue_by_month'] ?? []);
    var paymentMix = @json($summary['payment_mix_chart'] ?? ['labels' => [], 'data' => []]);
    var stockMovementByMonth = @json($summary['stock_movement_by_month'] ?? []);
    var stockHealth = @json($summary['stock_health_chart'] ?? ['labels' => [], 'data' => []]);

    var revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueTrendCtx, {
        type: 'bar',
        data: {
            labels: revenueByMonth.map(function (row) { return row.label; }),
            datasets: [
                {
                    label: @json(__('Membership')),
                    data: revenueByMonth.map(function (row) { return row.membership_total; }),
                    backgroundColor: 'rgba(31, 111, 120, 0.76)',
                    borderColor: 'rgba(31, 111, 120, 1)',
                    borderWidth: 1
                },
                {
                    label: @json(__('Merchandise')),
                    data: revenueByMonth.map(function (row) { return row.merchandise_total; }),
                    backgroundColor: 'rgba(242, 165, 65, 0.82)',
                    borderColor: 'rgba(242, 165, 65, 1)',
                    borderWidth: 1
                },
                {
                    type: 'line',
                    label: @json(__('Net total')),
                    data: revenueByMonth.map(function (row) { return row.total; }),
                    borderColor: '#123c69',
                    backgroundColor: 'rgba(18, 60, 105, 0.12)',
                    borderWidth: 2,
                    fill: false,
                    lineTension: 0.25
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom' },
            scales: {
                xAxes: [{ stacked: true }],
                yAxes: [{
                    stacked: true,
                    ticks: { beginAtZero: true }
                }]
            }
        }
    });

    var paymentMixCtx = document.getElementById('paymentMixChart').getContext('2d');
    new Chart(paymentMixCtx, {
        type: 'doughnut',
        data: {
            labels: paymentMix.labels,
            datasets: [{
                data: paymentMix.data,
                backgroundColor: ['#1f6f78', '#38598b', '#c06c2b'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom' }
        }
    });

    var stockMovementCtx = document.getElementById('stockMovementChart').getContext('2d');
    new Chart(stockMovementCtx, {
        type: 'bar',
        data: {
            labels: stockMovementByMonth.map(function (row) { return row.label; }),
            datasets: [
                {
                    label: @json(__('Restocked')),
                    data: stockMovementByMonth.map(function (row) { return row.restock_quantity; }),
                    backgroundColor: 'rgba(46, 163, 154, 0.8)',
                    borderColor: 'rgba(46, 163, 154, 1)',
                    borderWidth: 1
                },
                {
                    label: @json(__('Sold')),
                    data: stockMovementByMonth.map(function (row) { return row.sale_quantity; }),
                    backgroundColor: 'rgba(242, 165, 65, 0.78)',
                    borderColor: 'rgba(242, 165, 65, 1)',
                    borderWidth: 1
                },
                {
                    type: 'line',
                    label: @json(__('Adjustment net')),
                    data: stockMovementByMonth.map(function (row) { return row.adjustment_net; }),
                    borderColor: '#9b2c2c',
                    backgroundColor: 'rgba(155, 44, 44, 0.12)',
                    borderWidth: 2,
                    fill: false,
                    lineTension: 0.25
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom' },
            scales: {
                yAxes: [{
                    ticks: { beginAtZero: true }
                }]
            }
        }
    });

    var stockHealthCtx = document.getElementById('stockHealthChart').getContext('2d');
    new Chart(stockHealthCtx, {
        type: 'doughnut',
        data: {
            labels: stockHealth.labels,
            datasets: [{
                data: stockHealth.data,
                backgroundColor: ['#2ea39a', '#f2a541', '#9aa6b2'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            legend: { position: 'bottom' }
        }
    });
});
</script>
@endsection

