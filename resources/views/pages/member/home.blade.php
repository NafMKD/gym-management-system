@extends('layouts.portal')

@section('title')
    @include('layouts.header', ['title' => $portalTitle . ' | Home'])
@endsection

@section('content')
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">{{ $portalTitle }}</h1>
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                <p class="mb-2">
                    <a href="{{ route('member.classes.index') }}" class="btn btn-primary">{{ __('Group classes') }}</a>
                </p>
                <p class="text-muted">{{ __('More features coming soon.') }}</p>
            </div>
        </section>
    </div>
@endsection
