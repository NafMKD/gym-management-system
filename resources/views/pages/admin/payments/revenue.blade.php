@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Revenue Overview'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Revenue Overview") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Reports") }}</li>
                    <li class="breadcrumb-item active">{{ __("Revenue Overview") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Revenue Report" no-message footer>
            <!-- Filters Section -->
            <form id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __("Start Date") }}</label>
                        <input type="date" name="start_date" id="start_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>{{ __("End Date") }}</label>
                        <input type="date" name="end_date" id="end_date" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>{{ __("Payment Method") }}</label>
                        <select name="payment_method" id="payment_method" class="form-control">
                            <option value="">{{ __("All") }}</option>
                            <option value="cash">{{ __("Cash") }}</option>
                            <option value="bank">{{ __("Bank") }}</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>{{ __("Status") }}</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">{{ __("All") }}</option>
                            <option value="pending">{{ __("Pending") }}</option>
                            <option value="completed">{{ __("Completed") }}</option>
                            <option value="failed">{{ __("Failed") }}</option>
                        </select>
                    </div>
                    <div class="col-md-3 mt-4">
                        <button type="submit" class="btn btn-primary">{{ __("Filter") }}</button>
                        <button type="reset" id="reset" class="btn btn-secondary">{{ __("Reset") }}</button>
                    </div>
                </div>
            </form>

            <hr>

            <!-- Revenue Summary -->
            <div class="alert alert-info">
                <h4>{{ __("Total Revenue:") }} {{ __('Birr')}} <span id="total-revenue">0.00</span></h4>
                <p>{{ __("Total Transactions:") }} <span id="total-transactions">0</span></p>
            </div>

            <!-- Payments DataTable -->
            <table class="table table-bordered" id="paymentsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __("Member") }}</th>
                        <th>{{ __("Invoice") }}</th>
                        <th>{{ __("Amount") }}</th>
                        <th>{{ __("Payment Method") }}</th>
                        <th>{{ __("Status") }}</th>
                        <th>{{ __("Payment Date") }}</th>
                    </tr>
                </thead>
            </table>

            <x-slot:footer></x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            let table = $('#paymentsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.payments.revenue.list') }}",
                    data: function (d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.payment_method = $('#payment_method').val();
                        d.status = $('#status').val();
                    }, 
                    error: function(xhr, status, error) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';

                        Object.keys(errors).forEach(function(key) {
                            errorMessages += errors[key].join('<br>') + '<br>';
                        });

                        Toast.fire({
                            icon: 'error',
                            title: 'Validation Errors',
                            html: errorMessages  
                        });

                        $('#filterForm input, #filterForm select').val(''); 
                        table.ajax.reload();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'membership_id' },
                    { data: 'invoice' },
                    { data: 'amount' },
                    { data: 'payment_method' },
                    { data: 'status' },
                    { data: 'payment_date' }
                ],
                drawCallback: function(settings) {
                    let api = this.api();
                    let total = api
                        .column(3, { page: 'all' })
                        .data()
                        .reduce(function (a, b) {
                            return parseFloat(a) + parseFloat(b.replace(/[\$,]/g, '')); // Removing any currency symbols or commas
                        }, 0);
                    let count = api.data().count();
                    $('#total-revenue').text(total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    $('#total-transactions').text(count);
                }
            });

            $('#filterForm').on('submit', function(e) {
                e.preventDefault();

                let startDate = $('#start_date').val();
                let endDate = $('#end_date').val();

                // Validation: If start date is set, end date is required
                if (startDate && !endDate) {
                    Toast.fire({
                        icon: 'warning',
                        title: 'Please select an end date if a start date is set.'
                    });
                    $('#end_date').focus();
                    return;
                }

                table.ajax.reload();
            });

            $('#reset').on('click', function() {
                $('#filterForm input, #filterForm select').val(''); 
                Toast.fire({
                    icon: 'success',
                    title: 'Filters have been reset.'
                });
                $('#paymentsTable').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
