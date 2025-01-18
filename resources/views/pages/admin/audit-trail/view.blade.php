@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Audit Trail | View'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Audit Trail Detail") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Audit Trail") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.audit_trail.list') }}">{{ __("List") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Detail") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Audit Trail Detail" no-message>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.audit_trail.list') }}">
                        <button type="button" class="btn btn-tool">
                            <i class="fas fa-arrow-left"></i> {{ __("Back") }}
                        </button>
                    </a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __("Audit ID") }}:</dt>
                        <dd class="col-sm-8">{{ $auditTrail->id }}</dd>
                        <dt class="col-sm-4">{{ __("User") }}:</dt>
                        <dd class="col-sm-8">{{ $auditTrail->user->getName() ?? 'N/A' }}</dd>
                        <dt class="col-sm-4">{{ __("Table Name") }}:</dt>
                        <dd class="col-sm-8">{{ $auditTrail->table_name }}</dd>
                        <dt class="col-sm-4">{{ __("Record ID") }}:</dt>
                        <dd class="col-sm-8">{{ $auditTrail->record_id }}</dd>
                        <dt class="col-sm-4">{{ __("Action") }}:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-{{ $auditTrail->action == 'insert' ? 'success' : ($auditTrail->action == 'update' ? 'primary' : 'danger') }}">
                                {{ ucfirst($auditTrail->action) }}
                            </span>
                        </dd>
                        <dt class="col-sm-4">{{ __("Created At") }}:</dt>
                        <dd class="col-sm-8">{{ $auditTrail->created_detail }}</dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <h5>{{ __("Changes") }}</h5>
                    <div class="table-responsive">
                        @php
                            $changedData = is_string($auditTrail->changed_data) 
                                ? json_decode($auditTrail->changed_data, true) 
                                : $auditTrail->changed_data;
                        @endphp
                        @if($auditTrail->action == 'insert' || $auditTrail->action == 'delete')
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __("Field") }}</th>
                                        <th>{{ $auditTrail->action == 'insert' ? __("Value") : __("Deleted Value") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changedData as $field => $value)
                                        @if(!in_array($field, ['password', 'remember_token']))
                                            <tr>
                                                <td>{{ $field }}</td>
                                                <td>{{ $value ?? 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @elseif($auditTrail->action == 'update')
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __("Field") }}</th>
                                        <th>{{ __("Before") }}</th>
                                        <th>{{ __("After") }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changedData['before'] as $field => $beforeValue)
                                        @if(!in_array($field, ['password', 'remember_token']))
                                            <tr>
                                                <td>{{ $field }}</td>
                                                <td>{{ $beforeValue ?? 'N/A' }}</td>
                                                <td>{{ $changedData['after'][$field] ?? 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </x-content>
@endsection
