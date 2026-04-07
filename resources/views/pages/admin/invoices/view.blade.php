@extends('pages.admin.inc.app')

@section('header')
@include('layouts.header', ['title' => 'Admin | Invoice'])
@endsection

@section('content-header')
<x-content class="content-header">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">{{ __("Invoice") }}</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item">{{ __("Home") }}</li>
                <li class="breadcrumb-item">{{ __("Invoice") }}</li>
                <li class="breadcrumb-item"><a href="{{ route('admin.invoices.list') }}">{{ __("Invoice List") }}</a>
                </li>
                <li class="breadcrumb-item active">{{ __("Detail") }}</li>
            </ol>
        </div><!-- /.col -->
    </div><!-- /.row -->
</x-content>
@endsection

@section('content')
@php
    $netPaid = (float) $invoice->payments()->where('status', 'completed')->sum('amount');
    $remaining = max(0, (float) $invoice->amount - $netPaid);
@endphp
<section class="content">
    <div class="container-fluid">
        @include('pages.admin.invoices.partials.invoice-body', ['invoice' => $invoice])

        @if($invoice->payments->isNotEmpty())
        <div class="row no-print mb-3">
            <div class="col-12">
                <h5>{{ __("Payment history") }}</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>{{ __("Date") }}</th>
                                <th>{{ __("Type") }}</th>
                                <th>{{ __("Method") }}</th>
                                <th class="text-right">{{ __("Amount") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments->sortByDesc('payment_date') as $p)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y H:i') }}</td>
                                <td>{{ ($p->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment') }}</td>
                                <td>{{ ucfirst($p->payment_method) }}</td>
                                <td class="text-right">{{ number_format($p->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">{{ __("Net paid") }}</th>
                                <th class="text-right">{{ number_format($netPaid, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="3">{{ __("Remaining") }}</th>
                                <th class="text-right">{{ number_format($remaining, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <div class="row no-print">
            <div class="col-12">
                <form action="{{ route('admin.invoices.send.email', $invoice) }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary"><i class="fas fa-envelope"></i> {{ __("Email invoice") }}</button>
                </form>
                <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-outline-secondary" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> {{ __("Download PDF") }}</a>

                @if ($invoice->status == 'unpaid' && $remaining > 0)
                    <a href="{{ route('admin.payments.add', $invoice->id) }}" class="btn btn-success float-right"><i class="far fa-credit-card"></i> {{ __("Submit Payment") }}</a>
                @endif

                @if ($netPaid > 0)
                    <button type="button" class="btn btn-warning float-right mr-2" data-toggle="modal" data-target="#refundModal"><i class="fas fa-undo"></i> {{ __("Record refund") }}</button>
                @endif
            </div>
        </div>
    </div>
</section>

<div class="modal fade no-print" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.payments.refund') }}" method="post">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">{{ __("Record refund") }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ __("Maximum refund") }}: <strong>{{ number_format($netPaid, 2) }}</strong></p>
                    <div class="form-group">
                        <label for="refund_amount">{{ __("Amount") }}</label>
                        <input type="number" step="0.01" min="0.01" max="{{ $netPaid }}" class="form-control" name="amount" id="refund_amount" required>
                    </div>
                    <div class="form-group">
                        <label for="refund_method">{{ __("Payment method") }}</label>
                        <select name="payment_method" id="refund_method" class="form-control refund-method">
                            <option value="cash">{{ __("Cash") }}</option>
                            <option value="bank">{{ __("Bank") }}</option>
                        </select>
                    </div>
                    <div class="form-group refund-bank-fields" style="display:none;">
                        <label for="refund_payment_bank">{{ __("Bank") }}</label>
                        <select name="payment_bank" id="refund_payment_bank" class="form-control">
                            <option value="telebirr">Telebirr</option>
                            <option value="cbe">CBE</option>
                            <option value="boa">BOA</option>
                        </select>
                    </div>
                    <div class="form-group refund-bank-fields" style="display:none;">
                        <label for="refund_bank_transaction_number">{{ __("Transaction number") }}</label>
                        <input type="text" name="bank_transaction_number" id="refund_bank_transaction_number" class="form-control" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="refund_notes">{{ __("Notes") }} ({{ __("optional") }})</label>
                        <textarea name="notes" id="refund_notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __("Cancel") }}</button>
                    <button type="submit" class="btn btn-warning">{{ __("Record refund") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    function toggleRefundBank() {
        var bank = $('.refund-method').val() === 'bank';
        $('.refund-bank-fields').toggle(bank);
        $('#refund_payment_bank').prop('required', bank);
    }
    $('.refund-method').on('change', toggleRefundBank);
    toggleRefundBank();
});
</script>
@endsection
