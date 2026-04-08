@extends($shellLayout ?? 'pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => ($deskShellTitlePrefix ?? 'Admin') . ' | Change package'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Change package") }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.memberships.view', $membership) }}">{{ __("Membership") }}</a></li>
                    <li class="breadcrumb-item active">{{ __("Upgrade") }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="{{ __('Select new package') }}" form="admin.memberships.upgrade.update" form-update="{{ $membership->id }}" footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.memberships.view', $membership) }}">
                        <button type="button" class="btn btn-tool"><i class="fas fa-arrow-left"></i> {{ __("Back") }}</button>
                    </a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <p class="text-muted">{{ __("Membership #:id — :name", ['id' => $membership->id, 'name' => $membership->user?->getName() ?? '']) }}</p>
                    <div class="form-group">
                        <label>{{ __("Package") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <select name="package_id" class="form-control select2bs4 @error('package_id') is-invalid @enderror" required>
                            <option value="">-- {{ __("Select") }} --</option>
                            @foreach ($availablePackages as $pkg)
                                <option value="{{ $pkg->id }}" @selected((string) old('package_id', $membership->package_id) === (string) $pkg->id)>
                                    {{ $pkg->name }} — {{ number_format($pkg->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                        <span class="text-danger" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <p class="small text-muted">{{ __("End date and visit days will be recalculated from the package duration and granted days.") }}</p>
                </div>
            </div>
            <x-slot:footer>
                <button type="submit" class="btn btn-primary float-right loading-button">{{ __("Save") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection
