@extends('pages.admin.inc.app')

@section('header')
@include('layouts.header', ['title' => 'Admin | Home'])
@endsection

@section('content-header')
<x-content class="content-header">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
        </div><!-- /.col -->
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item active">Home</li>
            </ol>
        </div><!-- /.col -->
    </div><!-- /.row -->
</x-content>
@endsection

@section('content')
<x-content class="content" sortable>
    Comming soon...
</x-content>
@endsection

@section('script')

@endsection
