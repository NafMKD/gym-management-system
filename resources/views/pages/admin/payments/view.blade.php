@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Payment | View'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Payment Detail") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Payments") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.payments.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Payment Detail" no-message footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.payments.list') }}"><button type="button" class="btn btn-tool"><i
                        class="fas fa-arrow-left"></i>
                    {{ __("Back") }}
                    </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Invoice Number") }}:</dt>
                        <dd class="col-sm-9"><a href="{{ route('admin.invoices.view', $payment->invoice->id) }}">{{ $payment->invoice->invoice_number }}</a></dd>
                        <dt class="col-sm-3">{{ __("Membership ID") }}:</dt>
                        <dd class="col-sm-9"><a href="{{ route('admin.memberships.view', $payment->membership->id) }}">{{ $payment->membership->id }}</a></dd>
                        <dt class="col-sm-3">{{ __("Payment Date") }}:</dt>
                        <dd class="col-sm-9">{{ $payment->payment_date }}</dd>
                        <dt class="col-sm-3">{{ __("Amount") }}:</dt>
                        <dd class="col-sm-9">{{ number_format($payment->amount, 2, '.', ',') }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Payment Method") }}:</dt>
                        <dd class="col-sm-9">{{ ucfirst($payment->payment_method) }}</dd>
                        <dt class="col-sm-3">{{ __("Bank") }}:</dt>
                        <dd class="col-sm-9">{{ $payment->payment_bank ?? __("N/A") }}</dd>
                        <dt class="col-sm-3">{{ __("Transaction Number") }}:</dt>
                        <dd class="col-sm-9">{{ $payment->bank_transaction_number ?? __("N/A") }}</dd>
                        <dt class="col-sm-3">{{ __("Status") }}:</dt>
                        <dd class="col-sm-9 {{ $payment->status === 'completed' ? 'text-success' : ($payment->status === 'failed' ? 'text-danger' : 'text-warning') }}">
                            {{ ucfirst($payment->status) }}
                        </dd>
                    </dl>
                </div>
            </div>
            <x-slot:footer>
                @if($payment->status !== 'completed' && $payment->status !== 'failed')
                <button type="button" id="markCompleted" class="btn btn-success float-right">{{ __("Mark as Completed") }}</button>
                <button type="button" id="markFailed" class="btn btn-danger float-left">{{ __("Mark as Failed") }}</button>
                @endif
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        $(function () {
            $('#markCompleted').on('click', function() {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You are about to mark this payment as completed!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, complete it!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.payments.mark.completed') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                payment_id: '{{ $payment->id }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    '{{ __("Completed!") }}',
                                    '{{ __("The payment has been marked as completed.") }}',
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    '{{ __("Error!") }}',
                                    `{{ __("`+xhr.responseJSON.error+`") }}`,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            $('#markFailed').on('click', function() {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You are about to mark this payment as failed!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, mark as failed!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.payments.mark.failed') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                payment_id: '{{ $payment->id }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    '{{ __("Failed!") }}',
                                    '{{ __("The payment has been marked as failed.") }}',
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                Swal.fire(
                                    '{{ __("Error!") }}',
                                    `{{ __("`+xhr.responseJSON.error+`") }}`,
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
