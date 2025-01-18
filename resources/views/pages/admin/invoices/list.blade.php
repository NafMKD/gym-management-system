
@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Invoice | List'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Invoice List") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Invoice") }}</li>
                    <li class="breadcrumb-item active">{{ __("List") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="Invoices List">
            <table id="invoicesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Full Name") }}</th>
                    <th>{{ __("Package") }}</th>
                    <th>{{ __("Amount") }}</th>
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
            $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('admin.invoices.list.data') }}", 
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'package', name: 'package' },
                    { data: 'amount', name: 'amount' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });
        });
    </script>
@endsection

