@extends('pages.admin.inc.app')

@section('header')
@include('layouts.header', ['title' => 'Admin | Home'])
@endsection

@section('content-header')
<x-content class="content-header">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">{{ __('Dashboard') }}</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">{{ __('Home') }}</li>
            </ol>
        </div><!-- /.col -->
    </div><!-- /.row -->
</x-content>
@endsection

@section('content')
<x-content class="content">
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Active memberships') }}</span>
                    <span class="info-box-number">{{ number_format($summary['membership_by_status']['active'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Inactive') }}</span>
                    <span class="info-box-number">{{ number_format($summary['membership_by_status']['inactive'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-user-slash"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Cancelled') }}</span>
                    <span class="info-box-number">{{ number_format($summary['membership_by_status']['cancelled'] ?? 0) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-user-plus"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('New this month') }}</span>
                    <span class="info-box-number">{{ number_format($summary['new_memberships_this_month'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-coins"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Revenue this month') }} ({{ __('completed payments') }})</span>
                    <span class="info-box-number">{{ __('Birr') }} {{ number_format($summary['revenue_this_month'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-teal elevation-1"><i class="fas fa-wallet"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Total revenue (all time)') }}</span>
                    <span class="info-box-number">{{ __('Birr') }} {{ number_format($summary['revenue_all_time'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="info-box mb-3">
                <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-file-invoice"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{{ __('Unpaid invoices') }}</span>
                    <span class="info-box-number">{{ number_format($summary['unpaid_invoices_count'] ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <a href="{{ route('admin.merchandise.checkout') }}" class="btn btn-success btn-sm mr-1 mb-1"><i class="fas fa-cash-register"></i> {{ __('POS / Sell merchandise') }}</a>
            <a href="{{ route('admin.memberships.list') }}" class="btn btn-outline-primary btn-sm mr-1 mb-1">{{ __('Memberships') }}</a>
            <a href="{{ route('admin.invoices.list') }}" class="btn btn-outline-secondary btn-sm mr-1 mb-1">{{ __('Invoices') }}</a>
            <a href="{{ route('admin.class_bookings.list') }}" class="btn btn-outline-info btn-sm mb-1">{{ __('Class bookings') }}</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Memberships by status') }}</h3>
                </div>
                <div class="card-body" style="min-height: 260px;">
                    <canvas id="membershipStatusChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Completed payment revenue by month') }}</h3>
                </div>
                <div class="card-body" style="min-height: 260px;">
                    <canvas id="revenueByMonthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Recent memberships') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.memberships.list') }}" class="btn btn-tool text-primary">{{ __('View all') }}</a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Package') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['recent_memberships'] as $m)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.memberships.view', $m) }}">{{ $m->user?->getName() ?? '—' }}</a>
                                </td>
                                <td>{{ $m->package?->name ? ucwords($m->package->name) : __('Custom') }}</td>
                                <td><span class="badge badge-{{ $m->status === 'active' ? 'success' : ($m->status === 'cancelled' ? 'secondary' : 'warning') }}">{{ ucfirst($m->status) }}</span></td>
                                <td>{{ $m->created_at?->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">{{ __('No records') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Recent completed payments') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payments.list') }}" class="btn btn-tool text-primary">{{ __('View all') }}</a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-striped table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Invoice') }}</th>
                                <th>{{ __('Member') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Date') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($summary['recent_payments'] as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.view', $p->invoice_id) }}">{{ $p->invoice?->invoice_number ?? '—' }}</a>
                                </td>
                                <td>{{ $p->membership?->user?->getName() ?? '—' }}</td>
                                <td>{{ number_format($p->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d/m/Y') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">{{ __('No records') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Export') }}</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.export.memberships_csv') }}" class="btn btn-outline-secondary mr-2 mb-2">
                        <i class="fas fa-file-csv"></i> {{ __('Memberships CSV') }}
                    </a>
                    <a href="{{ route('admin.export.payments_csv') }}" class="btn btn-outline-secondary mb-2">
                        <i class="fas fa-file-csv"></i> {{ __('Payments CSV') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-content>
@endsection

@section('script')
<script>
$(function () {
    var membershipData = @json($summary['membership_status_chart']);
    var revenueByMonth = @json($summary['revenue_by_month']);

    var ctxPie = document.getElementById('membershipStatusChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: membershipData.labels,
            datasets: [{
                data: membershipData.data,
                backgroundColor: ['#28a745', '#ffc107', '#6c757d'],
            }]
        },
        options: {
            legend: { position: 'bottom' },
            maintainAspectRatio: false,
            responsive: true
        }
    });

    var ctxBar = document.getElementById('revenueByMonthChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: revenueByMonth.map(function (r) { return r.label; }),
            datasets: [{
                label: @json(__('Birr')),
                data: revenueByMonth.map(function (r) { return r.total; }),
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            legend: { display: false },
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: { beginAtZero: true }
                }]
            }
        }
    });
});
</script>
@endsection
