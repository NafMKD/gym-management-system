<?php

/**
 * Front desk shell: top navigation and branding.
 * Routes must exist under admin_shared (user-access:admin,reception) or reception.* — no finance/staff-only paths.
 */

return [
    'brand_route' => 'reception.home',
    'brand_label' => 'Front desk',

    /**
     * Top navigation. Use a dropdown to avoid crowded horizontal links on small screens.
     *
     * @var list<array{type?: string, route?: string, label: string, active?: array<int, string>, items?: list<array{route: string, label: string, active: array<int, string>}>}>
     */
    'nav' => [
        [
            'type' => 'link',
            'route' => 'reception.home',
            'label' => 'Home',
            'active' => ['reception.home'],
        ],
        [
            'type' => 'dropdown',
            'label' => 'Desk',
            'active' => [
                'admin.users.*',
                'admin.memberships.*',
                'admin.merchandise.*',
                'admin.class_bookings.*',
                'admin.packages.*',
                'admin.gym_classes.*',
                'admin.class_schedules.*',
                'admin.attendance.*',
            ],
            'items' => [
                [
                    'route' => 'admin.users.list',
                    'label' => 'Members',
                    'active' => ['admin.users.*'],
                ],
                [
                    'route' => 'admin.memberships.list',
                    'label' => 'Memberships',
                    'active' => ['admin.memberships.*'],
                ],
                [
                    'route' => 'admin.merchandise.checkout',
                    'label' => 'POS',
                    'active' => ['admin.merchandise.checkout', 'admin.merchandise.checkout.store'],
                ],
                [
                    'route' => 'admin.merchandise.history',
                    'label' => 'Sales history',
                    'active' => ['admin.merchandise.history'],
                ],
                [
                    'route' => 'admin.class_bookings.list',
                    'label' => 'Bookings',
                    'active' => ['admin.class_bookings.*'],
                ],
                [
                    'route' => 'admin.packages.list',
                    'label' => 'Packages',
                    'active' => ['admin.packages.*'],
                ],
                [
                    'route' => 'admin.gym_classes.list',
                    'label' => 'Classes',
                    'active' => ['admin.gym_classes.*'],
                ],
                [
                    'route' => 'admin.class_schedules.list',
                    'label' => 'Schedule',
                    'active' => ['admin.class_schedules.*'],
                ],
                [
                    'route' => 'admin.attendance.scan',
                    'label' => 'Attendance',
                    'active' => ['admin.attendance.*'],
                ],
            ],
        ],
    ],
];
