@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | User | View'])
    <style>
        .user-history-tabs .nav-link {
            font-weight: 600;
        }

        .user-history-toolbar {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .user-history-toolbar .form-group {
            margin-bottom: 0;
            min-width: 320px;
        }

        .user-history-empty {
            border: 1px dashed #ced4da;
            border-radius: 0.35rem;
            background: #f8f9fa;
            padding: 1rem;
            color: #6c757d;
        }

        @media (max-width: 767.98px) {
            .user-history-toolbar .form-group {
                min-width: 100%;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("User Detail") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("User") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default collapsed-card">
            <div class="card-header">
                <h3 class="card-title">{{ __("User Detail") }}</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.users.list') }}" class="btn btn-tool mr-2">
                        <i class="fas fa-arrow-left"></i> {{ __("Back") }}
                    </a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="{{ __('Expand') }}">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-3">{{ __("User Id") }}:</dt>
                            <dd class="col-sm-9">{{ $user->id }}</dd>
                            <dt class="col-sm-3">{{ __("Full Name") }}:</dt>
                            <dd class="col-sm-9">{{ $user->getName() }}</dd>
                            <dt class="col-sm-3">{{ __("Email") }}:</dt>
                            <dd class="col-sm-9">{{ $user->email ?: '-' }}</dd>
                            <dt class="col-sm-3">{{ __("Phone") }}:</dt>
                            <dd class="col-sm-9">{{ $user->phone }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-3">{{ __("Register Date") }}:</dt>
                            <dd class="col-sm-9">
                                {{ $user->created_detail }}
                            </dd>
                            <dt class="col-sm-3">{{ __("Last Update") }}:</dt>
                            <dd class="col-sm-9">
                                {{ $user->updated_detail }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">{{ __("History") }}</h3>
            </div>
            <div class="card-body">
                <ul class="nav nav-pills user-history-tabs mb-3" id="user-history-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="membership-history-tab" data-toggle="pill" href="#membership-history-pane" role="tab" aria-controls="membership-history-pane" aria-selected="true">
                            {{ __("Membership History") }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="attendance-history-tab" data-toggle="pill" href="#attendance-history-pane" role="tab" aria-controls="attendance-history-pane" aria-selected="false">
                            {{ __("Attendance History") }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="membership-history-pane" role="tabpanel" aria-labelledby="membership-history-tab">
                        <div class="table-responsive">
                            <table id="membershipHistoryTable" class="table table-bordered table-striped table-hover table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>{{ __("Membership") }}</th>
                                        <th>{{ __("Package") }}</th>
                                        <th>{{ __("Start Date") }}</th>
                                        <th>{{ __("End Date") }}</th>
                                        <th>{{ __("Remaining Days") }}</th>
                                        <th>{{ __("Price") }}</th>
                                        <th>{{ __("Status") }}</th>
                                        <th>{{ __("Registered") }}</th>
                                        <th>{{ __("Updated") }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="attendance-history-pane" role="tabpanel" aria-labelledby="attendance-history-tab">
                        <div class="user-history-toolbar">
                            <div class="form-group">
                                <label for="attendanceMembershipFilter" class="mb-1">{{ __("Membership") }}</label>
                                <select id="attendanceMembershipFilter" class="form-control" @disabled($membershipOptions->isEmpty())>
                                    @forelse($membershipOptions as $membershipOption)
                                        <option value="{{ $membershipOption->id }}">
                                            #{{ $membershipOption->id }} - {{ $membershipOption->package?->name ?? __('Custom') }}
                                            ({{ $membershipOption->start_date }} to {{ $membershipOption->end_date }})
                                        </option>
                                    @empty
                                        <option value="">{{ __("No memberships found") }}</option>
                                    @endforelse
                                </select>
                            </div>

                            <small class="text-muted">{{ __("Attendance is loaded when this tab is opened.") }}</small>
                        </div>

                        @if($membershipOptions->isEmpty())
                            <div class="user-history-empty">{{ __("This user has no memberships yet, so there is no attendance history to display.") }}</div>
                        @endif

                        <div class="table-responsive">
                            <table id="attendanceHistoryTable" class="table table-bordered table-striped table-hover table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>{{ __("Attendance") }}</th>
                                        <th>{{ __("Membership") }}</th>
                                        <th>{{ __("Package") }}</th>
                                        <th>{{ __("Entry Date") }}</th>
                                        <th>{{ __("Membership Status") }}</th>
                                        <th>{{ __("Record") }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-content>
@endsection

@section('script')
    <script>
        $(function () {
            let membershipHistoryTable = null;
            let attendanceHistoryTable = null;

            function initMembershipHistoryTable() {
                if (membershipHistoryTable) {
                    return;
                }

                membershipHistoryTable = $('#membershipHistoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    order: [[2, 'desc']],
                    ajax: "{{ route('admin.users.membership_history.data', $user) }}",
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'package_name', name: 'package_name', orderable: false, searchable: false },
                        { data: 'start_date', name: 'start_date' },
                        { data: 'end_date', name: 'end_date' },
                        { data: 'remaining_days', name: 'remaining_days' },
                        { data: 'price', name: 'price' },
                        { data: 'status', name: 'status', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'updated_at', name: 'updated_at' }
                    ]
                });
            }

            function initAttendanceHistoryTable() {
                if (attendanceHistoryTable) {
                    return;
                }

                attendanceHistoryTable = $('#attendanceHistoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    order: [[3, 'desc']],
                    ajax: {
                        url: "{{ route('admin.users.attendance_history.data', $user) }}",
                        data: function (d) {
                            d.membership_id = $('#attendanceMembershipFilter').val();
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id' },
                        { data: 'membership_id', name: 'membership_id' },
                        { data: 'package_name', name: 'package_name', orderable: false, searchable: false },
                        { data: 'entry_date', name: 'entry_date' },
                        { data: 'membership_status', name: 'membership_status', orderable: false, searchable: false },
                        { data: 'record_status', name: 'record_status', orderable: false, searchable: false }
                    ]
                });
            }

            initMembershipHistoryTable();

            $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
                const target = $(e.target).attr('href');

                if (target === '#attendance-history-pane') {
                    initAttendanceHistoryTable();
                    if (attendanceHistoryTable) {
                        attendanceHistoryTable.columns.adjust();
                    }
                }

                if (target === '#membership-history-pane' && membershipHistoryTable) {
                    membershipHistoryTable.columns.adjust();
                }
            });

            $('#attendanceMembershipFilter').on('change', function () {
                if (attendanceHistoryTable) {
                    attendanceHistoryTable.ajax.reload();
                }
            });
        });
    </script>
@endsection
