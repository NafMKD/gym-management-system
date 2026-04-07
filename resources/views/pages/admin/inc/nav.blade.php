<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="#" class="brand-link">
        <center><span class="brand-text font-weight-light">{{ __("Admin Dashboard") }}</span></center>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('assets/dist/img/logo.JPG') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->getName() }}</a>
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
                    <a href="{{ route('admin.home') }}" class="nav-link  {{ !request()->routeIs('admin.home') ?: 'active' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __("Dashboard") }}</p>
                    </a>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.users*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.users*') ?: 'active' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            {{ __("Users") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.add') }}" class="nav-link {{ !request()->routeIs('admin.users.add') ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add User") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.users.list') }}" class="nav-link {{ !(request()->routeIs('admin.users.list') || request()->routeIs('admin.users.view') || request()->routeIs('admin.users.edit')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Users List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.packages*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.packages*') ?: 'active' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>
                            {{ __("Packages") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.packages.add') }}" class="nav-link {{ !request()->routeIs('admin.packages.add') ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Package") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.packages.list') }}" class="nav-link {{ !(request()->routeIs('admin.packages.list') || request()->routeIs('admin.packages.view') || request()->routeIs('admin.packages.edit')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Packages List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.memberships*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.memberships*') ?: 'active' }}">
                        <i class="nav-icon fas fa-user-tag"></i>
                        <p>
                            {{ __("Memberships") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.memberships.add') }}" class="nav-link {{ !request()->routeIs('admin.memberships.add') ?: 'active' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add Membership") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.memberships.list') }}" class="nav-link {{ !(request()->routeIs('admin.memberships.list') || request()->routeIs('admin.memberships.view') || request()->routeIs('admin.memberships.upgrade') || request()->routeIs('admin.memberships.renew')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Memberships List") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.memberships.extension_requests.list') }}" class="nav-link {{ request()->routeIs('admin.memberships.extension_requests*') ? 'active' : '' }}">
                                <i class="fas fa-calendar-plus nav-icon"></i>
                                <p>{{ __("Extension requests") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.gym_classes*') && !request()->routeIs('admin.class_schedules*') && !request()->routeIs('admin.class_bookings*') && !request()->routeIs('admin.trainer_commissions*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.gym_classes*') && !request()->routeIs('admin.class_schedules*') && !request()->routeIs('admin.class_bookings*') && !request()->routeIs('admin.trainer_commissions*') ?: 'active' }}">
                        <i class="nav-icon fas fa-dumbbell"></i>
                        <p>
                            {{ __("Classes") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.gym_classes.add') }}" class="nav-link {{ request()->routeIs('admin.gym_classes.add') ? 'active' : '' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add class") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.gym_classes.list') }}" class="nav-link {{ request()->routeIs('admin.gym_classes.*') && !request()->routeIs('admin.gym_classes.add') ? 'active' : '' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Gym classes") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.class_schedules.add') }}" class="nav-link {{ request()->routeIs('admin.class_schedules.add') ? 'active' : '' }}">
                                <i class="fas fa-calendar-plus nav-icon"></i>
                                <p>{{ __("Add session") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.class_schedules.list') }}" class="nav-link {{ request()->routeIs('admin.class_schedules.*') && !request()->routeIs('admin.class_schedules.add') ? 'active' : '' }}">
                                <i class="fas fa-calendar-alt nav-icon"></i>
                                <p>{{ __("Schedules") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.class_bookings.list') }}" class="nav-link {{ request()->routeIs('admin.class_bookings*') ? 'active' : '' }}">
                                <i class="fas fa-clipboard-list nav-icon"></i>
                                <p>{{ __("Bookings") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.trainer_commissions.list') }}" class="nav-link {{ request()->routeIs('admin.trainer_commissions*') ? 'active' : '' }}">
                                <i class="fas fa-coins nav-icon"></i>
                                <p>{{ __("Trainer commissions") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.products*') && !request()->routeIs('admin.merchandise*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.products*') && !request()->routeIs('admin.merchandise*') ?: 'active' }}">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>
                            {{ __("Inventory") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.products.add') }}" class="nav-link {{ request()->routeIs('admin.products.add') ? 'active' : '' }}">
                                <i class="fas fa-plus nav-icon"></i>
                                <p>{{ __("Add product") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.products.list') }}" class="nav-link {{ request()->routeIs('admin.products.list') || request()->routeIs('admin.products.view') || request()->routeIs('admin.products.edit') ? 'active' : '' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Products") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.merchandise.checkout') }}" class="nav-link {{ request()->routeIs('admin.merchandise*') ? 'active' : '' }}">
                                <i class="fas fa-cash-register nav-icon"></i>
                                <p>{{ __("Sell merchandise") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.invoices.list') }}" class="nav-link  {{ !request()->routeIs('admin.invoices*') ?: 'active' }}">
                        <i class="nav-icon fas fa-file-invoice"></i>
                        <p>{{ __("Invoices") }}</p>
                    </a>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.payments*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.payments*') ?: 'active' }}">
                        <i class="nav-icon fas fa-dollar-sign"></i>
                        <p>
                            {{ __("Payments") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.list') }}" class="nav-link {{ !(request()->routeIs('admin.payments.list') || request()->routeIs('admin.payments.view') || request()->routeIs('admin.payments.add')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Payments List") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.payments.revenue.list') }}" class="nav-link {{ !request()->routeIs('admin.payments.revenue.list') ?: 'active' }}">
                                <i class="fas fa-chart-line nav-icon"></i>
                                <p>{{ __("Revenue") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item  {{ !request()->routeIs('admin.staffs*') ?: 'menu-open' }}">
                    <a href="#" class="nav-link {{ !request()->routeIs('admin.staffs*') ?: 'active' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>
                            {{ __("Staff Management") }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.staffs.add') }}" class="nav-link {{ !request()->routeIs('admin.staffs.add') ?: 'active' }}">
                                <i class="fas fa-user-plus nav-icon"></i>
                                <p>{{ __("Add Staff") }}</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.staffs.list') }}" class="nav-link {{ !(request()->routeIs('admin.staffs.list') || request()->routeIs('admin.staffs.view') || request()->routeIs('admin.staffs.edit')) ?: 'active' }}">
                                <i class="fas fa-list nav-icon"></i>
                                <p>{{ __("Staff List") }}</p>
                            </a>
                        </li>
                    </ul>
                </li>                
                <li class="nav-item">
                    <a href="{{ route('admin.audit_trail.list') }}" class="nav-link  {{ !request()->routeIs('admin.audit_trail*') ?: 'active' }}">
                        <i class="nav-icon fas fa-history"></i>
                        <p>{{ __("Audit Trail") }}</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
