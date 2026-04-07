<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// redirecting `/` route to `/login` route
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * Role-based dashboard entry (used after login, verification, etc.)
 */
Route::middleware('auth')->get('/dashboard', function () {
    $role = Auth::user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.home'),
        'trainer' => redirect()->route('trainer.home'),
        'reception' => redirect()->route('reception.home'),
        'member' => redirect()->route('member.home'),
        default => redirect()->route('login')->with('error', 'Invalid Role Type, please contact admin!'),
    };
})->name('dashboard');

/**
 * Admin + reception (front desk): members, memberships, POS, attendance, class ops — no invoice/payment UI routes.
 */
Route::group([
    'middleware' => [
        'auth',
        'user-access:admin,reception',
    ],
    'prefix' => 'admin',
    'as' => 'admin.',
], function () {
    require __DIR__.'/web/admin_shared.php';
});

/**
 * Admin-only: KPI dashboard, invoices, payments, revenue, inventory admin, staff, audits, package/class admin.
 */
Route::group([
    'middleware' => [
        'auth',
        'user-access:admin',
    ],
    'prefix' => 'admin',
    'as' => 'admin.',
], function () {
    require __DIR__.'/web/admin_only.php';
});

require __DIR__.'/web/portal.php';

require __DIR__.'/auth.php';