@extends($shellLayout ?? 'pages.admin.inc.app')

@php
    $pageTitlePrefix = auth()->user()->role === 'accountant'
        ? __('Accountant')
        : ($deskShellTitlePrefix ?? 'Admin');
    $isAccountant = auth()->user()->role === 'accountant';
@endphp

@section('header')
    @include('layouts.header', ['title' => $pageTitlePrefix . ' | ' . __('Users') . ' | ' . __('List')])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $isAccountant ? __("User Reports") : __("Users List") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Users") }}</li>
                    <li class="breadcrumb-item active">{{ __("List") }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ $isAccountant ? __('User Reports') : __('Users List') }}">
            <x-slot:headerTools>
                <div class="d-flex flex-wrap justify-content-end" style="gap: 0.5rem;">
                    @if(! $isAccountant)
                        <a href="{{ route('admin.users.add') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> {{ __('Add User') }}
                        </a>
                    @endif
                    <a href="{{ route('admin.users.export.csv') }}" class="btn btn-outline-secondary btn-sm" id="usersExportLink">
                        <i class="fas fa-file-csv"></i> {{ __('Export CSV') }}
                    </a>
                    <a href="{{ route('admin.users.print') }}" class="btn btn-outline-dark btn-sm" id="usersPrintLink" target="_blank" rel="noopener">
                        <i class="fas fa-print"></i> {{ __('Print') }}
                    </a>
                </div>
            </x-slot:headerTools>

            <form id="usersFilterForm" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label for="report_search" class="mb-1">{{ __('Search') }}</label>
                        <input
                            type="text"
                            id="report_search"
                            class="form-control"
                            placeholder="{{ __('Name, email, or phone') }}"
                        >
                    </div>
                    <div class="col-md-3">
                        <label for="created_from" class="mb-1">{{ __('Created from') }}</label>
                        <input type="date" id="created_from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="created_to" class="mb-1">{{ __('Created to') }}</label>
                        <input type="date" id="created_to" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary">{{ __('Apply') }}</button>
                            <button type="button" id="resetUsersFilters" class="btn btn-secondary">{{ __('Reset') }}</button>
                        </div>
                    </div>
                </div>

                <div class="mt-2 d-flex flex-wrap" style="gap: 0.5rem;">
                    <button type="button" class="btn btn-outline-secondary btn-sm users-date-preset" data-preset="today">{{ __('Today') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm users-date-preset" data-preset="this_month">{{ __('This month') }}</button>
                </div>
            </form>

            <div class="table-responsive">
                <table id="usersTable" class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __("Full Name") }}</th>
                        <th>{{ __("Email") }}</th>
                        <th>{{ __("Phone") }}</th>
                        <th>{{ __("Created") }}</th>
                        <th>{{ __("Action") }}</th>
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
            function pad(n) {
                return n < 10 ? '0' + n : String(n);
            }

            function formatDate(date) {
                return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
            }

            function applyPreset(preset) {
                var now = new Date();

                if (preset === 'today') {
                    var today = formatDate(now);
                    $('#created_from').val(today);
                    $('#created_to').val(today);
                    return;
                }

                if (preset === 'this_month') {
                    var first = new Date(now.getFullYear(), now.getMonth(), 1);
                    var last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                    $('#created_from').val(formatDate(first));
                    $('#created_to').val(formatDate(last));
                }
            }

            function buildReportQuery() {
                var params = new URLSearchParams();
                var search = $('#report_search').val();
                var createdFrom = $('#created_from').val();
                var createdTo = $('#created_to').val();

                if (search) {
                    params.set('search', search);
                }
                if (createdFrom) {
                    params.set('created_from', createdFrom);
                }
                if (createdTo) {
                    params.set('created_to', createdTo);
                }

                return params.toString();
            }

            function syncReportLinks() {
                var query = buildReportQuery();
                var exportUrl = "{{ route('admin.users.export.csv') }}";
                var printUrl = "{{ route('admin.users.print') }}";

                $('#usersExportLink').attr('href', query ? exportUrl + '?' + query : exportUrl);
                $('#usersPrintLink').attr('href', query ? printUrl + '?' + query : printUrl);
            }

            var usersTable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                order: [[4, 'desc']],
                ajax: {
                    url: "{{ route('admin.users.list.data') }}",
                    data: function (d) {
                        d.search = $('#report_search').val();
                        d.created_from = $('#created_from').val();
                        d.created_to = $('#created_to').val();
                    },
                    error: function (xhr) {
                        var response = xhr.responseJSON || {};
                        var errors = response.errors || {};
                        var errorMessages = '';

                        Object.keys(errors).forEach(function (key) {
                            errorMessages += errors[key].join('<br>') + '<br>';
                        });

                        Toast.fire({
                            icon: 'error',
                            title: response.message || 'Failed to load user report.',
                            html: errorMessages || undefined
                        });
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });

            $('#usersFilterForm').on('submit', function (e) {
                e.preventDefault();
                syncReportLinks();
                usersTable.ajax.reload();
            });

            $('#resetUsersFilters').on('click', function () {
                $('#report_search').val('');
                $('#created_from').val('');
                $('#created_to').val('');
                syncReportLinks();
                usersTable.ajax.reload();
            });

            $('.users-date-preset').on('click', function () {
                applyPreset($(this).data('preset'));
                syncReportLinks();
                usersTable.ajax.reload();
            });

            syncReportLinks();
        });
    </script>
@endsection
