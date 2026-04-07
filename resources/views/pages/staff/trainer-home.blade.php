@extends('layouts.portal')

@section('title')
    @include('layouts.header', ['title' => __('Trainer') . ' | ' . __('Home')])
@endsection

@section('content')
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-8">
                        <h1 class="m-0">{{ __('Trainer dashboard') }}</h1>
                    </div>
                    <div class="col-sm-4 text-md-right">
                        <a href="{{ route('trainer.profile.edit') }}" class="btn btn-outline-primary btn-sm">{{ __('My trainer profile') }}</a>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card card-outline card-success h-100">
                            <div class="card-header"><h3 class="card-title">{{ __('Commission snapshot') }}</h3></div>
                            <div class="card-body">
                                <p class="lead mb-1">{{ __('Birr') }} {{ number_format($commissionSnapshot['month_total'], 2) }}</p>
                                <p class="text-muted mb-0">{{ __('Earned this month') }} ({{ $commissionSnapshot['month_label'] }})</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8 mb-3">
                        <div class="card h-100">
                            <div class="card-header"><h3 class="card-title">{{ __('Upcoming sessions') }}</h3></div>
                            <div class="card-body p-0">
                                @if($upcomingSessions->isEmpty())
                                    <p class="p-3 text-muted mb-0">{{ __('No upcoming sessions scheduled.') }}</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('When') }}</th>
                                                    <th>{{ __('Class') }}</th>
                                                    <th>{{ __('Booked') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($upcomingSessions as $session)
                                                    <tr>
                                                        <td>{{ $session->starts_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td>
                                                        <td>{{ $session->gymClass?->name ?? '—' }}</td>
                                                        <td>{{ $session->bookings->count() }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
