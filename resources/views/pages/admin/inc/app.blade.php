<!DOCTYPE html>
<html lang="en">

<head>
    @yield('header')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        @include('layouts.navigation')

        {{-- @include('pages.admin.inc.nav') --}}

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            @yield('content-header')
            <!-- /.content-header -->

            <!-- Main content -->
            @yield('content')
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        @include('layouts.footer')

    </div>
    <!-- ./wrapper -->

    @include('layouts.script')
    <!-- Custom Scripts -->
    @yield('script')
    <!-- ./Custom scripts -->
</body>

</html>
