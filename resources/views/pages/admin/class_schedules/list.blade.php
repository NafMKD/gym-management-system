@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Class schedules'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Class schedules') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Schedules') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Sessions') }}">
            @if(Auth::user()->role === 'admin')
            <x-slot:headerTools>
                <a href="{{ route('admin.class_schedules.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add session') }}</a>
            </x-slot:headerTools>
            @endif
            <div class="table-responsive">
            <table id="schedulesTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Class') }}</th>
                        <th>{{ __('Trainer') }}</th>
                        <th>{{ __('Starts') }}</th>
                        <th>{{ __('Ends') }}</th>
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
    $('#schedulesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.class_schedules.list.data') }}",
        order: [[3, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'class_name', name: 'class_name', orderable: false, searchable: false },
            { data: 'trainer_name', name: 'trainer_name', orderable: false, searchable: false },
            { data: 'starts_at', name: 'starts_at' },
            { data: 'ends_at', name: 'ends_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });
});
</script>
@endsection
