@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Edit User'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Edit User") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("User") }}</li>
                    <li class="breadcrumb-item active">{{ __("Edit") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="User Information" form="admin.users.update"  form-update="{{$user->id}}" footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.users.list') }}"><button type="button" class="btn btn-tool"><i
                                class="fas fa-arrow-left"></i>
                            {{ __("Back") }}
                        </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("First Name") }}</label>
                                <input id="first_name" placeholder="{{ __("Enter First Name") }}" type="text"
                                    class="form-control @error('first_name') is-invalid @enderror" name="first_name"
                                    value="{{ $user->first_name }}" required autocomplete="first_name">
                                @error('first_name')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Last Name") }}</label>
                                <input id="last_name" placeholder="{{ __("Enter Last Name") }}" type="text"
                                    class="form-control @error('last_name') is-invalid @enderror" name="last_name"
                                    value="{{ $user->last_name }}" required autocomplete="last_name">
                                @error('last_name')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ __("Email") }}</label>
                        <input id="email" placeholder="{{ __("Enter Email") }}" type="email"
                            class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ $user->email }}" required autocomplete="email">
                        @error('email')
                        <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>{{ __("Phone") }}</label>
                                <input id="phone" placeholder="{{ __("Enter Phone") }}" type="text"
                                    class="form-control @error('phone') is-invalid @enderror" name="phone"
                                    value="{{ $user->phone }}" maxlength="10" minlength="0" required autocomplete="phone">
                                @error('phone')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>{{ __("Gender") }}</label>
                                <select name="gender" id="gender"
                                        class="form-control @error('gender') is-invalid @enderror"
                                        autofocus>
                                    <option value="Male" {{ $user->gender == "Male" ? "selected" : "" }}>{{ __("Male") }}</option>
                                    <option value="Female" {{ $user->gender == "Female" ? "selected" : "" }}>{{ __("Female") }}</option>
                                </select>
                                @error('gender')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <p class=" float-left"><i class="text-danger font-weight-bold">*</i> {{ __("are required fields") }}</p>
                <button type="submit" class="btn btn-primary float-right .loading-button">{{ __("Update") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
