@extends('layouts.portal')

@section('title')
    @include('layouts.header', ['title' => $portalTitle . ' | Home'])
@endsection

@section('content')
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('Member dashboard') }}</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card card-outline card-primary h-100">
                            <div class="card-header"><h3 class="card-title">{{ __('Membership') }}</h3></div>
                            <div class="card-body">
                                @if($membership)
                                    <p class="mb-1"><strong>{{ __('Status') }}:</strong> {{ ucfirst($membership->status) }}</p>
                                    <p class="mb-1"><strong>{{ __('Valid until') }}:</strong> {{ $membership->end_date }}</p>
                                    @if($membership->package)
                                        <p class="mb-0 text-muted">{{ $membership->package->name }}</p>
                                    @endif
                                @else
                                    <p class="text-muted mb-0">{{ __('No active membership on file.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <a href="{{ route('member.classes.index') }}" class="btn btn-primary btn-lg btn-block py-3">{{ __('Book a group class') }}</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title">{{ __('Recent class bookings') }}</h3></div>
                    <div class="card-body p-0">
                        @if($recentBookings->isEmpty())
                            <p class="p-3 text-muted mb-0">{{ __('No bookings yet.') }}</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Session') }}</th>
                                            <th>{{ __('When') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentBookings as $b)
                                            <tr>
                                                <td>{{ $b->schedule?->gymClass?->name ?? '—' }}</td>
                                                <td>{{ $b->schedule?->starts_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?? '—' }}</td>
                                                <td>{{ ucfirst((string) $b->status) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
