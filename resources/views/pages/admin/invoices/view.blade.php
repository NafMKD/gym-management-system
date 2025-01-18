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
<section class="content">
    <div class="container-fluid">
        <div class="invoice p-3 mb-3">
            <!-- title row -->
            <div class="row">
                <div class="col-12">
                    <h4>
                        <img src="{{ asset('assets/dist/img/logo.JPG') }}" class="img-circle img-sm mr-2" alt="User Image"> MyFitness
                        <small class="float-right">Date: {{ Carbon\Carbon::parse($invoice->issued_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}</small>
                    </h4>
                </div>
                <!-- /.col -->
            </div>
            <!-- info row -->
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
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    To
                    <address>
                        <strong>{{ $invoice->membership->user->getName() }}</strong><br>
                        Phone: (251) {{ substr($invoice->membership->user->phone, 0, 3) . '-' . substr($invoice->membership->user->phone, 3, 2) . '-' . substr($invoice->membership->user->phone, 5) }}<br>
                    </address>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    <b>Invoice:</b> <em>{{ $invoice->invoice_number }}</em><br>
                    <br>
                    <b>Payment Due:</b> {{ Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}<br>
                    <b>Membership ID:</b> {{ $invoice->membership->id }}
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <!-- Table row -->
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
                                <td>{{ is_null($invoice->membership->package?->name) ? '-' :ucwords($invoice->membership->package?->name)}}</td>
                                <td>{{ is_null($invoice->membership->package?->decription) ? '-' : ucfirst($invoice->membership->package?->decription)}}</td>
                                <td>{{ __('Birr')}} {{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <div class="row">
                <!-- accepted payments column -->
                <div class="col-6">
                    <p class="lead">
                        Payment Status: 
                        <span class="badge {{ $invoice->status == 'paid' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </p>
                </div>
                <!-- /.col -->
                <div class="col-6">
                    <p class="lead">Amount Due {{ Carbon\Carbon::parse($invoice->due_date)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y') }}</p>

                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th>Tax (0%)</th>
                                <td>{{ __('Birr')}} 0.00</td>
                            </tr>
                            <tr>
                                <th>Total:</th>
                                <td>{{ __('Birr')}} {{ number_format($invoice->amount, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <!-- this row will not appear when printing -->
            <div class="row no-print">
                <div class="col-12">
                    <a href="invoice-print.html" rel="noopener" target="_blank" class="btn btn-default"><i class="fas fa-print"></i> Print</a>
                    <button type="button" class="btn btn-success float-right"><i class="far fa-credit-card"></i> Submit Payment </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection