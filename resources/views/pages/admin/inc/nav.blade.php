<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <span class="brand-text font-weight-light">{{ __("Admin Dashboard") }}</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ Auth::user()->avatar() }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="{{ route('admin.profile.view') }}" class="d-block">{{ Auth::user()->getName() }}</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                       aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('admin.home') }}" class="nav-link {{ !request()->routeIs('admin.home') ?: 'active' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __("Dashboard") }}</p>
                    </a>
                </li>
                <li class="nav-item {{ !request()->routeIs('admin.department*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.department*') ?: 'active' }}">
                        <i class="nav-icon fas fa-university"></i>
                        <p>
                            {{ __("Department") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.department.add') }}" class="nav-link {{ !request()->routeIs('admin.department.add') ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Department") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.department.list') }}" class="nav-link  {{ !(request()->routeIs('admin.department.list') || request()->routeIs('admin.department.view') || request()->routeIs('admin.department.edit')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Department List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ !request()->routeIs('admin.staff*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.staff*') ?: 'active' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            {{ __("Staffs") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.staff.add') }}" class="nav-link {{ !request()->routeIs('admin.staff.add') ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Staff") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.staff.list') }}" class="nav-link {{ !(request()->routeIs('admin.staff.list') || request()->routeIs('admin.staff.view') ) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Staff List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ !(request()->routeIs('admin.template*')) ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !(request()->routeIs('admin.template*')) ?: 'active' }}">
                        <i class="nav-icon fas fa-columns"></i>
                        <p>
                            {{ __("Templates") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.template.add') }}" class="nav-link {{ !(request()->routeIs('admin.template.add')) ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Template") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.template.list') }}" class="nav-link {{ !(request()->routeIs('admin.template.list') || request()->routeIs('admin.template.view') ) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Template List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ !(request()->routeIs('admin.evaluation*')) ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !(request()->routeIs('admin.evaluation*')) ?: 'active' }}">
                        <i class="nav-icon fas fa-check-square"></i>
                        <p>
                            {{ __("Evaluations") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.evaluation.add') }}" class="nav-link {{ !(request()->routeIs('admin.evaluation.add')) ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Evaluation") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.evaluation.list') }}" class="nav-link {{ !(request()->routeIs('admin.evaluation.list') || request()->routeIs('admin.evaluation.view*') ) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Evaluation List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.response.list') }}" class="nav-link {{ !(request()->routeIs('admin.response*')) ?: 'active' }}">
                        <i class="nav-icon fas fa-reply"></i>
                        <p>{{ __("Responses") }}</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.profile.view') }}" class="nav-link {{ !(request()->routeIs('admin.profile*')) ?: 'active' }}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>{{ __("Profile") }}</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
