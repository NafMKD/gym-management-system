
@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Memberships | List'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Memberships List") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Memberships") }}</li>
                    <li class="breadcrumb-item active">{{ __("List") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="Membership List">
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.memberships.add') }}"><button type="button" class="btn btn-primary"><i
                                class="fas fa-plus"></i>
                        {{ __("Add Membership") }}
                        </button></a>
                </div>
            </x-slot:headerTools>
            <table id="membershipsTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Full Name") }}</th>
                    <th>{{ __("Start Date") }}</th>
                    <th>{{ __("End Date") }}</th>
                    <th>{{ __("Remaining Days") }}</th>
                    <th>{{ __("Status") }}</th>
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
            $('#membershipsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.memberships.list.data') }}", 
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'start_date', name: 'start_date' },
                    { data: 'end_date', name: 'end_date' },
                    { data: 'remaining_days', name: 'remaining_days' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });
        });
    </script>
@endsection

