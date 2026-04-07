{{-- Shared markup for HTML invoice view, PDF, and email bodies --}}
@php
    $logoPath = public_path('assets/dist/img/logo.JPG');
    $logoSrc = (!empty($forPdf) && is_file($logoPath))
        ? 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($logoPath))
        : asset('assets/dist/img/logo.JPG');
    $isMerchandise = ($invoice->invoice_source ?? 'membership') === 'merchandise';
    $billTo = $isMerchandise ? $invoice->customer : $invoice->membership?->user;
@endphp
<div class="invoice p-3 mb-3">
    <div class="row">
        <div class="col-12">
            <h4>
                <img src="{{ $logoSrc }}" class="img-circle img-sm mr-2" alt="Logo"> MyFitness
                <small class="float-right">Date: {{ \Carbon\Carbon::parse($invoice->issued_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}</small>
            </h4>
        </div>
    </div>
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            From
            <address>
                <strong>MyFitness GYM</strong><br>
                Jimma, Merkato<br>
                Tsinat Building, 4<sup>th</sup> Flor <br>
                Phone: (251) 917-55-3839<br>
                Email: myfitness743@gmail.com
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            To
            <address>
                @if($billTo)
                    <strong>{{ $billTo->getName() }}</strong><br>
                    @php $phone = (string) $billTo->phone; @endphp
                    Phone: (251) {{ strlen($phone) >= 9 ? substr($phone, 0, 3) . '-' . substr($phone, 3, 2) . '-' . substr($phone, 5) : $phone }}<br>
                @elseif($isMerchandise)
                    <strong>{{ __('Walk-in') }}</strong><br>
                    <span class="text-muted">{{ __('No member linked') }}</span>
                @else
                    <strong>—</strong>
                @endif
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Invoice:</b> <em>{{ $invoice->invoice_number }}</em><br>
            <br>
            <b>Payment Due:</b> {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}<br>
            @if($isMerchandise)
                <b>{{ __('Type') }}:</b> {{ __('Merchandise') }}<br>
            @else
                <b>Membership ID:</b> {{ $invoice->membership?->id ?? '—' }}
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
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
                            <th>Full Name</th>
                            <th>Package</th>
                            <th>Description</th>
                            <th>Subtotal</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if($isMerchandise)
                        @foreach($invoice->merchandiseSaleLines as $i => $line)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $line->product?->name }}</td>
                                <td>{{ $line->product?->sku ?? '—' }}</td>
                                <td>{{ $line->quantity }}</td>
                                <td class="text-right">{{ __('Birr') }} {{ number_format((float) $line->unit_price, 2) }}</td>
                                <td class="text-right">{{ __('Birr') }} {{ number_format((float) $line->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>1</td>
                            <td>{{ $invoice->membership?->user?->getName() }}</td>
                            <td>{{ is_null($invoice->membership?->package?->name) ? __("Custom") : ucwords($invoice->membership->package?->name) }}</td>
                            <td>{{ is_null($invoice->membership?->package?->decription) ? '-' : ucfirst($invoice->membership->package?->decription) }}</td>
                            <td>{{ __('Birr') }} {{ number_format($invoice->amount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <p class="lead">
                Payment Status:
                <span class="badge {{ $invoice->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </p>
        </div>
        <div class="col-6">
            <p class="lead">Amount Due {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>Tax (0%)</th>
                        <td>{{ __('Birr') }} 0.00</td>
                    </tr>
                    <tr>
                        <th>Total:</th>
                        <td>{{ __('Birr') }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
