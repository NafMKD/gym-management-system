@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Product Report | Print'])
    <style>
        .product-print-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .product-print-summary-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.85rem 1rem;
        }

        .product-print-summary-card span {
            display: block;
            color: #6c757d;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .product-print-summary-card strong {
            display: block;
            margin-top: 0.25rem;
            font-size: 1.25rem;
        }

        @media print {
            .main-sidebar,
            .main-header,
            .content-header,
            .main-footer,
            .no-print {
                display: none !important;
            }

            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header no-print">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Product Movement Report') }}</h1>
            </div>
            <div class="col-sm-6 text-sm-right">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> {{ __('Print') }}
                </button>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-body">
                <h2 class="h4 mb-2">{{ __('Product Movement Report') }}</h2>
                <div class="text-muted mb-3">
                    <div>{{ __('Generated at') }}: <strong>{{ $reportGeneratedAt->format('d/m/Y H:i') }}</strong></div>
                    <div>{{ __('Filters') }}: <strong>{{ $filterSummary }}</strong></div>
                </div>

                <div class="product-print-summary">
                    <div class="product-print-summary-card"><span>{{ __('Products') }}</span><strong>{{ number_format($summary['product_count']) }}</strong></div>
                    <div class="product-print-summary-card"><span>{{ __('Stock On Hand') }}</span><strong>{{ number_format($summary['stock_on_hand']) }}</strong></div>
                    <div class="product-print-summary-card"><span>{{ __('Low Stock Items') }}</span><strong>{{ number_format($summary['low_stock_count']) }}</strong></div>
                    <div class="product-print-summary-card"><span>{{ __('Quantity In') }}</span><strong>{{ number_format($summary['quantity_in']) }}</strong></div>
                    <div class="product-print-summary-card"><span>{{ __('Quantity Out') }}</span><strong>{{ number_format($summary['quantity_out']) }}</strong></div>
                    <div class="product-print-summary-card"><span>{{ __('Sales Revenue') }}</span><strong>{{ __('Birr') }} {{ number_format($summary['sales_revenue'], 2) }}</strong></div>
                </div>

                <h3 class="h5">{{ __('Inventory Snapshot') }}</h3>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Unit Price') }}</th>
                                <th>{{ __('Stock') }}</th>
                                <th>{{ __('Active') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventory as $product)
                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->sku ?? '-' }}</td>
                                    <td>{{ number_format((float) $product->unit_price, 2) }}</td>
                                    <td>{{ number_format((int) $product->stock_quantity) }}</td>
                                    <td>{{ $product->is_active ? __('Yes') : __('No') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">{{ __('No products matched the selected filters.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h3 class="h5">{{ __('Quantity Log') }}</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Movement Date') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('SKU') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Quantity Change') }}</th>
                                <th>{{ __('Actor / Salesman') }}</th>
                                <th>{{ __('Invoice') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movements as $movement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($movement->created_at)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') }}</td>
                                    <td>{{ $movement->product?->name ?? __('Deleted product') }}</td>
                                    <td>{{ $movement->product?->sku ?? '-' }}</td>
                                    <td>{{ ucfirst((string) $movement->reason) }}</td>
                                    <td>{{ number_format($movement->quantity_change) }}</td>
                                    <td>{{ $movement->user?->getName() ?? __('Legacy / Unknown') }}</td>
                                    <td>{{ $movement->invoice?->invoice_number ?? '-' }}</td>
                                    <td>{{ $movement->invoice?->customer?->getName() ?? '-' }}</td>
                                    <td>{{ $movement->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">{{ __('No stock movements matched the selected filters.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-content>
@endsection
