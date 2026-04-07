<?php

/**
 * Front desk shell: top navigation and branding.
 * Routes must exist under admin_shared (user-access:admin,reception) or reception.* — no finance/staff-only paths.
 */

return [
    'brand_route' => 'reception.home',
    'brand_label' => 'Front desk',

    /**
     * Top navigation items (order = display order).
     *
     * @var list<array{route: string, label: string, icon?: string, active: array<int, string>}>
     */
    'nav' => [
        [
            'route' => 'reception.home',
            'label' => 'Desk home',
            'icon' => 'fa-home',
            'active' => ['reception.home'],
        ],
        [
            'route' => 'admin.users.list',
            'label' => 'Members',
            'icon' => 'fa-users',
            'active' => ['admin.users.*'],
        ],
        [
            'route' => 'admin.memberships.list',
            'label' => 'Memberships',
            'icon' => 'fa-id-card',
            'active' => ['admin.memberships.*'],
        ],
        [
            'route' => 'admin.merchandise.checkout',
            'label' => 'POS',
            'icon' => 'fa-cash-register',
            'active' => ['admin.merchandise.*'],
        ],
        [
            'route' => 'admin.class_bookings.list',
            'label' => 'Bookings',
            'icon' => 'fa-calendar-check',
            'active' => ['admin.class_bookings.*'],
        ],
        [
            'route' => 'admin.packages.list',
            'label' => 'Packages',
            'icon' => 'fa-box',
            'active' => ['admin.packages.*'],
        ],
        [
            'route' => 'admin.gym_classes.list',
            'label' => 'Classes',
            'icon' => 'fa-dumbbell',
            'active' => ['admin.gym_classes.*'],
        ],
        [
            'route' => 'admin.class_schedules.list',
            'label' => 'Schedule',
            'icon' => 'fa-calendar-alt',
            'active' => ['admin.class_schedules.*'],
        ],
        [
            'route' => 'admin.attendance.scan',
            'label' => 'Attendance',
            'icon' => 'fa-qrcode',
            'active' => ['admin.attendance.*'],
        ],
    ],
];
