@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Revenue Overview'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Revenue Overview') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item">{{ __('Reports') }}</li>
                    <li class="breadcrumb-item active">{{ __('Revenue Overview') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="{{ __('Revenue Report') }}" no-message footer>
            <form id="revenueFilterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">{{ __('Payment From') }}</label>
                        <input type="date" name="start_date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">{{ __('Payment To') }}</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="payment_method">{{ __('Method') }}</label>
                        <select name="payment_method" id="payment_method" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="bank">{{ __('Bank') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="payment_bank">{{ __('Bank') }}</label>
                        <select name="payment_bank" id="payment_bank" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="telebirr">Telebirr</option>
                            <option value="cbe">CBE</option>
                            <option value="boa">BOA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="failed">{{ __('Failed') }}</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="payment_type">{{ __('Type') }}</label>
                        <select name="payment_type" id="payment_type" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="payment">{{ __('Payment') }}</option>
                            <option value="refund">{{ __('Refund') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="invoice_source">{{ __('Source') }}</label>
                        <select name="invoice_source" id="invoice_source" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="membership">{{ __('Membership') }}</option>
                            <option value="merchandise">{{ __('Merchandise') }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="created_by_user_id">{{ __('Created By') }}</label>
                        <select name="created_by_user_id" id="created_by_user_id" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}">{{ $creator->getName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="d-block">{{ __('Export Columns') }}</label>
                        <div class="d-flex flex-wrap" style="gap: 0.5rem 0.75rem; max-height: 120px; overflow-y: auto; padding-top: 0.25rem;">
                            @foreach($exportColumns as $key => $column)
                                <div class="form-check mr-2">
                                    <input class="form-check-input revenue-export-column" type="checkbox" value="{{ $key }}" id="revenue-column-{{ $key }}" {{ in_array($key, $defaultExportColumns, true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="revenue-column-{{ $key }}">{{ __($column['label']) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> {{ __('Apply Filters') }}
                    </button>
                    <button type="button" class="btn btn-secondary" id="resetRevenueFilters">
                        {{ __('Reset') }}
                    </button>
                    <button type="button" class="btn btn-outline-success" id="exportRevenueCsv">
                        <i class="fas fa-file-csv"></i> {{ __('Export CSV') }}
                    </button>
                    <button type="button" class="btn btn-outline-dark" id="printRevenueReport">
                        <i class="fas fa-print"></i> {{ __('Print Report') }}
                    </button>
                </div>
            </form>

            <hr>

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="alert alert-info h-100">
                        <div class="small text-uppercase text-white">{{ __('Net Revenue') }}</div>
                        <div class="h4 mb-0">{{ __('Birr') }} <span id="total-revenue">0.00</span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-secondary h-100">
                        <div class="small text-uppercase text-white">{{ __('Transactions') }}</div>
                        <div class="h4 mb-0"><span id="total-transactions">0</span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-success h-100">
                        <div class="small text-uppercase text-white">{{ __('Payments') }}</div>
                        <div class="h4 mb-0"><span id="total-payments">0</span></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="alert alert-warning h-100">
                        <div class="small text-uppercase text-white">{{ __('Refunds') }}</div>
                        <div class="h4 mb-0"><span id="total-refunds">0</span></div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered" id="paymentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Customer / Member') }}</th>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Method') }}</th>
                        <th>{{ __('Bank') }}</th>
                        <th>{{ __('Payment Date') }}</th>
                        <th>{{ __('Created By') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
            </table>

            <x-slot:footer></x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        function revenueFilters() {
            return {
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
                payment_method: $('#payment_method').val(),
                payment_bank: $('#payment_bank').val(),
                status: $('#status').val(),
                payment_type: $('#payment_type').val(),
                invoice_source: $('#invoice_source').val(),
                created_by_user_id: $('#created_by_user_id').val()
            };
        }

        function selectedRevenueColumns() {
            return $('.revenue-export-column:checked').map(function () {
                return $(this).val();
            }).get();
        }

        function revenueQuery(includeColumns) {
            let params = revenueFilters();
            if (includeColumns) {
                let columns = selectedRevenueColumns();
                columns.forEach(function (column) {
                    if (!params.columns) {
                        params.columns = [];
                    }
                    params.columns.push(column);
                });
            }

            return $.param(params, true);
        }

        function toggleRevenueBank() {
            let bankMethod = $('#payment_method').val() === 'bank';
            $('#payment_bank').prop('disabled', !bankMethod && $('#payment_bank').val() === '');
            if (!bankMethod && $('#payment_bank').val() === '') {
                $('#payment_bank').prop('disabled', true);
            } else {
                $('#payment_bank').prop('disabled', false);
            }
        }

        function fetchTotalRevenue() {
            $.ajax({
                url: "{{ route('admin.payments.revenue.total') }}",
                type: "GET",
                data: revenueFilters(),
                success: function(response) {
                    let formattedTotal = Number.parseFloat(response.totalRevenue || 0).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    $('#total-revenue').text(formattedTotal);
                    $('#total-transactions').text(response.totalTransactions);
                    $('#total-payments').text(response.netPayments);
                    $('#total-refunds').text(response.refundTransactions);
                },
                error: function(xhr) {
                    let errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                    let messages = Object.values(errors).flat().join('<br>');

                    Toast.fire({
                        icon: 'error',
                        title: '{{ __('Validation Errors') }}',
                        html: messages || '{{ __('Failed to fetch total revenue.') }}'
                    });
                }
            });
        }

        $(function () {
            let table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[8, 'desc']],
                ajax: {
                    url: "{{ route('admin.payments.revenue.list') }}",
                    data: function (d) {
                        Object.assign(d, revenueFilters());
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
                    { data: 'name', name: 'name' },
                    { data: 'invoice', name: 'invoice' },
                    { data: 'source', name: 'source' },
                    { data: 'payment_type', name: 'payment_type' },
                    { data: 'amount', name: 'amount' },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'payment_bank', name: 'payment_bank' },
                    { data: 'payment_date', name: 'payment_date' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'status', name: 'status' }
                ],
                drawCallback: function() {
                    fetchTotalRevenue();
                }
            });

            $('#revenueFilterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#resetRevenueFilters').on('click', function() {
                $('#revenueFilterForm')[0].reset();
                toggleRevenueBank();
                table.ajax.reload();
                fetchTotalRevenue();
            });

            $('#payment_method').on('change', toggleRevenueBank);
            toggleRevenueBank();

            $('#exportRevenueCsv').on('click', function () {
                window.location = "{{ route('admin.payments.revenue.export.csv') }}?" + revenueQuery(true);
            });

            $('#printRevenueReport').on('click', function () {
                window.open("{{ route('admin.payments.revenue.print') }}?" + revenueQuery(false), '_blank');
            });

            fetchTotalRevenue();
        });
    </script>
@endsection
