@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Add Membership'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __("Add Membership") }}</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __("Home") }}</li>
                    <li class="breadcrumb-item">{{ __("Memberships") }}</li>
                    <li class="breadcrumb-item active">{{ __("Add") }}</li>
                </ol>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card class="card-default" title="Membership Information" form="admin.memberships.store" footer>
            <x-slot:headerTools>
                <div class="card-tools mr-5">
                    <a href="{{ route('admin.memberships.list') }}"><button type="button" class="btn btn-tool"><i
                                class="fas fa-arrow-left"></i>
                        {{ __("Back") }}
                        </button></a>
                </div>
            </x-slot:headerTools>
            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6">
                    <div class="form-group" id="sectionTwo">
                        <label>{{ __("Select User") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <select name="user_id" id="user_id"
                                class="form-control @error('user_id') is-invalid @enderror select2bs4"
                                >
                            <option value="">-- {{ __("Select") }} --</option>
                            @foreach ($availableMembers as $availableMember)
                                <option value="{{ $availableMember->id }}">{{ $availableMember->getName() }}
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                        <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div class="form-group" id="sectionTow">
                        <label>{{ __("Package") }}</label> <i class="text-danger font-weight-bold">*</i>
                        <select name="package_id" id="package_id"
                                class="form-control @error('package_id') is-invalid @enderror select2bs4"
                                >
                            <option value="">{{ __("Custom") }}</option>
                            @foreach ($availablePackages as $availablePackage)
                                <option value="{{ $availablePackage->id }}">{{ $availablePackage->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('package_id')
                        <span class="text-danger" role="alert">
                            {{ $message }}
                        </span>
                        @enderror
                    </div>
                    <div class="row" id="sectionThree">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("Start Date") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input type="text" class="form-control @error('start_date') is-invalid @enderror datetimepicker-input" id="start_date" name="start_date" data-toggle="datetimepicker" data-target="#start_date"/>
                                @error('start_date')
                                <span class="text-danger" role="alert">
                                    {{ $message }}
                                </span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ __("End Date") }}</label> <i class="text-danger font-weight-bold">*</i>
                                <input type="text" class="form-control @error('end_date') is-invalid @enderror datetimepicker-input" id="end_date" name="end_date" data-toggle="datetimepicker" data-target="#end_date"/>
                                @error('end_date')
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
                <button type="submit" class="btn btn-primary float-right loading-button">{{ __("Register") }}</button>
            </x-slot:footer>
        </x-card>
    </x-content>
@endsection

@section('script')
    <script>
        $(function () {
            let packageData = {};

            // date
            $('#start_date').datetimepicker({
                format: 'L'
            });
            $('#end_date').datetimepicker({
                useCurrent: false,
                format: 'L'
            });
            $("#start_date").on("change.datetimepicker", function (e) {
                let minEndDate = e.date ? e.date.clone().add(1, 'days') : null;
                $('#end_date').datetimepicker('minDate', minEndDate);
            });
            $("#end_date").on("change.datetimepicker", function (e) {
                $('#start_date').datetimepicker('maxDate', e.date);
            });


            // on package select unhide section three
            $('#package_id').on('change', function () {

                if($('#package_id').val() !== "") {
                    $.ajax({
                        url: "{{ route('admin.packages.package.data') }}", 
                        type: 'GET',
                        data: { package_id: $('#package_id').val() },
                        success: function (response) {
                            packageData = response; 
                        },
                        error: function () {
                            alert('Failed to fetch package data.');
                        }
                    });
                }

            })

            // When start_date is selected, calculate and set end_date
            $('#start_date').on('change.datetimepicker', function (e) {
                if ($('#package_id').val() !== "") {
                    if (packageData && packageData.duration > 0) {
                        const startDate = e.date; 
                        const endDate = startDate.clone().add(packageData.duration, 'days'); 

                        $('#end_date').datetimepicker('date', endDate);
                        $('#end_date').attr('disabled', true);
                    }
                } else {
                    $('#end_date').attr('disabled', false);
                }
            });
        });
    </script>
@endsection
