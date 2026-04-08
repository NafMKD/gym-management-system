@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Add session'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Add session') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.class_schedules.list') }}">{{ __('Schedules') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Add') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Session') }}</h3></div>
            <form method="POST" action="{{ route('admin.class_schedules.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __('Gym class') }}</label> <i class="text-danger">*</i>
                                <select name="gym_class_id" class="form-control" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($gymClasses as $gc)
                                        <option value="{{ $gc->id }}" @selected(old('gym_class_id') == $gc->id)>{{ $gc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Trainer') }}</label> <i class="text-danger">*</i>
                                <select name="trainer_id" class="form-control" required>
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach($trainers as $t)
                                        <option value="{{ $t->id }}" @selected(old('trainer_id') == $t->id)>{{ $t->getName() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Starts at') }}</label> <i class="text-danger">*</i>
                                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Ends at') }}</label> <i class="text-danger">*</i>
                                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Capacity override') }}</label>
                                <input type="number" name="capacity_override" class="form-control" value="{{ old('capacity_override') }}" min="1" max="500" placeholder="{{ __('Leave empty to use class default') }}">
                            </div>
                            <div class="form-group">
                                <label>{{ __('Notes') }}</label>
                                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" maxlength="500">
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
