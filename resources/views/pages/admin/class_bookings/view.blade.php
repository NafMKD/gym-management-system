@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Booking'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Booking') }} #{{ $classBooking->id }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.class_bookings.list') }}">{{ __('Bookings') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('View') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Details') }}" footer>
            <dl class="row">
                <dt class="col-sm-3">{{ __('Session') }}</dt>
                <dd class="col-sm-9">
                    <a href="{{ route('admin.class_schedules.view', $classBooking->schedule_id) }}">
                        {{ $classBooking->schedule?->gymClass?->name }} — {{ $classBooking->schedule?->starts_at?->format('d/m/Y H:i') }}
                    </a>
                </dd>
                <dt class="col-sm-3">{{ __('Member') }}</dt>
                <dd class="col-sm-9">
                    <a href="{{ route('admin.memberships.view', $classBooking->membership_id) }}">{{ $classBooking->membership?->user?->getName() }}</a>
                </dd>
                <dt class="col-sm-3">{{ __('Status') }}</dt>
                <dd class="col-sm-9">{{ ucfirst($classBooking->status) }}</dd>
                <dt class="col-sm-3">{{ __('Booked by') }}</dt>
                <dd class="col-sm-9">{{ $classBooking->bookedBy?->getName() ?? '—' }}</dd>
            </dl>
            <x-slot:footer>
                <a href="{{ route('admin.class_bookings.list') }}" class="btn btn-default">{{ __('Back') }}</a>
                @if($classBooking->status !== 'cancelled')
                <form action="{{ route('admin.class_bookings.cancel', $classBooking) }}" method="post" class="d-inline" onsubmit="return confirm(@json(__('Cancel this booking?')))">
                    @csrf
                    <button type="submit" class="btn btn-warning">{{ __('Cancel booking') }}</button>
                </form>
                @endif
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
