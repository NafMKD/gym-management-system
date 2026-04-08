@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Add extension request'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Add extension request") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Memberships") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.memberships.extension_requests.list') }}">{{ __("Extension requests") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Add") }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="{{ __('Extension request') }}" form="admin.memberships.extension_requests.store" footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.memberships.extension_requests.list') }}">
                        <button type="button" class="btn btn-tool"><i class="fas fa-arrow-left"></i> {{ __("Back") }}</button>
                    </a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{{ __("Membership") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <select name="membership_id" class="form-control select2bs4 @error('membership_id') is-invalid @enderror" required>
                            <option value="">-- {{ __("Select") }} --</option>
                            @foreach ($memberships as $m)
                                <option value="{{ $m->id }}" @selected((string) old('membership_id', $prefillMembershipId ?? '') === (string) $m->id)>
                                    #{{ $m->id }} — {{ $m->user?->getName() }} ({{ __("ends") }} {{ $m->end_date }})
                                </option>
                            @endforeach
                        </select>
                        @error('membership_id')
                        <span class="text-danger" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>{{ __("Requested days") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <input type="number" name="requested_days" min="1" max="10" class="form-control @error('requested_days') is-invalid @enderror"
                               value="{{ old('requested_days', 10) }}" required>
                        @error('requested_days')
                        <span class="text-danger" role="alert">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">{{ __("Maximum 10 days (SRS).") }}</small>
                    </div>
                    <div class="form-group">
                        <label>{{ __("Reason") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <textarea name="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>
                        @error('reason')
                        <span class="text-danger" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <x-slot:footer>
                <p class="float-left"><i class="text-danger font-weight-bold">*</i> {{ __("are required fields") }}</p>
                <button type="submit" class="btn btn-primary float-right loading-button">{{ __("Submit") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
