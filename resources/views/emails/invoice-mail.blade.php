<x-mail::message>
# {{ __('Invoice :num', ['num' => $invoice->invoice_number]) }}

{{ __('Hello :name,', ['name' => $invoice->membership->user->first_name ?? __('Member')]) }}

{{ __('Please find your membership invoice details below.') }}

**{{ __('Amount') }}:** {{ __('Birr') }} {{ number_format($invoice->amount, 2) }}

**{{ __('Due date') }}:** {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}

**{{ __('Status') }}:** {{ ucfirst($invoice->status) }}

{{ __('Thank you') }},<br>
{{ config('app.name') }}
</x-mail::message>
