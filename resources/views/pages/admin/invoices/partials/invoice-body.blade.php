{{-- Shared markup for HTML invoice view, PDF, and email bodies --}}
@php
    $logoPath = public_path('assets/dist/img/logo.JPG');
    $logoSrc = (!empty($forPdf) && is_file($logoPath))
        ? 'data:image/jpeg;base64,' . base64_encode((string) file_get_contents($logoPath))
        : asset('assets/dist/img/logo.JPG');
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
                <strong>{{ $invoice->membership->user->getName() }}</strong><br>
                Phone: (251) {{ substr((string) $invoice->membership->user->phone, 0, 3) . '-' . substr((string) $invoice->membership->user->phone, 3, 2) . '-' . substr((string) $invoice->membership->user->phone, 5) }}<br>
            </address>
        </div>
        <div class="col-sm-4 invoice-col">
            <b>Invoice:</b> <em>{{ $invoice->invoice_number }}</em><br>
            <br>
            <b>Payment Due:</b> {{ \Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}<br>
            <b>Membership ID:</b> {{ $invoice->membership->id }}
        </div>
    </div>
    <div class="row">
        <div class="col-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Package</th>
                        <th>Description</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>{{ $invoice->membership->user->getName() }}</td>
                        <td>{{ is_null($invoice->membership->package?->name) ? __("Custom") : ucwords($invoice->membership->package?->name) }}</td>
                        <td>{{ is_null($invoice->membership->package?->decription) ? '-' : ucfirst($invoice->membership->package?->decription) }}</td>
                        <td>{{ __('Birr') }} {{ number_format($invoice->amount, 2) }}</td>
                    </tr>
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
