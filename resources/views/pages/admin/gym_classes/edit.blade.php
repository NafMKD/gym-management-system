@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Edit gym class'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Edit gym class') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.gym_classes.list') }}">{{ __('Gym classes') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Edit') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Class details') }}</h3></div>
            <form method="POST" action="{{ route('admin.gym_classes.update', $gymClass) }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Name') }}</label> <i class="text-danger">*</i>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $gymClass->name) }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Description') }}</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $gymClass->description) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Capacity') }}</label> <i class="text-danger">*</i>
                                        <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $gymClass->capacity) }}" min="1" max="500" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('Duration (minutes)') }}</label> <i class="text-danger">*</i>
                                        <input type="number" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', $gymClass->duration_minutes) }}" min="15" max="480" required>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $gymClass->is_active))>
                                    <label class="custom-control-label" for="is_active">{{ __('Active') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.gym_classes.view', $gymClass) }}" class="btn btn-default">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary float-right loading-button">{{ __('Update') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection
