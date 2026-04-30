@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Invoices | Print'])
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
                <h1 class="m-0">{{ __('Invoices Report Print View') }}</h1>
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
                <h2 class="h4 mb-3">{{ __('Invoices Report') }}</h2>
                <div class="text-muted mb-3">
                    <div>{{ __('Generated at') }}: <strong>{{ $reportGeneratedAt->format('d/m/Y H:i') }}</strong></div>
                    <div>{{ __('Filters') }}: <strong>{{ $filterSummary }}</strong></div>
                    <div>{{ __('Total invoices') }}: <strong>{{ number_format($invoices->count()) }}</strong></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice Number') }}</th>
                                <th>{{ __('Customer / Member') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th>{{ __('Package / Context') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Created By') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>
                                        {{ ($invoice->invoice_source ?? 'membership') === 'merchandise'
                                            ? ($invoice->customer?->getName() ?? __('Walk-in'))
                                            : ($invoice->membership?->user?->getName() ?? 'N/A') }}
                                    </td>
                                    <td>{{ ucfirst((string) ($invoice->invoice_source ?? 'membership')) }}</td>
                                    <td>
                                        {{ ($invoice->invoice_source ?? 'membership') === 'merchandise'
                                            ? __('Merchandise')
                                            : ($invoice->membership?->package?->name ?? __('Custom')) }}
                                    </td>
                                    <td>{{ number_format((float) $invoice->amount, 2) }}</td>
                                    <td>{{ ucfirst((string) $invoice->status) }}</td>
                                    <td>{{ $invoice->issued_date ? \Carbon\Carbon::parse($invoice->issued_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') : '-' }}</td>
                                    <td>{{ $invoice->createdBy?->getName() ?? __('Legacy / Unknown') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">{{ __('No invoices matched the selected filters.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-content>
@endsection
