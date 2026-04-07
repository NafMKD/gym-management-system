@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Gym class'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ $gymClass->name }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.gym_classes.list') }}">{{ __('Gym classes') }}</a></li>
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
                <dt class="col-sm-3">{{ __('Name') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->name }}</dd>
                <dt class="col-sm-3">{{ __('Description') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->description ?: '—' }}</dd>
                <dt class="col-sm-3">{{ __('Capacity') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->capacity }}</dd>
                <dt class="col-sm-3">{{ __('Duration') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->duration_minutes }} {{ __('min') }}</dd>
                <dt class="col-sm-3">{{ __('Active') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->is_active ? __('Yes') : __('No') }}</dd>
                <dt class="col-sm-3">{{ __('Scheduled sessions') }}</dt>
                <dd class="col-sm-9">{{ $gymClass->schedules_count }}</dd>
            </dl>
            <x-slot:footer>
                <a href="{{ route('admin.gym_classes.list') }}" class="btn btn-default">{{ __('Back') }}</a>
                <a href="{{ route('admin.gym_classes.edit', $gymClass) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                <a href="{{ route('admin.gym_classes.delete', $gymClass) }}" class="btn btn-danger float-right"
                   onclick="return confirm(@json(__('Are you sure?')))">{{ __('Delete') }}</a>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
