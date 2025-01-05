@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Membership | View'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Membership Detail") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Membership") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.memberships.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Membership Detail" no-message footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a class="mr-5" href="{{ route('admin.memberships.print_id_card', $membership->id) }}"><button type="button" class="btn btn-success"><i
                            class="fas fa-print"></i>
                    {{ __("Print ID Card") }}
                    </button></a>
                    <a href="{{ route('admin.memberships.list') }}"><button type="button" class="btn btn-tool"><i
                        class="fas fa-arrow-left"></i>
                    {{ __("Back") }}
                    </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Full Name") }}:</dt>
                        <dd class="col-sm-9"><a href="{{ route('admin.users.view', $membership->user->id) }}">{{ $membership->user->getName() }}</a></dd>
                        <dt class="col-sm-3">{{ __("Start Date") }}:</dt>
                        <dd class="col-sm-9">{{ $membership->start_date }}</dd>
                        <dt class="col-sm-3">{{ __("End Date") }}:</dt>
                        <dd class="col-sm-9">{{ $membership->end_date }}</dd>
                        <dt class="col-sm-3">{{ __("Remaining days") }}:</dt>
                        <dd class="col-sm-9">{{ $membership->remaining_days }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Total Price") }}:</dt>
                        <dd class="col-sm-9">{{ number_format($membership->price, 2, '.', ',') }}</dd>
                        <dt class="col-sm-3">{{ __("Status") }}:</dt>
                        <dd class="col-sm-9 {{ $membership->status === 'active' ? 'text-success' : ($membership->status === 'inactive' ? 'text-warning' : 'text-danger') }} ">{{ ucwords($membership->status) }}</dd>
                        <dt class="col-sm-3">{{ __("Register Date") }}:</dt>
                        <dd class="col-sm-9">
                            {{ $membership->created_detail }}
                        </dd>
                        <dt class="col-sm-3">{{ __("Last Update") }}:</dt>
                        <dd class="col-sm-9">
                            {{ $membership->updated_detail }}
                        </dd>
                    </dl>
                </div>
            </div>
            <x-slot:footer>
                @if($membership->status !== 'cancelled')
                <button type="button" id="cancel" class="btn btn-danger float-left">{{ __("Cancel") }}</button>
                <button type="button" id="changeStatus" class="btn btn-warning float-right">{{ __("Change Status") }}</button>
                @endif
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        $(function () {

            $('#cancel').on('click', function() {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    html: `{{ __("You won\'t be able to revert this!") }}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, cancel it!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.memberships.cancel', $membership->id) }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                membership_id: '{{ $membership->id }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    '{{ __("Cancelled!") }}',
                                    '{{ __("Membership has been cancelled.") }}',
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            }, 
                            error: function(xhr) {
                                if (xhr.status === 400) {
                                    Swal.fire(
                                        '{{ __("Bad Request!") }}',
                                        xhr.responseJSON?.message || '{{ __("Invalid request. Please check your input.") }}',
                                        'warning'
                                    );
                                } else {
                                    Swal.fire(
                                        '{{ __("Error!") }}',
                                        '{{ __("Something went wrong.") }}',
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                });
            });

            $('#changeStatus').on('click', function() {
                Swal.fire({
                    title: '{{ __("Are you sure?") }}',
                    text: '{{ __("You are about to change the status of this membership!") }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: '{{ __("Yes, change it!") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('admin.memberships.change.status', $membership->id) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}', 
                                membership_id: '{{ $membership->id }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    '{{ __("Changed!") }}',
                                    '{{ __("Membership status has been changed.") }}',
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            }, 
                            error: function(xhr) {
                                if (xhr.status === 400) {
                                    Swal.fire(
                                        '{{ __("Bad Request!") }}',
                                        xhr.responseJSON?.message || '{{ __("Invalid request. Please check your input.") }}',
                                        'warning'
                                    );
                                } else {
                                    Swal.fire(
                                        '{{ __("Error!") }}',
                                        '{{ __("Something went wrong.") }}',
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
