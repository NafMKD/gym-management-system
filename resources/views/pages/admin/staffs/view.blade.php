@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Staff | View'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Staff Detail") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Staff") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.staffs.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Staff Detail" no-message>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.staffs.list') }}"><button type="button" class="btn btn-tool"><i
                                class="fas fa-arrow-left"></i>
                        {{ __("Back") }}
                        </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Staff Id") }}:</dt>
                        <dd class="col-sm-9">{{ $user->id }}</dd>
                        <dt class="col-sm-3">{{ __("Full Name") }}:</dt>
                        <dd class="col-sm-9">{{ $user->getName() }}</dd>
                        <dt class="col-sm-3">{{ __("Email") }}:</dt>
                        <dd class="col-sm-9">{{ $user->email }}</dd>
                        <dt class="col-sm-3">{{ __("Phone") }}:</dt>
                        <dd class="col-sm-9">{{ $user->phone }}</dd>
                        <dt class="col-sm-3">{{ __("Role") }}:</dt>
                        <dd class="col-sm-9">{{ ucfirst($user->role) }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-3">{{ __("Register Date") }}:</dt>
                        <dd class="col-sm-9">
                            {{ $user->created_detail }}
                        </dd>
                        <dt class="col-sm-3">{{ __("Last Update") }}:</dt>
                        <dd class="col-sm-9">
                            {{ $user->updated_detail }}
                        </dd>
                    </dl>
                </div>
            </div>
        </x-card>
    </x-content>
@endsection
