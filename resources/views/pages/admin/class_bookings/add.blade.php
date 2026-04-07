@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Add booking'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Add booking') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.class_bookings.list') }}">{{ __('Bookings') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Add') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Booking') }}</h3></div>
            <form method="POST" action="{{ route('admin.class_bookings.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Session') }}</label> <i class="text-danger">*</i>
                                <select name="class_schedule_id" class="form-control" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($schedules as $s)
                                        <option value="{{ $s->id }}" @selected(old('class_schedule_id', $selectedScheduleId) == $s->id)>
                                            {{ $s->gymClass?->name }} — {{ $s->starts_at?->format('d/m/Y H:i') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Membership') }}</label> <i class="text-danger">*</i>
                                <select name="membership_id" class="form-control" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($memberships as $m)
                                        <option value="{{ $m->id }}" @selected(old('membership_id') == $m->id)>
                                            #{{ $m->id }} — {{ $m->user?->getName() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Status') }}</label>
                                <select name="status" class="form-control">
                                    <option value="confirmed" @selected(old('status', 'confirmed') === 'confirmed')>{{ __('Confirmed') }}</option>
                                    <option value="pending" @selected(old('status') === 'pending')>{{ __('Pending') }}</option>
                                    <option value="attended" @selected(old('status') === 'attended')>{{ __('Attended') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right loading-button">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection
