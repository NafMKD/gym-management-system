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
                    <a href="{{ route('admin.class_schedules.view', $classBooking->class_schedule_id) }}">
                        {{ $classBooking->schedule?->gymClass?->name }} — {{ $classBooking->schedule?->starts_at?->format('d/m/Y H:i') }}
                    </a>
                </dd>
                <dt class="col-sm-3">{{ __('Trainer') }}</dt>
                <dd class="col-sm-9">{{ $classBooking->schedule?->trainer?->getName() ?? '—' }}</dd>
                <dt class="col-sm-3">{{ __('Member') }}</dt>
                <dd class="col-sm-9">
                    <a href="{{ route('admin.memberships.view', $classBooking->membership_id) }}">{{ $classBooking->membership?->user?->getName() }}</a>
                </dd>
                <dt class="col-sm-3">{{ __('Status') }}</dt>
                <dd class="col-sm-9">{{ ucfirst($classBooking->status) }}</dd>
                <dt class="col-sm-3">{{ __('Booked by') }}</dt>
                <dd class="col-sm-9">{{ $classBooking->bookedBy?->getName() ?? '—' }}</dd>
                @if($classBooking->trainerCommissionEntry)
                <dt class="col-sm-3">{{ __('Session commission') }}</dt>
                <dd class="col-sm-9">{{ __('Birr') }} {{ number_format((float) $classBooking->trainerCommissionEntry->amount, 2) }}
                    <span class="badge badge-light">{{ __('Recorded') }}</span>
                </dd>
                @endif
            </dl>
            <x-slot:footer>
                <a href="{{ route('admin.class_bookings.list') }}" class="btn btn-default">{{ __('Back') }}</a>
                @if($classBooking->status !== 'cancelled' && $classBooking->status !== 'attended')
                <form action="{{ route('admin.class_bookings.mark_attended', $classBooking) }}" method="post" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm(@json(__('Mark this member as attended for this session?')))">{{ __('Mark attended') }}</button>
                </form>
                @endif
                @if($classBooking->status !== 'cancelled')
                <form action="{{ route('admin.class_bookings.cancel', $classBooking) }}" method="post" class="d-inline" onsubmit="return confirm(@json(__('Cancel this booking?')))">
                    @csrf
                    <button type="submit" class="btn btn-warning">{{ __('Cancel booking') }}</button>
                </form>
                @endif
            </x-slot:footer>
        </x-card>

        <div class="card card-default mt-3">
            <div class="card-header"><h3 class="card-title">{{ __('Session feedback') }} ({{ __('optional') }})</h3></div>
            <form method="POST" action="{{ route('admin.class_bookings.feedback', $classBooking) }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Rating') }} (1–5)</label>
                                <select name="rating" class="form-control">
                                    <option value="">{{ __('—') }}</option>
                                    @for($r = 1; $r <= 5; $r++)
                                        <option value="{{ $r }}" @selected(old('rating', $classBooking->trainerSessionFeedback?->rating) == $r)>{{ $r }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __('Comment') }}</label>
                        <textarea name="comment" class="form-control" rows="3" maxlength="2000">{{ old('comment', $classBooking->trainerSessionFeedback?->comment) }}</textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">{{ __('Save feedback') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection
