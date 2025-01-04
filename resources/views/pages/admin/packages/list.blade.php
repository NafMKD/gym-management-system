
@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Packages | List'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Packages List") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Packages") }}</li>
                    <li class="breadcrumb-item active">{{ __("List") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="Packages List">
            <table id="packagesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Name") }}</th>
                    <th>{{ __("Duration") }}</th>
                    <th>{{ __("Price") }}</th>
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
            $('#packagesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.packages.list.data') }}", 
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'duration', name: 'duration' },
                    { data: 'price', name: 'price' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });
        });
    </script>
@endsection

