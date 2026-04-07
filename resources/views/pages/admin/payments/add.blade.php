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
                        <div class="col-md-4">
                            <div class="form-group">
                                @if(($invoice->invoice_source ?? 'membership') === 'merchandise')
                                    <label>{{ __("Customer") }}</label>
                                    <input type="text" class="form-control" value="{{ $invoice->customer?->getName() ?? '—' }}" readonly>
                                    <input type="hidden" name="membership_id" value="">
                                @else
                                    <label>{{ __("Membership ID") }}</label> <i class="text-danger font-weight-bold">*</i>
                                    <input id="membership_id" type="text"
                                        class="form-control" name="membership_id"
                                        value="{{ $invoice->membership_id }}" readonly>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __("Payment Method") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <select name="payment_method" id="payment_method"
                                        class="form-control @error('payment_method') is-invalid @enderror">
                                    <option value="cash">{{ __("Cash") }}</option>
                                    <option value="bank" @selected(old('payment_method') === 'bank')>{{ __("Bank") }}</option>
                                </select>
                                @error('payment_method')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __("Amount") }}</label> <i class="text-danger font-weight-bold">*</i>
                                @php
                                    $remaining = (float) $invoice->amount - (float) $invoice->payments()->where('status', 'completed')->sum('amount');
                                @endphp
                                <input id="amount" type="number" step="0.01"
                                    class="form-control @error('amount') is-invalid @enderror" name="amount"
                                    value="{{ old('amount', number_format(max(0, $remaining), 2, '.', '')) }}" required autocomplete="amount">
                                @error('amount')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row payment-bank-fields" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Bank") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <select name="payment_bank" id="payment_bank"
                                        class="form-control @error('payment_bank') is-invalid @enderror">
                                    <option value="">{{ __("Select bank") }}</option>
                                    <option value="telebirr" @selected(old('payment_bank') === 'telebirr')>Telebirr</option>
                                    <option value="cbe" @selected(old('payment_bank') === 'cbe')>CBE</option>
                                    <option value="boa" @selected(old('payment_bank') === 'boa')>BOA</option>
                                </select>
                                @error('payment_bank')
                                <span class="text-danger" role="alert">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Transaction number") }}</label>
                                <input type="text" name="bank_transaction_number" id="bank_transaction_number" maxlength="50"
                                    class="form-control @error('bank_transaction_number') is-invalid @enderror"
                                    value="{{ old('bank_transaction_number') }}">
                                @error('bank_transaction_number')
                                <span class="text-danger" role="alert">{{ $message }}</span>
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
$(function () {
    function toggleBankFields() {
        var bank = $('#payment_method').val() === 'bank';
        $('.payment-bank-fields').toggle(bank);
        $('#payment_bank').prop('required', bank);
    }
    $('#payment_method').on('change', toggleBankFields);
    toggleBankFields();
});
</script>
@endsection
