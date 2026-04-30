@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Payments | Print'])
    <style>
        @media print {
            .main-sidebar,
            .main-header,
            .content-header,
            .main-footer,
            .no-print {
                display: none !important;
            }

            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header no-print">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $reportTitle }}</h1>
            </div>
            <div class="col-sm-6 text-sm-right">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> {{ __('Print') }}
                </button>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-body">
                <h2 class="h4 mb-1">{{ $reportTitle }}</h2>
                <p class="text-muted mb-3">{{ $reportSubtitle }}</p>
                <div class="text-muted mb-3">
                    <div>{{ __('Generated at') }}: <strong>{{ $reportGeneratedAt->format('d/m/Y H:i') }}</strong></div>
                    <div>{{ __('Filters') }}: <strong>{{ $filterSummary }}</strong></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3 col-6"><strong>{{ __('Net Revenue') }}:</strong> {{ __('Birr') }} {{ number_format($totals['totalRevenue'], 2) }}</div>
                    <div class="col-md-3 col-6"><strong>{{ __('Transactions') }}:</strong> {{ number_format($totals['totalTransactions']) }}</div>
                    <div class="col-md-3 col-6"><strong>{{ __('Payments') }}:</strong> {{ number_format($totals['paymentTransactions']) }}</div>
                    <div class="col-md-3 col-6"><strong>{{ __('Refunds') }}:</strong> {{ number_format($totals['refundTransactions']) }}</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Payment ID') }}</th>
                                <th>{{ __('Customer / Member') }}</th>
                                <th>{{ __('Invoice') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th>{{ __('Bank') }}</th>
                                <th>{{ __('Payment Date') }}</th>
                                <th>{{ __('Created By') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>
                                        {{ ($payment->invoice?->invoice_source ?? 'membership') === 'merchandise'
                                            ? ($payment->invoice?->customer?->getName() ?? 'N/A')
                                            : ($payment->membership?->user?->getName() ?? 'N/A') }}
                                    </td>
                                    <td>{{ $payment->invoice?->invoice_number ?? 'N/A' }}</td>
                                    <td>{{ ucfirst((string) ($payment->invoice?->invoice_source ?? 'membership')) }}</td>
                                    <td>{{ ($payment->payment_type ?? 'payment') === 'refund' ? __('Refund') : __('Payment') }}</td>
                                    <td>{{ number_format((float) $payment->amount, 2) }}</td>
                                    <td>{{ ucfirst((string) $payment->payment_method) }}</td>
                                    <td>{{ $payment->payment_bank ? strtoupper((string) $payment->payment_bank) : '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') }}</td>
                                    <td>{{ $payment->createdBy?->getName() ?? __('Legacy / Unknown') }}</td>
                                    <td>{{ ucfirst((string) $payment->status) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">{{ __('No payments matched the selected filters.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-content>
@endsection
