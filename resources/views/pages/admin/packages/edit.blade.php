@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Edit Package'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Edit Package") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Packages") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.packages.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Package Information" form="admin.packages.update"  form-update="{{$package->id}}" footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.packages.list') }}"><button type="button" class="btn btn-tool"><i
                                class="fas fa-arrow-left"></i>
                            {{ __("Back") }}
                        </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __("Name") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <input id="name" placeholder="{{ __("Enter Name") }}" type="name"
                            class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ $package->name }}" required autocomplete="name">
                        @error('name')
                        <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>{{ __("Price") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="price" placeholder="{{ __("Enter Price") }}" type="number"
                                    class="form-control @error('price') is-invalid @enderror" name="price"
                                    value="{{ $package->price }}" required autocomplete="price">
                                @error('price')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __("Duration") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input id="duration" placeholder="{{ __("Enter Duration") }}" type="number"
                                    class="form-control @error('duration') is-invalid @enderror" name="duration"
                                    value="{{ $package->duration }}" required autocomplete="duration">
                                @error('duration')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __("Description") }}</label>
                        <textarea name="description" placeholder="{{ __("Enter Description") }}..."  id="description" class="form-control @error('description') is-invalid @enderror" cols="30" rows="10">{{ $package->description }}</textarea>
                        @error('description')
                        <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <p class=" float-left"><i class="text-danger font-weight-bold">*</i> {{ __("are required fields") }}</p>
                <button type="submit" class="btn btn-primary float-right loading-button">{{ __("Update") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
