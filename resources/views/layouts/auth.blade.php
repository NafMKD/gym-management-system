<!DOCTYPE html>
<html lang="en">

<head>
    @yield('title')
</head>

<body class="hold-transition sidebar-collapse layout-top-nav layout-footer-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container">
            <a href="/" class="navbar-brand">
                <img src="{{ asset('assets/dist/img/logo.JPG') }}" alt="Logo" class="brand-image img-circle elevation-2"
                     style="opacity: .8">
                <span class="brand-text font-weight-light">{{ __("My Fitness") }}</em></span>
            </a>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <ul class="navbar-nav">

                </ul>
            </div>

            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">

            </ul>
        </div>
    </nav>
    @yield('content')
    @include('layouts.footer')
</div>

@include('layouts.script')
</body>

</html>
