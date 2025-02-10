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
                                <label>{{ __("Membership ID") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="membership_id" type="text"
                                    class="form-control" name="membership_id"
                                    value="{{ $invoice->membership_id }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __("Amount") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="amount" type="number" step="0.01"
                                    class="form-control @error('amount') is-invalid @enderror" name="amount"
                                    value="{{ $invoice->amount - $invoice->payments()->where('status', 'completed')->sum('amount') }}" required autocomplete="amount">
                                @error('amount')
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