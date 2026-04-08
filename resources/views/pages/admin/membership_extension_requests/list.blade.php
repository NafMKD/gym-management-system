@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Extension requests'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Extension requests") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Memberships") }}</li>
                    <li class="breadcrumb-item active">{{ __("Extension requests") }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Extension requests') }}">
            <x-slot:headerTools>
                <a href="{{ route('admin.memberships.extension_requests.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add request') }}</a>
            </x-slot:headerTools>
            <div class="table-responsive">
            <table id="extensionRequestsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Member") }}</th>
                    <th>{{ __("Membership ID") }}</th>
                    <th>{{ __("Days") }}</th>
                    <th>{{ __("Reason") }}</th>
                    <th>{{ __("Status") }}</th>
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
        $(document).ready(function () {
            const table = $('#extensionRequestsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.memberships.extension_requests.list.data') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'member', name: 'member', orderable: false, searchable: false},
                    {data: 'membership_id', name: 'membership_id'},
                    {data: 'requested_days', name: 'requested_days'},
                    {data: 'reason', name: 'reason'},
                    {data: 'status', name: 'status', orderable: false},
                    {data: 'created_at', name: 'created_at'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
            });

            $(document).on('click', '.btn-approve-ext', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '{{ __("Approve extension?") }}',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("Yes") }}'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.memberships.extension_requests.approve') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            membership_extension_request_id: id
                        },
                        success: function () {
                            Swal.fire('{{ __("Done") }}', '', 'success').then(() => table.ajax.reload(null, false));
                        },
                        error: function (xhr) {
                            Swal.fire('{{ __("Error") }}', xhr.responseJSON?.message || '', 'error');
                        }
                    });
                });
            });

            $(document).on('click', '.btn-reject-ext', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: '{{ __("Reject extension?") }}',
                    input: 'textarea',
                    inputPlaceholder: '{{ __("Reason (optional)") }}',
                    showCancelButton: true,
                    confirmButtonText: '{{ __("Reject") }}'
                }).then((result) => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('admin.memberships.extension_requests.reject') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            membership_extension_request_id: id,
                            rejection_reason: result.value || ''
                        },
                        success: function () {
                            Swal.fire('{{ __("Done") }}', '', 'success').then(() => table.ajax.reload(null, false));
                        },
                        error: function (xhr) {
                            Swal.fire('{{ __("Error") }}', xhr.responseJSON?.message || '', 'error');
                        }
                    });
                });
            });
        });
    </script>
@endsection
