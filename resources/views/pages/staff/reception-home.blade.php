@extends('layouts.reception')

@section('title')
    @include('layouts.header', ['title' => __('Front desk') . ' | ' . __('Home')])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Front desk') }}</h1>
                    <p class="text-muted mb-0">{{ __('Today’s class bookings and quick links') }}</p>
                </div>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-6 col-md-4 col-lg mb-2">
                    <a href="{{ route('admin.users.add') }}" class="btn btn-primary btn-lg btn-block py-3">{{ __('Add user') }}</a>
                </div>
                <div class="col-6 col-md-4 col-lg mb-2">
                    <a href="{{ route('admin.memberships.list') }}" class="btn btn-outline-primary btn-lg btn-block py-3">{{ __('Memberships') }}</a>
                </div>
                <div class="col-6 col-md-4 col-lg mb-2">
                    <a href="{{ route('admin.merchandise.checkout') }}" class="btn btn-outline-success btn-lg btn-block py-3">{{ __('Sell merchandise') }}</a>
                </div>
                <div class="col-6 col-md-4 col-lg mb-2">
                    <a href="{{ route('admin.merchandise.history') }}" class="btn btn-outline-dark btn-lg btn-block py-3">{{ __('Sales history') }}</a>
                </div>
                <div class="col-6 col-md-4 col-lg mb-2">
                    <a href="{{ route('admin.attendance.scan') }}" class="btn btn-outline-secondary btn-lg btn-block py-3">{{ __('Scan attendance') }}</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">{{ __('Today’s bookings') }}</h3>
                    <a href="{{ route('admin.class_bookings.list') }}" class="btn btn-sm btn-default">{{ __('All bookings') }}</a>
                </div>
                <div class="card-body p-0">
                    @if($todaysBookings->isEmpty())
                        <p class="p-3 text-muted mb-0">{{ __('No bookings for sessions starting today.') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('Time') }}</th>
                                        <th>{{ __('Class') }}</th>
                                        <th>{{ __('Member') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todaysBookings as $booking)
                                        <tr>
                                            <td>{{ $booking->schedule?->starts_at?->timezone(config('app.timezone'))->format('H:i') }}</td>
                                            <td>{{ $booking->schedule?->gymClass?->name ?? '—' }}</td>
                                            <td>{{ $booking->bookedBy?->getName() ?? $booking->membership?->user?->getName() ?? '—' }}</td>
                                            <td class="text-right">
                                                <a href="{{ route('admin.class_bookings.view', $booking) }}" class="btn btn-xs btn-info">{{ __('View') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </x-content>
@endsection
