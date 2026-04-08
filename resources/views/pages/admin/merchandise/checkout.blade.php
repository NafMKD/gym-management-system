@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Sell merchandise'])
    <link rel="stylesheet" href="{{ asset('assets/css/desk-shell.css') }}">
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-1 mb-sm-2 align-items-center">
            <div class="col">
                <h1 class="m-0 h4">{{ __('POS') }}</h1>
                <p class="text-muted small mb-0 d-none d-sm-block">{{ __('Sell merchandise') }}</p>
            </div>
            <div class="col-auto d-none d-sm-block">
                <ol class="breadcrumb float-sm-right mb-0 bg-transparent p-0 small">
                    <li class="breadcrumb-item">{{ __('Desk') }}</li>
                    <li class="breadcrumb-item active">{{ __('POS') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="pos-terminal-shell">
            <div class="card card-default pos-checkout pos-compact shadow">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Checkout') }}</h3>
                    <p class="card-text text-muted mb-0 small">{{ __('Walk-in = no linked member.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.merchandise.checkout.store') }}" id="checkoutForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small mb-1">{{ __('Customer') }}</label>
                            <select name="user_id" id="user_id" class="form-control form-control-sm @error('user_id') is-invalid @enderror">
                                <option value="" @selected(old('user_id', '') === '' || old('user_id', '') === null)>{{ __('Walk-in') }}</option>
                                @foreach($customers as $u)
                                    <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>
                                        {{ $u->getName() }} — {{ $u->phone }}@if($u->email) ({{ $u->email }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="text-danger d-block mt-1 small">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small mb-1">{{ __('Payment') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control form-control-sm">
                                <option value="cash">{{ __('Cash') }}</option>
                                <option value="bank" @selected(old('payment_method') === 'bank')>{{ __('Bank') }}</option>
                            </select>
                        </div>
                        <div class="form-row checkout-bank-fields" style="display:none;">
                            <div class="form-group col-12 mb-2">
                                <label class="font-weight-bold small mb-1">{{ __('Bank') }} <span class="text-danger">*</span></label>
                                <select name="payment_bank" class="form-control form-control-sm">
                                    <option value="">{{ __('Select bank') }}</option>
                                    <option value="telebirr" @selected(old('payment_bank') === 'telebirr')>Telebirr</option>
                                    <option value="cbe" @selected(old('payment_bank') === 'cbe')>CBE</option>
                                    <option value="boa" @selected(old('payment_bank') === 'boa')>BOA</option>
                                </select>
                            </div>
                            <div class="form-group col-12 mb-2">
                                <label class="small mb-1">{{ __('Transaction number') }}</label>
                                <input type="text" name="bank_transaction_number" class="form-control form-control-sm" maxlength="50" value="{{ old('bank_transaction_number') }}" placeholder="{{ __('Optional') }}">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small mb-1">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control form-control-sm" maxlength="500" value="{{ old('notes') }}" placeholder="{{ __('Optional') }}">
                        </div>

                        @if($products->isNotEmpty())
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small mb-1">{{ __('Quick add') }}</label>
                                <p class="text-muted small mb-1">{{ __('Tap to add a line') }}</p>
                                <div class="d-flex flex-wrap pos-quick-pick">
                                    @foreach($products as $p)
                                        <button type="button" class="btn btn-outline-secondary btn-sm m-0 pos-quick-add"
                                                data-product-id="{{ $p->id }}"
                                                title="{{ $p->name }} — {{ __('Stock') }}: {{ $p->stock_quantity }}">
                                            {{ \Illuminate\Support\Str::limit($p->name, 22) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <h6 class="lines-heading font-weight-bold mb-1">{{ __('Lines') }}</h6>
                        <div class="table-responsive mb-1">
                            <table class="table table-sm table-bordered table-hover mb-0 pos-lines-table" id="linesTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="small pos-line-product-th">{{ __('Product') }}</th>
                                        <th class="small text-center pos-line-qty-th">{{ __('Qty') }}</th>
                                        <th class="p-1 pos-line-remove-th"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(old('lines', [['product_id' => '', 'quantity' => '']]) as $idx => $line)
                                        <tr class="line-row">
                                            <td>
                                                <select name="lines[{{ $idx }}][product_id]" class="form-control form-control-sm product-select">
                                                    <option value="">{{ __('Product') }}</option>
                                                    @foreach($products as $p)
                                                        <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}" @selected(($line['product_id'] ?? '') == $p->id)>{{ $p->name }} ({{ $p->stock_quantity }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="lines[{{ $idx }}][quantity]" class="form-control form-control-sm text-center" min="1" value="{{ $line['quantity'] ?? '' }}" placeholder="1">
                                            </td>
                                            <td class="text-center align-middle p-1">
                                                <button type="button" class="btn btn-outline-danger btn-sm line-remove px-2 py-0" title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">&times;</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mb-0" id="addLine">
                            <i class="fas fa-plus"></i> {{ __('Line') }}
                        </button>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-block loading-button font-weight-bold py-2">
                            <i class="fas fa-check"></i> {{ __('Complete sale') }}
                        </button>
                    </div>
                </form>
            </div>
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
