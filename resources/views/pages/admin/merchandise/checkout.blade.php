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
                <p class="text-muted small mb-0 d-none d-sm-block">{{ __('Fast merchandise checkout') }}</p>
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
                    <p class="card-text text-muted mb-0 small">{{ __('Sale stays on this screen so staff can move straight to the next customer.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.merchandise.checkout.store') }}" id="checkoutForm" autocomplete="off">
                    @csrf
                    <div class="card-body">
                        <div class="form-group mb-2">
                            <label class="font-weight-bold small mb-1">{{ __('Customer') }}</label>
                            <select name="user_id" id="user_id" class="form-control form-control-sm @error('user_id') is-invalid @enderror">
                                <option value="" @selected(old('user_id', '') === '' || old('user_id', '') === null)>{{ __('Walk-in') }}</option>
                                @foreach($customers as $u)
                                    <option value="{{ $u->id }}" @selected((string) old('user_id') === (string) $u->id)>
                                        {{ $u->getName() }} - {{ $u->phone }}@if($u->email) ({{ $u->email }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')<span class="text-danger d-block mt-1 small">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold small mb-1">{{ __('Payment') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" id="payment_method" class="form-control form-control-sm">
                                <option value="cash" @selected(old('payment_method', 'cash') === 'cash')>{{ __('Cash') }}</option>
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

                        <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-3 bg-light small">
                            <div>
                                <span class="text-muted d-block">{{ __('Items') }}</span>
                                <strong id="posItemCount">0</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-muted d-block">{{ __('Total') }}</span>
                                <strong id="posTotal">{{ __('Birr') }} 0.00</strong>
                            </div>
                        </div>

                        @if($products->isNotEmpty())
                            <div class="form-group mb-2">
                                <label class="font-weight-bold small mb-1">{{ __('Quick add') }}</label>
                                <p class="text-muted small mb-1">{{ __('Tap once to add. Tap again to increase quantity.') }}</p>
                                <div class="d-flex flex-wrap pos-quick-pick">
                                    @foreach($products as $p)
                                        <button
                                            type="button"
                                            class="btn btn-outline-secondary btn-sm m-0 pos-quick-add"
                                            data-product-id="{{ $p->id }}"
                                            data-product-name="{{ $p->name }}"
                                            data-price="{{ (float) $p->unit_price }}"
                                            data-stock="{{ $p->stock_quantity }}"
                                            title="{{ $p->name }} - {{ __('Stock') }}: {{ $p->stock_quantity }}"
                                            @disabled($p->stock_quantity < 1)
                                        >
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
                                                        <option
                                                            value="{{ $p->id }}"
                                                            data-name="{{ $p->name }}"
                                                            data-price="{{ (float) $p->unit_price }}"
                                                            data-stock="{{ $p->stock_quantity }}"
                                                            @selected(($line['product_id'] ?? '') == $p->id)
                                                            @disabled($p->stock_quantity < 1)
                                                        >
                                                            {{ $p->name }} ({{ $p->stock_quantity }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" name="lines[{{ $idx }}][quantity]" class="form-control form-control-sm text-center product-quantity" min="1" value="{{ $line['quantity'] ?? '' }}" placeholder="1">
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
                        <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2" id="completeSaleButton">
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
    var $form = $('#checkoutForm');
    var $linesTableBody = $('#linesTable tbody');
    var $paymentMethod = $('#payment_method');
    var $paymentBank = $('.checkout-bank-fields select[name=payment_bank]');
    var $submitButton = $('#completeSaleButton');
    var initialSubmitHtml = $submitButton.html();
    var lineIndex = $linesTableBody.find('tr').length;
    var productOptionsHtml = '';
    var posToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1600,
        timerProgressBar: false,
    });

    function refreshProductOptionsHtml() {
        var $template = $linesTableBody.find('select.product-select:first').clone();
        $template.find('option').prop('selected', false);
        $template.find('option:first').prop('selected', true);
        productOptionsHtml = $template.html();
    }

    function toggleBank() {
        var bank = $paymentMethod.val() === 'bank';
        $('.checkout-bank-fields').toggle(bank);
        $paymentBank.prop('required', bank);
    }

    function buildRow(index) {
        return $(
            '<tr class="line-row">' +
                '<td>' +
                    '<select name="lines[' + index + '][product_id]" class="form-control form-control-sm product-select">' +
                        productOptionsHtml +
                    '</select>' +
                '</td>' +
                '<td>' +
                    '<input type="number" name="lines[' + index + '][quantity]" class="form-control form-control-sm text-center product-quantity" min="1" placeholder="1">' +
                '</td>' +
                '<td class="text-center align-middle p-1">' +
                    '<button type="button" class="btn btn-outline-danger btn-sm line-remove px-2 py-0" title="{{ __('Remove') }}" aria-label="{{ __('Remove') }}">&times;</button>' +
                '</td>' +
            '</tr>'
        );
    }

    function addLine(prefillProductId, prefillQuantity) {
        var $row = buildRow(lineIndex);
        lineIndex += 1;
        $linesTableBody.append($row);

        if (prefillProductId) {
            $row.find('select.product-select').val(String(prefillProductId));
        }

        if (prefillQuantity) {
            $row.find('input.product-quantity').val(prefillQuantity);
        }

        return $row;
    }

    function ensureSingleEmptyLine() {
        $linesTableBody.empty().append(buildRow(0));
        lineIndex = 1;
    }

    function focusFirstProduct() {
        window.setTimeout(function () {
            $linesTableBody.find('select.product-select:first').trigger('focus');
        }, 40);
    }

    function setSubmitting(isSubmitting) {
        if (isSubmitting) {
            $submitButton.prop('disabled', true);
            $submitButton.html('<i class="fas fa-spinner fa-spin"></i> {{ __('Processing...') }}');
            return;
        }

        $submitButton.prop('disabled', false);
        $submitButton.html(initialSubmitHtml);
    }

    function getLinePrice($row) {
        var $selected = $row.find('select.product-select option:selected');
        return parseFloat($selected.data('price') || 0);
    }

    function updateSummary() {
        var itemCount = 0;
        var total = 0;

        $linesTableBody.find('tr').each(function () {
            var $row = $(this);
            var productId = $row.find('select.product-select').val();
            var quantity = parseInt($row.find('input.product-quantity').val(), 10) || 0;

            if (!productId || quantity < 1) {
                return;
            }

            itemCount += quantity;
            total += getLinePrice($row) * quantity;
        });

        $('#posItemCount').text(itemCount);
        $('#posTotal').text('{{ __('Birr') }} ' + total.toFixed(2));
    }

    function syncProductStocks(products) {
        $.each(products || [], function (_, product) {
            var optionSelector = 'select.product-select option[value="' + product.id + '"]';
            var quickSelector = '.pos-quick-add[data-product-id="' + product.id + '"]';

            $(optionSelector).each(function () {
                var $option = $(this);
                var productName = $option.data('name') || product.name;
                $option.attr('data-stock', product.stock_quantity);
                $option.prop('disabled', product.stock_quantity < 1);
                $option.text(productName + ' (' + product.stock_quantity + ')');
            });

            $(quickSelector).each(function () {
                var $button = $(this);
                $button.attr('data-stock', product.stock_quantity);
                $button.prop('disabled', product.stock_quantity < 1);
                $button.attr('title', product.name + ' - {{ __('Stock') }}: ' + product.stock_quantity);
                $button.toggleClass('btn-outline-secondary', product.stock_quantity > 0);
                $button.toggleClass('btn-outline-danger', product.stock_quantity < 1);
            });
        });

        refreshProductOptionsHtml();
    }

    function resetAfterSuccess() {
        var paymentMethod = $paymentMethod.val();
        var paymentBank = $paymentBank.val();

        $form[0].reset();
        ensureSingleEmptyLine();

        $('#user_id').val('');
        $('input[name=notes]').val('');
        $('input[name=bank_transaction_number]').val('');
        $paymentMethod.val(paymentMethod || 'cash');
        $paymentBank.val(paymentMethod === 'bank' ? paymentBank : '');

        toggleBank();
        updateSummary();
        focusFirstProduct();
    }

    function firstErrorMessage(xhr) {
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                return xhr.responseJSON.message;
            }

            if (xhr.responseJSON.errors) {
                var firstKey = Object.keys(xhr.responseJSON.errors)[0];
                if (firstKey && xhr.responseJSON.errors[firstKey] && xhr.responseJSON.errors[firstKey][0]) {
                    return xhr.responseJSON.errors[firstKey][0];
                }
            }
        }

        return '{{ __('Sale could not be completed.') }}';
    }

    function fillEmptyOrNewLine(productId, quantity) {
        var $emptySelect = $linesTableBody.find('select.product-select').filter(function () {
            return !$(this).val();
        }).first();

        if ($emptySelect.length) {
            $emptySelect.val(String(productId));
            $emptySelect.closest('tr').find('input.product-quantity').val(quantity);
            return $emptySelect.closest('tr');
        }

        return addLine(productId, quantity);
    }

    $paymentMethod.on('change', toggleBank);

    $('#addLine').on('click', function () {
        addLine('', '');
        updateSummary();
    });

    $(document).on('click', '.line-remove', function () {
        if ($linesTableBody.find('tr').length > 1) {
            $(this).closest('tr').remove();
        } else {
            $(this).closest('tr').find('select.product-select, input.product-quantity').val('');
        }

        updateSummary();
    });

    $(document).on('click', '.pos-quick-add', function () {
        var productId = String($(this).data('product-id'));
        var $existing = $linesTableBody.find('select.product-select').filter(function () {
            return $(this).val() === productId;
        }).first();

        if ($existing.length) {
            var $qty = $existing.closest('tr').find('input.product-quantity');
            $qty.val((parseInt($qty.val(), 10) || 0) + 1).trigger('focus');
            updateSummary();
            return;
        }

        var $row = fillEmptyOrNewLine(productId, 1);
        $row.find('input.product-quantity').trigger('focus');
        updateSummary();
    });

    $(document).on('change input', 'select.product-select, input.product-quantity', function () {
        updateSummary();
    });

    $form.on('submit', function (event) {
        event.preventDefault();
        setSubmitting(true);

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            },
            success: function (response) {
                syncProductStocks(response.products || []);
                resetAfterSuccess();
                posToast.fire({
                    icon: 'success',
                    title: response.message || '{{ __('Sale recorded.') }}'
                });
            },
            error: function (xhr) {
                posToast.fire({
                    icon: 'error',
                    title: firstErrorMessage(xhr)
                });
            },
            complete: function () {
                setSubmitting(false);
            }
        });
    });

    toggleBank();
    refreshProductOptionsHtml();
    updateSummary();
    focusFirstProduct();
});
</script>
@endsection
