@extends('pages.admin.inc.app')

@section('header')
    @include('layouts.header', ['title' => 'Admin | Trainer commissions'])
@endsection

@section('content-header')
    <x-content class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6"><h1 class="m-0">{{ __('Trainer commissions') }}</h1></div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">{{ __('Home') }}</li>
                    <li class="breadcrumb-item active">{{ __('Commissions') }}</li>
                </ol>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <x-card title="{{ __('Commission entries') }}">
            <x-slot:headerTools>
                <a href="{{ route('admin.trainer_commissions.add') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> {{ __('Manual entry') }}</a>
            </x-slot:headerTools>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>{{ __('Trainer') }}</label>
                    <select id="filter_trainer_id" class="form-control form-control-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($trainers as $t)
                            <option value="{{ $t->id }}">{{ $t->getName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>{{ __('From') }}</label>
                    <input type="date" id="filter_start_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label>{{ __('To') }}</label>
                    <input type="date" id="filter_end_date" class="form-control form-control-sm">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-secondary" id="applyFilters">{{ __('Apply') }}</button>
                </div>
            </div>
            <table id="commissionsTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Earned') }}</th>
                        <th>{{ __('Trainer') }}</th>
                        <th>{{ __('Source') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Session / booking') }}</th>
                        <th>{{ __('Recorded by') }}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </x-card>
    </x-content>
@endsection

@section('script')
<script>
$(function () {
    var table = $('#commissionsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.trainer_commissions.list.data') }}",
            data: function (d) {
                d.trainer_id = $('#filter_trainer_id').val();
                d.start_date = $('#filter_start_date').val();
                d.end_date = $('#filter_end_date').val();
            }
        },
        order: [[1, 'desc']],
        columns: [
            { data: 'id', name: 'id' },
            { data: 'earned_at', name: 'earned_at' },
            { data: 'trainer', name: 'trainer', orderable: false },
            { data: 'source', name: 'source', orderable: false },
            { data: 'amount', name: 'amount' },
            { data: 'session', name: 'session', orderable: false },
            { data: 'recorded_by', name: 'recorded_by', orderable: false }
        ],
    });
    $('#applyFilters').on('click', function () {
        table.ajax.reload();
    });
});
</script>
@endsection
