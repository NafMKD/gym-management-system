@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Add Payment'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Add Payment") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Payments") }}</li>
                    <li class="breadcrumb-item active">{{ __("Add") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Payment Information" form="admin.payments.store" footer>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>{{ __("Invoice ID") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="invoice_id" type="text"
                                    class="form-control" name="invoice_id"
                                    value="{{ $invoice->id }}" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Membership ID") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="membership_id" type="text"
                                    class="form-control" name="membership_id"
                                    value="{{ $invoice->membership_id }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Amount") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="amount" type="number" step="0.01"
                                    class="form-control @error('amount') is-invalid @enderror" name="amount"
                                    value="{{ $invoice->amount }}" required autocomplete="amount">
                                @error('amount')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Payment Date") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="payment_date" type="datetime-local"
                                    class="form-control @error('payment_date') is-invalid @enderror" name="payment_date"
                                    value="{{ date('Y-m-d\TH:i') }}" max="{{ date('Y-m-d\TH:i') }}" required>
                                @error('payment_date')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Payment Method") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <select name="payment_method" id="payment_method"
                                        class="form-control @error('payment_method') is-invalid @enderror">
                                    <option value="cash">{{ __("Cash") }}</option>
                                    <option value="bank">{{ __("Bank") }}</option>
                                </select>
                                @error('payment_method')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Payment Bank") }}</label>
                                <select name="payment_bank" id="payment_bank"
                                        class="form-control @error('payment_bank') is-invalid @enderror">
                                    <option value="">{{ __("Select Bank") }}</option>
                                    <option value="telebirr">{{ __("Telebirr") }}</option>
                                    <option value="cbe">{{ __("CBE") }}</option>
                                    <option value="boa">{{ __("BOA") }}</option>
                                </select>
                                @error('payment_bank')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Bank Transaction Number") }}</label>
                                <input id="bank_transaction_number" placeholder="{{ __("Enter Bank Transaction Number") }}" type="text"
                                    class="form-control @error('bank_transaction_number') is-invalid @enderror" name="bank_transaction_number"
                                    value="{{ old('bank_transaction_number') }}" autocomplete="bank_transaction_number">
                                @error('bank_transaction_number')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <p class="float-left"><i class="text-danger font-weight-bold">*</i> {{ __("are required fields") }}</p>
                <button type="submit" class="btn btn-primary float-right loading-button">{{ __("Add Payment") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        // Set default behavior for bank fields
        const bankFields = ['#payment_bank', '#bank_transaction_number'];

        function toggleBankFields(paymentMethod) {
            if (paymentMethod === 'bank') {
                $(bankFields.join(',')).attr('required', true).parent().show();
            } else {
                $(bankFields.join(',')).attr('required', false).parent().hide();
            }
        }

        // Initialize based on default value
        toggleBankFields($('#payment_method').val());

        // Listen for payment method change
        $('#payment_method').change(function () {
            toggleBankFields($(this).val());
        });

        // Set payment date to current and disable future dates
        const currentDateTime = new Date().toISOString().slice(0, 16);
        $('#payment_date').val(currentDateTime).attr('max', currentDateTime);
    });
</script>
@endsection
