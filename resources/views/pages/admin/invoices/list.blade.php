@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Invoice | Report'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Invoices Report') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item">{{ __('Invoice') }}</li>
                    <li class="breadcrumb-item active">{{ __('Report') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="{{ __('Invoices Report') }}">
            <form id="invoiceFilterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label for="issued_from">{{ __('Issue From') }}</label>
                        <input type="date" name="issued_from" id="issued_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="issued_to">{{ __('Issue To') }}</label>
                        <input type="date" name="issued_to" id="issued_to" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="status">{{ __('Status') }}</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                            <option value="unpaid">{{ __('Unpaid') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="invoice_source">{{ __('Source') }}</label>
                        <select name="invoice_source" id="invoice_source" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            <option value="membership">{{ __('Membership') }}</option>
                            <option value="merchandise">{{ __('Merchandise') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="created_by_user_id">{{ __('Created By') }}</label>
                        <select name="created_by_user_id" id="created_by_user_id" class="form-control">
                            <option value="">{{ __('All') }}</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}">{{ $creator->getName() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <label class="d-block">{{ __('Export Columns') }}</label>
                        <div class="d-flex flex-wrap" style="gap: 0.75rem 1rem;">
                            @foreach($exportColumns as $key => $column)
                                <div class="form-check">
                                    <input class="form-check-input invoice-export-column" type="checkbox" value="{{ $key }}" id="invoice-column-{{ $key }}" {{ in_array($key, $defaultExportColumns, true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="invoice-column-{{ $key }}">{{ __($column['label']) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap" style="gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> {{ __('Apply Filters') }}
                    </button>
                    <button type="button" class="btn btn-secondary" id="resetInvoiceFilters">
                        {{ __('Reset') }}
                    </button>
                    <button type="button" class="btn btn-outline-success" id="exportInvoicesCsv">
                        <i class="fas fa-file-csv"></i> {{ __('Export CSV') }}
                    </button>
                    <button type="button" class="btn btn-outline-dark" id="printInvoicesReport">
                        <i class="fas fa-print"></i> {{ __('Print Report') }}
                    </button>
                </div>
            </form>

            <hr>

            <table id="invoicesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('Invoice Number') }}</th>
                    <th>{{ __('Customer / Member') }}</th>
                    <th>{{ __('Source') }}</th>
                    <th>{{ __('Package / Context') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Issue Date') }}</th>
                    <th>{{ __('Created By') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Action') }}</th>
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
            function selectedInvoiceColumns() {
                return $('.invoice-export-column:checked').map(function () {
                    return $(this).val();
                }).get();
            }

            function invoiceFilterData() {
                return {
                    issued_from: $('#issued_from').val(),
                    issued_to: $('#issued_to').val(),
                    status: $('#status').val(),
                    invoice_source: $('#invoice_source').val(),
                    created_by_user_id: $('#created_by_user_id').val()
                };
            }

            function buildInvoiceQuery(includeColumns) {
                let params = invoiceFilterData();
                if (includeColumns) {
                    let columns = selectedInvoiceColumns();
                    columns.forEach(function (column) {
                        if (!params.columns) {
                            params.columns = [];
                        }
                        params.columns.push(column);
                    });
                }

                return $.param(params, true);
            }

            let table = $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[6, 'desc']],
                ajax: {
                    url: "{{ route('admin.invoices.list.data') }}",
                    data: function (d) {
                        Object.assign(d, invoiceFilterData());
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
                    { data: 'invoice_number', name: 'invoice_number' },
                    { data: 'name', name: 'name' },
                    { data: 'source', name: 'source' },
                    { data: 'package', name: 'package' },
                    { data: 'amount', name: 'amount' },
                    { data: 'issued_date', name: 'issued_date' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });

            $('#invoiceFilterForm').on('submit', function (e) {
                e.preventDefault();
                table.ajax.reload();
            });

            $('#resetInvoiceFilters').on('click', function () {
                $('#invoiceFilterForm')[0].reset();
                table.ajax.reload();
            });

            $('#exportInvoicesCsv').on('click', function () {
                window.location = "{{ route('admin.invoices.export.csv') }}?" + buildInvoiceQuery(true);
            });

            $('#printInvoicesReport').on('click', function () {
                window.open("{{ route('admin.invoices.print') }}?" + buildInvoiceQuery(false), '_blank');
            });
        });
    </script>
@endsection
