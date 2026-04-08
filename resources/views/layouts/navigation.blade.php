<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links (AdminLTE + sidebar: reception uses layouts.reception + Desk menu for scan) -->
    <ul class="navbar-nav ml-auto">

        @if(Auth::user()->role === 'admin')
            <li class="nav-item">
                <a class="nav-link text-default" href="{{ route('admin.attendance.scan') }}" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-qrcode ml-1"></i> {{ __('Scan ID') }}
                </a>
            </li>
        @endif

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
</nav>
<!-- /.navbar -->
