<!DOCTYPE html>
<html lang="en">

<head>
    @yield('title')
</head>

<body class="hold-transition sidebar-collapse layout-top-nav layout-footer-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <a href="{{ route(config('reception.brand_route', 'reception.home')) }}" class="navbar-brand">
                <img src="{{ asset('assets/dist/img/logo.JPG') }}" alt="Logo" class="brand-image img-circle elevation-2"
                     style="opacity: .8">
                <span class="brand-text font-weight-light">{{ __(config('reception.brand_label', 'Front desk')) }}</span>
            </a>

            <div class="collapse navbar-collapse order-3" id="receptionNavbarCollapse">
                <ul class="navbar-nav">
                    @include('layouts.partials.reception-topnav')
                    <li class="nav-item d-flex align-items-center">
                        <span class="nav-link text-muted">{{ Auth::user()->getName() }}</span>
                    </li>
                </ul>
            </div>

            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                        {{ __('Log out') }} <i class="fas fa-sign-out-alt ml-1"></i>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    @if (session('error'))
        <div class="container mt-3">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        </div>
    @endif
    @if (session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        </div>
    @endif
    @yield('content')
    @include('layouts.footer')
</div>

@include('layouts.script')
@yield('script')
</body>

</html>
