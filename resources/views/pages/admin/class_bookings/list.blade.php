@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Class bookings'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Class bookings') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Bookings') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Bookings') }}">
            <x-slot:headerTools>
                <a href="{{ route('admin.class_bookings.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add booking') }}</a>
            </x-slot:headerTools>
            <div class="table-responsive">
            <table id="bookingsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Session') }}</th>
                        <th>{{ __('Member') }}</th>
                        <th>{{ __('Status') }}</th>
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
    $('#bookingsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.class_bookings.list.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'session', name: 'session', orderable: false },
            { data: 'member', name: 'member', orderable: false },
            { data: 'status', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });
});
</script>
@endsection
