<x-mail::message>
# {{ __('Invoice :num', ['num' => $invoice->invoice_number]) }}

@php
    $isMerchandise = ($invoice->invoice_source ?? 'membership') === 'merchandise';
    $helloName = $isMerchandise
        ? ($invoice->customer?->first_name ?? __('Customer'))
        : ($invoice->membership?->user?->first_name ?? __('Member'));
@endphp

{{ __('Hello :name,', ['name' => $helloName]) }}

@if($isMerchandise)
{{ __('Thank you for your purchase. Your merchandise invoice is summarized below.') }}
@else
{{ __('Please find your membership invoice details below.') }}
@endif

**{{ __('Amount') }}:** {{ __('Birr') }} {{ number_format($invoice->amount, 2) }}

**{{ __('Due date') }}:** {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}

**{{ __('Status') }}:** {{ ucfirst($invoice->status) }}

{{ __('Thank you') }},<br>
{{ config('app.name') }}
</x-mail::message>
