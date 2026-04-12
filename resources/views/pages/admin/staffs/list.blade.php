@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Staff | List'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Staff List") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Staff") }}</li>
                    <li class="breadcrumb-item active">{{ __("List") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="row mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="info-box mb-3 staff-filter-card shadow-sm"
                     data-role="" role="button" tabindex="0" title="{{ __('Show all staff') }}" style="cursor: pointer;">
                    <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('All staff') }}</span>
                        <span class="info-box-number">{{ number_format($staffCounts['all'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="info-box mb-3 staff-filter-card shadow-sm"
                     data-role="admin" role="button" tabindex="0" title="{{ __('Filter by admin') }}" style="cursor: pointer;">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-shield"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Admin') }}</span>
                        <span class="info-box-number">{{ number_format($staffCounts['admin'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="info-box mb-3 staff-filter-card shadow-sm"
                     data-role="trainer" role="button" tabindex="0" title="{{ __('Filter by trainer') }}" style="cursor: pointer;">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-dumbbell"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Trainer') }}</span>
                        <span class="info-box-number">{{ number_format($staffCounts['trainer'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="info-box mb-3 staff-filter-card shadow-sm"
                     data-role="reception" role="button" tabindex="0" title="{{ __('Filter by reception') }}" style="cursor: pointer;">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-concierge-bell"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">{{ __('Reception') }}</span>
                        <span class="info-box-number">{{ number_format($staffCounts['reception'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <x-card title="Staff List">
            <x-slot:headerTools>
                <a href="{{ route('admin.staffs.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add Staff') }}</a>
            </x-slot:headerTools>
            <table id="staffTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Full Name") }}</th>
                    <th>{{ __("Role") }}</th>
                    <th>{{ __("Email") }}</th>
                    <th>{{ __("Phone") }}</th>
                    <th>{{ __("Action") }}</th>
                </tr>
                </thead>
                <tbody>
                    
                </tbody>
            </table>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            window.staffRoleFilter = '';

            function syncStaffFilterHighlight() {
                $('.staff-filter-card').removeClass('border border-2 border-primary');
                $('.staff-filter-card').each(function () {
                    var r = $(this).data('role');
                    if ((window.staffRoleFilter || '') === (r == null ? '' : String(r))) {
                        $(this).addClass('border border-2 border-primary');
                    }
                });
            }

            var staffTable = $('#staffTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('admin.staffs.list.data') }}",
                    data: function (d) {
                        d.role_filter = window.staffRoleFilter || '';
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'role', name: 'role' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });

            $('.staff-filter-card').on('click keypress', function (e) {
                if (e.type === 'keypress' && e.which !== 13 && e.which !== 32) return;
                window.staffRoleFilter = $(this).data('role') == null ? '' : String($(this).data('role'));
                syncStaffFilterHighlight();
                staffTable.ajax.reload();
            });

            syncStaffFilterHighlight();
        });
    </script>
@endsection
