<x-mail::message>
# {{ __('Membership renewal reminder') }}

{{ __('Hello :name,', ['name' => $membership->user?->first_name ?? __('Member')]) }}

{{ __('Your current membership is scheduled to end on :date.', ['date' => \Carbon\Carbon::parse($membership->end_date)->format('d/m/Y')]) }}

{{ __('Please visit the gym or contact reception to renew.') }}

{{ __('Thank you') }},<br>
{{ config('app.name') }}
</x-mail::message>
