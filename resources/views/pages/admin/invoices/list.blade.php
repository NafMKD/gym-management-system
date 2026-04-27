
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
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="statusFilter" class="mb-1">{{ __("Status") }}</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">{{ __("All") }}</option>
                        <option value="paid">{{ __("Paid") }}</option>
                        <option value="unpaid">{{ __("Unpaid") }}</option>
                    </select>
                </div>
            </div>

            <table id="invoicesTable" class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __("Full Name") }}</th>
                    <th>{{ __("Package") }}</th>
                    <th>{{ __("Amount") }}</th>
                    <th>{{ __("Issue Date") }}</th>
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
            let table = $('#invoicesTable').DataTable({
                processing: true,
                serverSide: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('admin.invoices.list.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'package', name: 'package' },
                    { data: 'amount', name: 'amount' },
                    { data: 'issued_date', name: 'issued_date' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
            });

            $('#statusFilter').on('change', function () {
                table.ajax.reload();
            });
        });
    </script>
@endsection
