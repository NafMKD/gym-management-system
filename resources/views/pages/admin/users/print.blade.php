@extends($shellLayout ?? 'pages.admin.inc.app')

@php
    $pageTitlePrefix = auth()->user()->role === 'accountant'
        ? __('Accountant')
        : ($deskShellTitlePrefix ?? 'Admin');
@endphp

@section('header')
    @include('layouts.header', ['title' => $pageTitlePrefix . ' | ' . __('Users') . ' | ' . __('Print')])
    <style>
        .users-report-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
            color: #6c757d;
        }

        @media print {
            .main-sidebar,
            .main-header,
            .content-header,
            .main-footer,
            .no-print {
                display: none !important;
            }

            .content-wrapper,
            .content,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
@endsection

@section('content-header')
    <x-content class="content-header no-print">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('User Report Print View') }}</h1>
            </div>
            <div class="col-sm-6 text-sm-right">
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                    <i class="fas fa-print"></i> {{ __('Print') }}
                </button>
            </div>
        </div>
    </x-content>
@endsection

@section('content')
    <x-content class="content">
        <div class="card card-default">
            <div class="card-body">
                <h2 class="h4 mb-3">{{ __('Users Report') }}</h2>
                <div class="users-report-meta">
                    <span>{{ __('Generated at') }}: <strong>{{ $reportGeneratedAt->format('d/m/Y H:i') }}</strong></span>
                    <span>{{ __('Total users') }}: <strong>{{ number_format($users->count()) }}</strong></span>
                    <span>{{ __('Search') }}: <strong>{{ $filters['search'] ?? __('All') }}</strong></span>
                    <span>{{ __('Created from') }}: <strong>{{ $filters['created_from'] ?? __('Any') }}</strong></span>
                    <span>{{ __('Created to') }}: <strong>{{ $filters['created_to'] ?? __('Any') }}</strong></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Full Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Phone') }}</th>
                            <th>{{ __('Created') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($users as $reportUser)
                            <tr>
                                <td>{{ $reportUser->id }}</td>
                                <td>{{ $reportUser->getName() }}</td>
                                <td>{{ $reportUser->email ?: '—' }}</td>
                                <td>{{ $reportUser->phone }}</td>
                                <td>{{ \Carbon\Carbon::parse($reportUser->created_at)->setTimezone('Africa/Addis_Ababa')->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">{{ __('No users matched the selected filters.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-content>
@endsection
