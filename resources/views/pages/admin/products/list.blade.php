@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Product Report'])
    <style>
        .report-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .report-summary-card {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem;
            background: #fff;
            padding: 1rem;
        }

        .report-summary-card span {
            display: block;
            color: #6c757d;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .report-summary-card strong {
            display: block;
            margin-top: 0.25rem;
            font-size: 1.4rem;
            line-height: 1.2;
        }

        @media (max-width: 991.98px) {
            .report-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 575.98px) {
            .report-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Product Movement Report') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Product Report') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Product Movement Report') }}">
            <x-slot:headerTools>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.products.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add product') }}</a>
                @endif
            </x-slot:headerTools>

            <form id="productReportForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">{{ __('From') }}</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $filters['start_date'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">{{ __('To') }}</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $filters['end_date'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="product_id">{{ __('Product') }}</label>
                        <select name="product_id" id="product_id" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ (string) ($filters['product_id'] ?? '') === (string) $product->id ? 'selected' : '' }}>
                                    {{ $product->name }}{{ $product->sku ? ' ('.$product->sku.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="reason">{{ __('Reason') }}</label>
                        <select name="reason" id="reason" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="restock" {{ ($filters['reason'] ?? '') === 'restock' ? 'selected' : '' }}>{{ __('Restock') }}</option>
                            <option value="sale" {{ ($filters['reason'] ?? '') === 'sale' ? 'selected' : '' }}>{{ __('Sale') }}</option>
                            <option value="adjustment" {{ ($filters['reason'] ?? '') === 'adjustment' ? 'selected' : '' }}>{{ __('Adjustment') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="actor_id">{{ __('Actor / Salesman') }}</label>
                        <select name="actor_id" id="actor_id" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach($actors as $actor)
                                <option value="{{ $actor->id }}" {{ (string) ($filters['actor_id'] ?? '') === (string) $actor->id ? 'selected' : '' }}>
                                    {{ $actor->getName() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="d-block">{{ __('Export Columns') }}</label>
                        <div class="d-flex flex-wrap" style="gap: 0.5rem 0.75rem;">
                            @foreach($exportColumns as $key => $column)
                                <div class="form-check mr-2">
                                    <input class="form-check-input product-export-column" type="checkbox" value="{{ $key }}" id="product-column-{{ $key }}" {{ in_array($key, $defaultExportColumns, true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="product-column-{{ $key }}">{{ __($column['label']) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> {{ __('Apply Filters') }}
                    </button>
                    <a href="{{ route('admin.products.list') }}" class="btn btn-secondary">{{ __('Reset') }}</a>
                    <button type="button" class="btn btn-outline-success" id="exportProductReport">
                        <i class="fas fa-file-csv"></i> {{ __('Export CSV') }}
                    </button>
                    <button type="button" class="btn btn-outline-dark" id="printProductReport">
                        <i class="fas fa-print"></i> {{ __('Print Report') }}
                    </button>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    {{ __('Date, reason, and actor filters affect the movement ledger and summary cards. Product selection also narrows the inventory snapshot below.') }}
                </p>
            </form>
        </x-card>

        <div class="report-summary-grid mb-3">
            <div class="report-summary-card">
                <span>{{ __('Products') }}</span>
                <strong>{{ number_format($summary['product_count']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Stock On Hand') }}</span>
                <strong>{{ number_format($summary['stock_on_hand']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Low Stock Items') }}</span>
                <strong>{{ number_format($summary['low_stock_count']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Quantity In') }}</span>
                <strong>{{ number_format($summary['quantity_in']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Quantity Out') }}</span>
                <strong>{{ number_format($summary['quantity_out']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Adjustment Net') }}</span>
                <strong>{{ number_format($summary['adjustment_net']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Sale Quantity') }}</span>
                <strong>{{ number_format($summary['sale_quantity']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Restock Quantity') }}</span>
                <strong>{{ number_format($summary['restock_quantity']) }}</strong>
            </div>
            <div class="report-summary-card">
                <span>{{ __('Sales Revenue') }}</span>
                <strong>{{ __('Birr') }} {{ number_format($summary['sales_revenue'], 2) }}</strong>
            </div>
        </div>

        <x-card title="{{ __('Inventory Snapshot') }}">
            <table id="productsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Unit Price') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </x-card>

        <x-card title="{{ __('Quantity Log') }}">
            <table id="movementTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
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
                <tbody></tbody>
            </table>
        </x-card>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    function productFilters() {
        return {
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            product_id: $('#product_id').val(),
            reason: $('#reason').val(),
            actor_id: $('#actor_id').val()
        };
    }

    function selectedProductColumns() {
        return $('.product-export-column:checked').map(function () {
            return $(this).val();
        }).get();
    }

    function productQuery(includeColumns) {
        let params = productFilters();
        if (includeColumns) {
            let columns = selectedProductColumns();
            columns.forEach(function (column) {
                if (!params.columns) {
                    params.columns = [];
                }
                params.columns.push(column);
            });
        }

        return $.param(params, true);
    }

    let productsTable = $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        ajax: {
            url: "{{ route('admin.products.list.data') }}",
            data: function (d) {
                d.product_id = $('#product_id').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'sku', name: 'sku' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'stock_quantity', name: 'stock_quantity' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });

    let movementTable = $('#movementTable').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc']],
        ajax: {
            url: "{{ route('admin.products.movement.data') }}",
            data: function (d) {
                Object.assign(d, productFilters());
            },
            error: function(xhr) {
                let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                let messages = Object.values(errors).flat().join('<br>');

                Toast.fire({
                    icon: 'error',
                    title: '{{ __('Validation Errors') }}',
                    html: messages || '{{ __('Please review your filters and try again.') }}'
                });
            }
        },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'product_name', name: 'product_name' },
            { data: 'sku', name: 'sku' },
            { data: 'reason', name: 'reason' },
            { data: 'quantity_change', name: 'quantity_change' },
            { data: 'actor', name: 'actor' },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'notes', name: 'notes' }
        ],
    });

    $('#productReportForm').on('submit', function (e) {
        e.preventDefault();
        window.location = "{{ route('admin.products.list') }}?" + productQuery(false);
    });

    $('#product_id').on('change', function () {
        productsTable.ajax.reload();
    });

    $('#exportProductReport').on('click', function () {
        window.location = "{{ route('admin.products.export.csv') }}?" + productQuery(true);
    });

    $('#printProductReport').on('click', function () {
        window.open("{{ route('admin.products.print') }}?" + productQuery(false), '_blank');
    });
});
</script>
@endsection
