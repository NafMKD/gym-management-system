@extends('layouts.portal')

@section('title')
    @include('layouts.header', ['title' => __('Trainer profile')])
@endsection

@section('content')
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ __('Trainer profile') }}</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-header"><h3 class="card-title">{{ $user->getName() }}</h3></div>
                    <form method="POST" action="{{ route('trainer.profile.update') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>{{ __('Qualifications') }}</label>
                                        <textarea name="qualifications" class="form-control" rows="4" maxlength="5000">{{ old('qualifications', $profile->qualifications) }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('Specializations') }}</label>
                                        <textarea name="specializations" class="form-control" rows="4" maxlength="5000">{{ old('specializations', $profile->specializations) }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('Bio') }}</label>
                                        <textarea name="bio" class="form-control" rows="3" maxlength="5000">{{ old('bio', $profile->bio) }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('Commission per attended session') }} ({{ __('Birr') }})</label> <i class="text-danger">*</i>
                                        <input type="number" step="0.01" min="0" name="commission_per_session" class="form-control" value="{{ old('commission_per_session', $profile->commission_per_session) }}" required>
                                        <small class="text-muted">{{ __('Default rate for session commissions when a booking is marked attended.') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('trainer.home') }}" class="btn btn-default">{{ __('Back') }}</a>
                            <button type="submit" class="btn btn-primary float-right loading-button">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
