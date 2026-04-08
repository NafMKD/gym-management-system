@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Gym classes'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Gym classes') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Gym classes') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Gym classes') }}">
            <x-slot:headerTools>
                <a href="{{ route('admin.gym_classes.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add class') }}</a>
            </x-slot:headerTools>
            <div class="table-responsive">
            <table id="gymClassesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Capacity') }}</th>
                        <th>{{ __('Duration (min)') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            </div>
        </x-card>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    $('#gymClassesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.gym_classes.list.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'capacity', name: 'capacity' },
            { data: 'duration_minutes', name: 'duration_minutes' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });
});
</script>
@endsection
