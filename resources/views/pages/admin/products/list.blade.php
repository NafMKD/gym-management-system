@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Products'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Products') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Products') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Products') }}">
            <x-slot:headerTools>
                <a href="{{ route('admin.products.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Add product') }}</a>
            </x-slot:headerTools>
            <table id="productsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th>{{ __('Unit price') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Active') }}</th>
                        <th>{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </x-card>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.products.list.data') }}",
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'sku', name: 'sku' },
            { data: 'unit_price', name: 'unit_price' },
            { data: 'stock_quantity', name: 'stock_quantity' },
            { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
    });
});
</script>
@endsection
