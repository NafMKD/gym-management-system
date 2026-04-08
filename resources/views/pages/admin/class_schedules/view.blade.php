@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Session'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ $classSchedule->gymClass?->name }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.class_schedules.list') }}">{{ __('Schedules') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('View') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Session') }}" footer>
            <dl class="row">
                <dt class="col-sm-3">{{ __('Class') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->gymClass?->name }}</dd>
                <dt class="col-sm-3">{{ __('Trainer') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->trainer?->getName() }}</dd>
                <dt class="col-sm-3">{{ __('Starts') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->starts_at?->format('d/m/Y H:i') }}</dd>
                <dt class="col-sm-3">{{ __('Ends') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->ends_at?->format('d/m/Y H:i') }}</dd>
                <dt class="col-sm-3">{{ __('Capacity') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->effectiveCapacity() }} ({{ __('booked') }}: {{ $classSchedule->activeBookingsCount() }})</dd>
                <dt class="col-sm-3">{{ __('Notes') }}</dt>
                <dd class="col-sm-9">{{ $classSchedule->notes ?: '—' }}</dd>
            </dl>
            <h5 class="mt-4">{{ __('Bookings') }}</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead><tr><th>{{ __('Member') }}</th><th>{{ __('Status') }}</th></tr></thead>
                    <tbody>
                        @forelse($classSchedule->bookings as $b)
                        <tr>
                            <td><a href="{{ route('admin.memberships.view', $b->membership_id) }}">{{ $b->membership?->user?->getName() }}</a></td>
                            <td>{{ ucfirst($b->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="text-muted">{{ __('No bookings') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <x-slot:footer>
                <a href="{{ route('admin.class_schedules.list') }}" class="btn btn-default">{{ __('Back') }}</a>
                <a href="{{ route('admin.class_bookings.add', ['class_schedule_id' => $classSchedule->id]) }}" class="btn btn-success">{{ __('Add booking') }}</a>
                <a href="{{ route('admin.class_schedules.edit', $classSchedule) }}" class="btn btn-primary">{{ __('Edit') }}</a>
                <a href="{{ route('admin.class_schedules.delete', $classSchedule) }}" class="btn btn-danger float-right" onclick="return confirm(@json(__('Are you sure?')))">{{ __('Delete') }}</a>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
