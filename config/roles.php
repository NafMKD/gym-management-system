<?php

/**
 * Role scopes (Chapter 3). Authoritative route access is enforced in routes/web.php via
 * user-access middleware + split route files (admin_shared.php vs admin_only.php).
 *
 * - admin: full application administration including invoices, payments, revenue, inventory CRUD, staff.
 * - reception: front desk — same AdminLTE desk routes as admin_shared (users, memberships, POS, attendance,
 *   class bookings, read-only packages / gym classes / schedules). No invoice, payment, or revenue routes.
 * - trainer: portal + trainer profile; session list + commission snapshot on dashboard.
 * - member: portal — membership snapshot, class booking, read-only booking history.
 */
return [
    'reception_hidden_admin_route_prefixes' => [
        'invoices',
        'payments',
        'products',
        'staffs',
        'audit-trail',
        'trainer-commissions',
        'export',
    ],
];
