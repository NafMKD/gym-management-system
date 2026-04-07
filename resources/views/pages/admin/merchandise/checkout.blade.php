@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Sell merchandise'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Sell merchandise') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Merchandise') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Checkout') }}</h3></div>
            <form method="POST" action="{{ route('admin.merchandise.checkout.store') }}" id="checkoutForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Customer (member)') }}</label> <i class="text-danger">*</i>
                                <select name="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($customers as $u)
                                        <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->getName() }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>{{ __('Payment method') }}</label> <i class="text-danger">*</i>
                                <select name="payment_method" id="payment_method" class="form-control">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="bank" @selected(old('payment_method') === 'bank')>{{ __('Bank') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group checkout-bank-fields" style="display:none;">
                                <label>{{ __('Bank') }}</label> <i class="text-danger">*</i>
                                <select name="payment_bank" class="form-control">
                                    <option value="">{{ __('Select bank') }}</option>
                                    <option value="telebirr" @selected(old('payment_bank') === 'telebirr')>Telebirr</option>
                                    <option value="cbe" @selected(old('payment_bank') === 'cbe')>CBE</option>
                                    <option value="boa" @selected(old('payment_bank') === 'boa')>BOA</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row checkout-bank-fields" style="display:none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Transaction number') }}</label>
                                <input type="text" name="bank_transaction_number" class="form-control" maxlength="50" value="{{ old('bank_transaction_number') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Notes') }}</label>
                        <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('notes') }}">
                    </div>

                    <h5>{{ __('Lines') }}</h5>
                    <table class="table table-bordered" id="linesTable">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th style="width:120px">{{ __('Qty') }}</th>
                                <th style="width:50px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(old('lines', [['product_id' => '', 'quantity' => '']]) as $idx => $line)
                                <tr class="line-row">
                                    <td>
                                        <select name="lines[{{ $idx }}][product_id]" class="form-control product-select">
                                            <option value="">{{ __('Select product') }}</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}" @selected(($line['product_id'] ?? '') == $p->id)>{{ $p->name }} ({{ __('Stock') }}: {{ $p->stock_quantity }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="lines[{{ $idx }}][quantity]" class="form-control" min="1" value="{{ $line['quantity'] ?? '' }}" placeholder="1">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger line-remove" title="{{ __('Remove') }}">&times;</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="addLine">{{ __('Add line') }}</button>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary loading-button">{{ __('Complete sale') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    var lineIndex = $('#linesTable tbody tr').length;
    function toggleBank() {
        var bank = $('#payment_method').val() === 'bank';
        $('.checkout-bank-fields').toggle(bank);
        $('.checkout-bank-fields select[name=payment_bank]').prop('required', bank);
    }
    $('#payment_method').on('change', toggleBank);
    toggleBank();

    $('#addLine').on('click', function () {
        var $first = $('#linesTable tbody tr:first');
        var $row = $first.clone();
        $row.find('select, input').each(function () {
            var n = $(this).attr('name');
            if (n) {
                $(this).attr('name', n.replace(/lines\[\d+\]/, 'lines[' + lineIndex + ']'));
            }
            $(this).val('');
        });
        lineIndex++;
        $('#linesTable tbody').append($row);
    });
    $(document).on('click', '.line-remove', function () {
        if ($('#linesTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });
});
</script>
@endsection
