@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Add product'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Add product') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.products.list') }}">{{ __('Products') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Add') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Product details') }}</h3></div>
            <form method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Name') }}</label> <i class="text-danger">*</i>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                                @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label>{{ __('SKU') }}</label>
                                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" maxlength="100">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Unit price') }}</label> <i class="text-danger">*</i>
                                        <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price') }}" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Initial stock') }}</label>
                                        <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>{{ __('Low stock alert at') }}</label>
                                        <input type="number" name="low_stock_threshold" class="form-control" value="{{ old('low_stock_threshold', 5) }}" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', true))>
                                    <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary float-right loading-button">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection
