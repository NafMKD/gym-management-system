@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Add commission'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Manual commission entry') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.trainer_commissions.list') }}">{{ __('Commissions') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('Add') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-header"><h3 class="card-title">{{ __('Entry') }}</h3></div>
            <form method="POST" action="{{ route('admin.trainer_commissions.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
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
                                <label>{{ __('Amount') }} ({{ __('Birr') }})</label> <i class="text-danger">*</i>
                                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Earned date') }}</label> <i class="text-danger">*</i>
                                <input type="date" name="earned_at" class="form-control" value="{{ old('earned_at', date('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group">
                                <label>{{ __('Notes') }}</label>
                                <textarea name="notes" class="form-control" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.trainer_commissions.list') }}" class="btn btn-default">{{ __('Back') }}</a>
                    <button type="submit" class="btn btn-primary float-right loading-button">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </x-content>
@endsection
