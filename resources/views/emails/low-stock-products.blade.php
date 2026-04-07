<x-mail::message>
# {{ __('Low stock alert') }}

{{ __('The following products are at or below their low-stock threshold:') }}

@foreach($products as $p)
- **{{ $p->name }}** — {{ __('Stock') }}: {{ $p->stock_quantity }}, {{ __('threshold') }}: {{ $p->low_stock_threshold }}@if($p->sku) (SKU: {{ $p->sku }})@endif
@endforeach

{{ __('Review inventory in the admin panel under Inventory → Products.') }}

{{ __('Thank you') }},<br>
{{ config('app.name') }}
</x-mail::message>
