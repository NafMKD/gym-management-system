<!DOCTYPE html>
<html lang="en">

<head>
    @yield('title')
</head>

<body class="hold-transition sidebar-collapse layout-top-nav layout-footer-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <a href="{{ url('/') }}" class="navbar-brand">
                <img src="{{ asset('assets/dist/img/logo.JPG') }}" alt="Logo" class="brand-image img-circle elevation-2"
                     style="opacity: .8">
                <span class="brand-text font-weight-light">{{ config('app.name', 'My Fitness') }}</span>
            </a>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <ul class="navbar-nav">
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
    @yield('content')
    @include('layouts.footer')
</div>

@include('layouts.script')
@yield('script')
</body>

</html>
