@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Extension request'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Extension request") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.memberships.extension_requests.list') }}">{{ __("Extension requests") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="{{ __('Extension request') }}" no-message footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.memberships.extension_requests.list') }}">
                        <button type="button" class="btn btn-tool"><i class="fas fa-arrow-left"></i> {{ __("Back") }}</button>
                    </a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __("Status") }}:</dt>
                        <dd class="col-sm-8">{{ ucwords($extensionRequest->status) }}</dd>
                        <dt class="col-sm-4">{{ __("Member") }}:</dt>
                        <dd class="col-sm-8">{{ $extensionRequest->membership?->user?->getName() ?? 'N/A' }}</dd>
                        <dt class="col-sm-4">{{ __("Membership ID") }}:</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.memberships.view', $extensionRequest->membership_id) }}">#{{ $extensionRequest->membership_id }}</a>
                        </dd>
                        <dt class="col-sm-4">{{ __("Requested days") }}:</dt>
                        <dd class="col-sm-8">{{ $extensionRequest->requested_days }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __("Requested by") }}:</dt>
                        <dd class="col-sm-8">{{ $extensionRequest->requester?->getName() ?? '—' }}</dd>
                        <dt class="col-sm-4">{{ __("Processed by") }}:</dt>
                        <dd class="col-sm-8">{{ $extensionRequest->approver?->getName() ?? '—' }}</dd>
                        <dt class="col-sm-4">{{ __("Created") }}:</dt>
                        <dd class="col-sm-8">{{ $extensionRequest->created_detail }}</dd>
                    </dl>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <strong>{{ __("Reason") }}:</strong>
                    <p class="mb-0">{{ $extensionRequest->reason }}</p>
                </div>
                @if($extensionRequest->rejection_reason)
                    <div class="col-12 mt-2">
                        <strong>{{ __("Rejection reason") }}:</strong>
                        <p class="mb-0">{{ $extensionRequest->rejection_reason }}</p>
                    </div>
                @endif
            </div>
            @if($extensionRequest->status === 'pending')
                <x-slot:footer>
                    <button type="button" id="approve-ext" class="btn btn-success float-right ml-2">{{ __("Approve") }}</button>
                    <button type="button" id="reject-ext" class="btn btn-danger float-right">{{ __("Reject") }}</button>
                </x-slot:footer>
            @endif
        </x-card>
    </x-content>
@endsection

@section('script')
    @if($extensionRequest->status === 'pending')
        <script>
            $(function () {
                $('#approve-ext').on('click', function () {
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
                                membership_extension_request_id: {{ $extensionRequest->id }}
                            },
                            success: function () {
                                window.location.reload();
                            },
                            error: function (xhr) {
                                Swal.fire('{{ __("Error") }}', xhr.responseJSON?.message || '', 'error');
                            }
                        });
                    });
                });
                $('#reject-ext').on('click', function () {
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
                                membership_extension_request_id: {{ $extensionRequest->id }},
                                rejection_reason: result.value || ''
                            },
                            success: function () {
                                window.location.reload();
                            },
                            error: function (xhr) {
                                Swal.fire('{{ __("Error") }}', xhr.responseJSON?.message || '', 'error');
                            }
                        });
                    });
                });
            });
        </script>
    @endif
@endsection
