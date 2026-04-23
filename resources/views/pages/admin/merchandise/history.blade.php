@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | ' . __('Sales history')])
    <link rel="stylesheet" href="{{ asset('assets/css/desk-shell.css') }}">
    <style>
        .sales-history-shell {
            display: grid;
            gap: 1rem;
        }

        .sales-history-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
        }

        .sales-history-toolbar .form-inline {
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: end;
        }

        .sales-history-toolbar .form-group {
            margin-bottom: 0;
        }

        .sales-history-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .sales-history-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .sales-history-summary-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.9rem 1rem;
        }

        .sales-history-summary-card span {
            display: block;
            color: #6c757d;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sales-history-summary-card strong {
            display: block;
            margin-top: 0.25rem;
            font-size: 1.35rem;
            line-height: 1.2;
        }

        .sales-history-day-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.65rem;
            background: #fff;
            overflow: hidden;
        }

        .sales-history-day-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 1rem 1.15rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            background: #f8f9fa;
        }

        .sales-history-day-title {
            margin: 0;
            font-size: 1.05rem;
        }

        .sales-history-day-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            color: #6c757d;
            font-size: 0.86rem;
        }

        .sales-history-table-wrap {
            overflow-x: auto;
        }

        .sales-history-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1120px;
        }

        .sales-history-table caption {
            display: none;
        }

        .sales-history-table th,
        .sales-history-table td {
            padding: 0.7rem 0.75rem;
            border: 1px solid #dee2e6;
            vertical-align: top;
            font-size: 0.9rem;
        }

        .sales-history-table thead th {
            background: #eef2f6;
            white-space: nowrap;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sales-history-table tbody tr:nth-child(even) {
            background: #fcfcfd;
        }

        .sales-history-table .sales-history-invoice-cell {
            background: #fbfcfe;
            white-space: nowrap;
        }

        .sales-history-table .sales-history-line-cell {
            white-space: nowrap;
        }

        .sales-history-table .sales-history-money {
            text-align: right;
            white-space: nowrap;
        }

        .sales-history-table .sales-history-product {
            font-weight: 600;
        }

        .sales-history-table .sales-history-subtle {
            display: block;
            margin-top: 0.15rem;
            color: #6c757d;
            font-size: 0.8rem;
            line-height: 1.35;
        }

        .sales-history-table tfoot td {
            background: #f8f9fa;
            font-weight: 700;
        }

        .sales-history-empty {
            padding: 2rem 1.25rem;
            text-align: center;
            color: #6c757d;
        }

        .sales-history-report-stamp {
            color: #6c757d;
            font-size: 0.82rem;
        }

        @media (max-width: 991.98px) {
            .sales-history-summary {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .sales-history-summary {
                grid-template-columns: 1fr;
            }
        }

        @page {
            size: A4 landscape;
            margin: 12mm 10mm 18mm;
        }

        @media print {
            body {
                margin: 0 !important;
                background: #ffffff !important;
            }

            .main-sidebar,
            .main-header,
            .content-header,
            .main-footer,
            .no-print {
                display: none !important;
            }

            .content-wrapper,
            .content,
            .container-fluid,
            .sales-history-shell {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .sales-history-shell > :not(.sales-history-day-card) {
                display: none !important;
            }

            .sales-history-day-card {
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                margin: 0 0 0.8rem !important;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .sales-history-table-wrap {
                overflow: visible !important;
            }

            .sales-history-table {
                min-width: 0 !important;
            }

            .sales-history-table caption {
                display: table-caption;
                caption-side: top;
                padding: 0 0 0.45rem;
                color: #212529;
                font-size: 12px;
                font-weight: 700;
                text-align: left;
            }

            .sales-history-table th,
            .sales-history-table td {
                padding: 0.45rem 0.5rem;
                font-size: 11px;
            }

            .sales-history-table thead th {
                font-size: 10px;
            }

            .sales-history-invoice-group {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-1 mb-sm-2 align-items-center">
            <div class="col">
                <h1 class="m-0 h4">{{ __('Sales history') }}</h1>
                <p class="text-muted small mb-0 d-none d-sm-block">{{ __('Daily merchandise sales report for accountant handoff.') }}</p>
            </div>
            <div class="col-auto no-print">
                <a href="{{ route('admin.merchandise.checkout') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-cash-register"></i> {{ __('Back to POS') }}
                </a>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="sales-history-shell">
            <div class="card card-default shadow-sm no-print">
                <div class="card-body">
                    <div class="sales-history-toolbar">
                        <form method="GET" action="{{ route('admin.merchandise.history') }}" class="form-inline">
                            <div class="form-group">
                                <label for="date" class="small font-weight-bold mb-1 d-block">{{ __('Report date') }}</label>
                                <input type="date" name="date" id="date" value="{{ $selectedDate }}" class="form-control form-control-sm">
                            </div>
                            <div class="sales-history-toolbar-actions">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> {{ __('Apply') }}
                                </button>
                                <a href="{{ route('admin.merchandise.history') }}" class="btn btn-outline-secondary btn-sm">
                                    {{ __('Today') }}
                                </a>
                            </div>
                        </form>

                        <div class="sales-history-toolbar-actions">
                            <button type="button" class="btn btn-outline-dark btn-sm" id="printSalesHistory">
                                <i class="fas fa-print"></i> {{ __('Print report') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sales-history-summary no-print">
                <div class="sales-history-summary-card">
                    <span>{{ __('Invoices') }}</span>
                    <strong>{{ number_format($reportSummary['invoice_count']) }}</strong>
                </div>
                <div class="sales-history-summary-card">
                    <span>{{ __('Lines') }}</span>
                    <strong>{{ number_format($reportSummary['line_count']) }}</strong>
                </div>
                <div class="sales-history-summary-card">
                    <span>{{ __('Items sold') }}</span>
                    <strong>{{ number_format($reportSummary['item_quantity']) }}</strong>
                </div>
                <div class="sales-history-summary-card">
                    <span>{{ __('Gross total') }}</span>
                    <strong>{{ __('Birr') }} {{ number_format($reportSummary['gross_total'], 2) }}</strong>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 no-print">
                <div>
                    <h2 class="h5 mb-1">{{ __('Merchandise Sales Report') }}</h2>
                    <p class="text-muted mb-0">{{ __('Grouped by sales date for cashier-to-accountant handoff.') }}</p>
                </div>
                <div class="sales-history-report-stamp">
                    {{ __('Selected date') }}: <strong>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $selectedDate, 'Africa/Addis_Ababa')->format('d/m/Y') }}</strong>
                </div>
            </div>

            @forelse($groupedSales as $group)
                <section class="sales-history-day-card">
                    <div class="sales-history-day-header no-print">
                        <div>
                            <h3 class="sales-history-day-title">{{ $group['date_label'] }}</h3>
                            <div class="sales-history-day-meta">
                                <span>{{ __('Invoices') }}: {{ number_format($group['summary']['invoice_count']) }}</span>
                                <span>{{ __('Items sold') }}: {{ number_format($group['summary']['item_quantity']) }}</span>
                                <span>{{ __('Gross total') }}: {{ __('Birr') }} {{ number_format($group['summary']['gross_total'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="sales-history-table-wrap">
                        <table class="sales-history-table">
                            <caption>
                                {{ $group['date_label'] }} |
                                {{ __('Invoices') }}: {{ number_format($group['summary']['invoice_count']) }} |
                                {{ __('Items sold') }}: {{ number_format($group['summary']['item_quantity']) }} |
                                {{ __('Gross total') }}: {{ __('Birr') }} {{ number_format($group['summary']['gross_total'], 2) }}
                            </caption>
                            <thead>
                                <tr>
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Payment') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('SKU') }}</th>
                                    <th>{{ __('Qty') }}</th>
                                    <th class="text-right">{{ __('Unit price') }}</th>
                                    <th class="text-right">{{ __('Line total') }}</th>
                                    <th class="text-right">{{ __('Invoice total') }}</th>
                                </tr>
                            </thead>
                            @foreach($group['invoices'] as $invoice)
                                <tbody class="sales-history-invoice-group">
                                    @foreach($invoice['lines'] as $lineIndex => $line)
                                        <tr>
                                            @if($lineIndex === 0)
                                                <td rowspan="{{ $invoice['row_count'] }}" class="sales-history-invoice-cell">
                                                    <strong>#{{ $invoice['id'] }}</strong>
                                                    <span class="sales-history-subtle">{{ $invoice['invoice_number'] }}</span>
                                                    <span class="sales-history-subtle">{{ ucfirst($invoice['status']) }}</span>
                                                </td>
                                                <td rowspan="{{ $invoice['row_count'] }}" class="sales-history-invoice-cell">
                                                    {{ $invoice['issued_at'] }}
                                                </td>
                                                <td rowspan="{{ $invoice['row_count'] }}" class="sales-history-invoice-cell">
                                                    <strong>{{ $invoice['customer_name'] }}</strong>
                                                    @if($invoice['customer_phone'])
                                                        <span class="sales-history-subtle">{{ $invoice['customer_phone'] }}</span>
                                                    @endif
                                                </td>
                                                <td rowspan="{{ $invoice['row_count'] }}" class="sales-history-invoice-cell">
                                                    <strong>{{ $invoice['payment_label'] }}</strong>
                                                    @if($invoice['payment_detail'])
                                                        <span class="sales-history-subtle">{{ $invoice['payment_detail'] }}</span>
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="sales-history-line-cell">
                                                <span class="sales-history-product">{{ $line['product_name'] }}</span>
                                            </td>
                                            <td class="sales-history-line-cell">
                                                {{ $line['sku'] ?: '—' }}
                                            </td>
                                            <td class="sales-history-line-cell">
                                                {{ number_format($line['quantity']) }}
                                            </td>
                                            <td class="sales-history-money">
                                                {{ __('Birr') }} {{ number_format($line['unit_price'], 2) }}
                                            </td>
                                            <td class="sales-history-money">
                                                {{ __('Birr') }} {{ number_format($line['line_total'], 2) }}
                                            </td>
                                            @if($lineIndex === 0)
                                                <td rowspan="{{ $invoice['row_count'] }}" class="sales-history-money sales-history-invoice-cell">
                                                    <strong>{{ __('Birr') }} {{ number_format($invoice['invoice_total'], 2) }}</strong>
                                                    <span class="sales-history-subtle">{{ __('Qty') }}: {{ number_format($invoice['quantity_total']) }}</span>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            @endforeach
                            <tfoot>
                                <tr>
                                    <td colspan="6">{{ __('Daily total') }}</td>
                                    <td>{{ number_format($group['summary']['item_quantity']) }}</td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ __('Birr') }} {{ number_format($group['summary']['gross_total'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @empty
                <div class="sales-history-day-card no-print">
                    <div class="sales-history-empty">
                        <h3 class="h5">{{ __('No merchandise sales found for this date.') }}</h3>
                        <p class="mb-0">{{ __('Choose another date or return to POS to record a sale.') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
    </x-content>
@endsection

@section('script')
<script>
    (function () {
        var printButton = document.getElementById('printSalesHistory');
        if (printButton) {
            printButton.addEventListener('click', function () {
                window.print();
            });
        }
    })();
</script>
@endsection
