@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Sell merchandise'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('POS — Sell merchandise') }}</h1></div>
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
        <div class="card card-default pos-checkout">
            <div class="card-header">
                <h3 class="card-title">{{ __('Checkout') }}</h3>
                <p class="card-text text-muted mb-0 small">{{ __('Customer is optional — use Walk-in for counter sales without a member.') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.merchandise.checkout.store') }}" id="checkoutForm">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 mb-3">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">{{ __('Customer (member)') }}</label>
                                <select name="user_id" id="user_id" class="form-control form-control-lg @error('user_id') is-invalid @enderror">
                                    <option value="" @selected(old('user_id', '') === '' || old('user_id', '') === null)>{{ __('Walk-in') }}</option>
                                    @foreach($customers as $u)
                                        <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>
                                            {{ $u->getName() }} — {{ $u->phone }}@if($u->email) ({{ $u->email }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')<span class="text-danger d-block mt-1">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold">{{ __('Payment method') }}</label> <i class="text-danger">*</i>
                                <select name="payment_method" id="payment_method" class="form-control form-control-lg">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="bank" @selected(old('payment_method') === 'bank')>{{ __('Bank') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-3 mb-3">
                            <div class="form-group mb-0 checkout-bank-fields" style="display:none;">
                                <label class="font-weight-bold">{{ __('Bank') }}</label> <i class="text-danger">*</i>
                                <select name="payment_bank" class="form-control form-control-lg">
                                    <option value="">{{ __('Select bank') }}</option>
                                    <option value="telebirr" @selected(old('payment_bank') === 'telebirr')>Telebirr</option>
                                    <option value="cbe" @selected(old('payment_bank') === 'cbe')>CBE</option>
                                    <option value="boa" @selected(old('payment_bank') === 'boa')>BOA</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row checkout-bank-fields" style="display:none;">
                        <div class="col-md-6 mb-3">
                            <div class="form-group mb-0">
                                <label>{{ __('Transaction number') }}</label>
                                <input type="text" name="bank_transaction_number" class="form-control form-control-lg" maxlength="50" value="{{ old('bank_transaction_number') }}" placeholder="{{ __('Optional') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Notes') }}</label>
                        <input type="text" name="notes" class="form-control" maxlength="500" value="{{ old('notes') }}" placeholder="{{ __('Optional') }}">
                    </div>

                    @if($products->isNotEmpty())
                        <div class="form-group">
                            <label class="font-weight-bold">{{ __('Quick add') }}</label>
                            <p class="text-muted small mb-2">{{ __('Tap a product to add a line (or use the table below).') }}</p>
                            <div class="d-flex flex-wrap pos-quick-pick">
                                @foreach($products as $p)
                                    <button type="button" class="btn btn-outline-secondary btn-sm m-1 pos-quick-add py-2 px-3"
                                            data-product-id="{{ $p->id }}"
                                            title="{{ $p->name }} — {{ __('Stock') }}: {{ $p->stock_quantity }}">
                                        {{ \Illuminate\Support\Str::limit($p->name, 28) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <h5 class="mt-3 font-weight-bold">{{ __('Lines') }}</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="linesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th style="min-width:110px">{{ __('Qty') }}</th>
                                    <th style="width:56px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(old('lines', [['product_id' => '', 'quantity' => '']]) as $idx => $line)
                                    <tr class="line-row">
                                        <td>
                                            <select name="lines[{{ $idx }}][product_id]" class="form-control form-control-lg product-select">
                                                <option value="">{{ __('Select product') }}</option>
                                                @foreach($products as $p)
                                                    <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}" @selected(($line['product_id'] ?? '') == $p->id)>{{ $p->name }} ({{ __('Stock') }}: {{ $p->stock_quantity }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="lines[{{ $idx }}][quantity]" class="form-control form-control-lg text-center" min="1" value="{{ $line['quantity'] ?? '' }}" placeholder="1">
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-danger btn-lg line-remove px-3" title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">&times;</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-lg mb-2" id="addLine">
                        <i class="fas fa-plus"></i> {{ __('Add line') }}
                    </button>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-lg btn-block loading-button py-3">
                        <i class="fas fa-check"></i> {{ __('Complete sale') }}
                    </button>
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

    $(document).on('click', '.pos-quick-add', function () {
        var pid = String($(this).data('product-id'));
        var $empty = $('#linesTable tbody tr').filter(function () {
            return !$(this).find('select.product-select').val();
        }).first().find('select.product-select');
        if ($empty.length) {
            $empty.val(pid);
            var $q = $empty.closest('tr').find('input[type=number]');
            if (!$q.val() || parseInt($q.val(), 10) < 1) {
                $q.val(1);
            }
            return;
        }
        $('#addLine').trigger('click');
        var $last = $('#linesTable tbody tr:last');
        $last.find('select.product-select').val(pid);
        $last.find('input[type=number]').val(1);
    });
});
</script>
@endsection
