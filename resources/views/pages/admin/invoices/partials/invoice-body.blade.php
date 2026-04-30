{{-- Shared markup for HTML invoice view, PDF, and email bodies --}}
@php
    $logoPath = public_path('assets/dist/img/logo.JPG');
    $logoSrc = (!empty($forPdf) && is_file($logoPath))
        ? 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($logoPath))
        : asset('assets/dist/img/logo.JPG');
    $isMerchandise = ($invoice->invoice_source ?? 'membership') === 'merchandise';
    $billTo = $isMerchandise ? $invoice->customer : $invoice->membership?->user;
    $billToPhone = (string) ($billTo?->phone ?? '');
    $formattedBillToPhone = strlen($billToPhone) >= 9
        ? '(251) ' . substr($billToPhone, 0, 3) . '-' . substr($billToPhone, 3, 2) . '-' . substr($billToPhone, 5)
        : $billToPhone;
@endphp

<div class="invoice invoice-document">
    <table class="invoice-head" role="presentation">
        <tr>
            <td>
                <div class="invoice-brand">
                    <img src="{{ $logoSrc }}" class="invoice-logo" alt="Logo">
                    <span class="invoice-brand-name">{{ config('gym.invoice_brand', 'MyFitness') }}</span>
                </div>
            </td>
            <td class="invoice-date">
                <strong>{{ __('Date') }}:</strong>
                {{ \Carbon\Carbon::parse($invoice->issued_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <table class="invoice-info" role="presentation">
        <tr>
            <td>
                <span class="invoice-label">{{ __('From') }}</span>
                <address class="invoice-address">
                    <strong>{{ config('gym.invoice_name', 'My Fitness GYM') }}</strong><br>
                    {{ config('gym.address.area', 'Jimma, Merkato') }}<br>
                    {{ config('gym.address.building', 'Tsinat Building, 4th Floor') }}<br>
                    {{ __('Phone') }}: {{ config('gym.contact.phone_display', '(251) 917-55-3839') }}<br>
                    {{ __('Email') }}: {{ config('gym.contact.email', 'myfitness743@gmail.com') }}
                </address>
            </td>
            <td>
                <span class="invoice-label">{{ __('To') }}</span>
                <address class="invoice-address">
                    @if($billTo)
                        <strong>{{ $billTo->getName() }}</strong><br>
                        @if($formattedBillToPhone !== '')
                            {{ __('Phone') }}: {{ $formattedBillToPhone }}<br>
                        @endif
                    @elseif($isMerchandise)
                        <strong>{{ __('Walk-in') }}</strong><br>
                        <span class="text-muted">{{ __('No member linked') }}</span>
                    @else
                        <strong>&mdash;</strong>
                    @endif
                </address>
            </td>
            <td>
                <span class="invoice-label">{{ __('Invoice Details') }}</span>
                <p class="invoice-detail-line">
                    <strong>{{ __('Invoice') }}:</strong>
                    <em>{{ $invoice->invoice_number }}</em>
                </p>
                <p class="invoice-detail-line">
                    <strong>{{ __('Payment Due') }}:</strong>
                    {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}
                </p>
                @if($isMerchandise)
                    <p class="invoice-detail-line">
                        <strong>{{ __('Type') }}:</strong>
                        {{ __('Merchandise') }}
                    </p>
                @else
                    <p class="invoice-detail-line">
                        <strong>{{ __('Membership ID') }}:</strong>
                        @if($invoice->membership?->id)
                            {{ $invoice->membership->id }}
                        @else
                            &mdash;
                        @endif
                    </p>
                @endif
                <p class="invoice-detail-line">
                    <strong>{{ __('Created By') }}:</strong>
                    {{ $invoice->createdBy?->getName() ?? __('Legacy / Unknown') }}
                </p>
            </td>
        </tr>
    </table>

    <table class="invoice-items">
        <thead>
            <tr>
                <th>#</th>
                @if($isMerchandise)
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th class="text-right">{{ __('Unit price') }}</th>
                    <th class="text-right">{{ __('Line total') }}</th>
                @else
                    <th>{{ __('Full Name') }}</th>
                    <th>{{ __('Package') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th class="text-right">{{ __('Subtotal') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if($isMerchandise)
                @foreach($invoice->merchandiseSaleLines as $i => $line)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $line->product?->name }}</td>
                        <td>{{ $line->product?->sku ?? 'N/A' }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td class="text-right invoice-money">{{ __('Birr') }} {{ number_format((float) $line->unit_price, 2) }}</td>
                        <td class="text-right invoice-money">{{ __('Birr') }} {{ number_format((float) $line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td>1</td>
                    <td>{{ $invoice->membership?->user?->getName() }}</td>
                    <td>{{ is_null($invoice->membership?->package?->name) ? __('Custom') : ucwords((string) $invoice->membership?->package?->name) }}</td>
                    <td>{{ is_null($invoice->membership?->package?->decription) ? '-' : ucfirst((string) $invoice->membership?->package?->decription) }}</td>
                    <td class="text-right invoice-money">{{ __('Birr') }} {{ number_format((float) $invoice->amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="invoice-footer" role="presentation">
        <tr>
            <td>
                <p class="invoice-lead">
                    {{ __('Payment Status') }}:
                    <span class="invoice-status-badge {{ $invoice->status === 'paid' ? 'is-paid' : 'is-unpaid' }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </p>
            </td>
            <td class="invoice-summary-wrap">
                <p class="invoice-summary-title">
                    {{ __('Amount Due') }}
                    {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}
                </p>
                <table class="invoice-summary">
                    <tr>
                        <th>{{ __('Tax (0%)') }}</th>
                        <td class="text-right invoice-money">{{ __('Birr') }} 0.00</td>
                    </tr>
                    <tr>
                        <th>{{ __('Total') }}:</th>
                        <td class="text-right invoice-money">{{ __('Birr') }} {{ number_format((float) $invoice->amount, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
