<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditTrailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;

Route::get('/home', [DashboardController::class, 'index'])->name('home');


/**
 * Group For `/admin/users/*`
 */
Route::group([
    'prefix' => 'users',
    'as' => 'users.'
], function () {
    Route::get('/add', [UserController::class, 'create'])->name('add');
    Route::post('/add', [UserController::class, 'store'])->name('store');
    Route::get('/list', [UserController::class, 'index'])->name('list');
    Route::get('/view/{user}', [UserController::class, 'show'])->name('view');
    Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
    Route::post('/update/{user}', [UserController::class, 'update'])->name('update');
    Route::get('/delete/{user}', [UserController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [UserController::class, 'getUsersData'])->name('list.data');
});

/**
 * Group For `/admin/packages/*`
 */
Route::group([
    'prefix' => 'packages',
    'as' => 'packages.'
], function () {
    Route::get('/add', [PackageController::class, 'create'])->name('add');
    Route::post('/add', [PackageController::class, 'store'])->name('store');
    Route::get('/list', [PackageController::class, 'index'])->name('list');
    Route::get('/view/{package}', [PackageController::class, 'show'])->name('view');
    Route::get('/edit/{package}', [PackageController::class, 'edit'])->name('edit');
    Route::post('/update/{package}', [PackageController::class, 'update'])->name('update');
    Route::get('/delete/{package}', [PackageController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [PackageController::class, 'getPackagesData'])->name('list.data');
    Route::get('/package-data', [PackageController::class, 'getPackageData'])->name('package.data');
});

/**
 * Group For `/admin/memberships/*`
 */
Route::group([
    'prefix' => 'memberships',
    'as' => 'memberships.'
], function () {
    Route::get('/add', [MembershipController::class, 'create'])->name('add');
    Route::post('/add', [MembershipController::class, 'store'])->name('store');
    Route::get('/list', [MembershipController::class, 'index'])->name('list');
    Route::get('/view/{membership}', [MembershipController::class, 'show'])->name('view');
    Route::get('/{membership}/print-id-card', [MembershipController::class, 'printIdCard'])->name('print_id_card');
    Route::get('/list-data', [MembershipController::class, 'getMembershipsData'])->name('list.data');
    Route::post('/cancel', [MembershipController::class, 'cancel'])->name('cancel');
    Route::post('/change-status', [MembershipController::class, 'changeStatus'])->name('change.status');
});

/**
 * Group For `/admin/attendance/*`
 */
Route::group([
    'prefix' => 'attendance',
    'as' => 'attendance.'
], function () {
    Route::get('/scan', [AttendanceController::class, 'showScanPage'])->name('scan');
    Route::post('/scan', [AttendanceController::class, 'recordAttendance'])->name('record');
});

/**
 * Group For `/admin/audit-trail/*`
 */
Route::group([
    'prefix' => 'audit-trail',
    'as' => 'audit_trail.'
], function () {
    Route::get('/list', action: [AuditTrailController::class, 'index'])->name('list');
    Route::get('/view/{audit_trail}', [AuditTrailController::class, 'show'])->name('view');
    Route::get('/list-data', [AuditTrailController::class, 'getTrailsData'])->name('list.data');
});

/**
 * Group For `/admin/invoices/*`
 */
Route::group([
    'prefix' => 'invoices',
    'as' => 'invoices.'
], function () {
    Route::get('/list', action: [InvoiceController::class, 'index'])->name('list');
    Route::get('/view/{invoice}', [InvoiceController::class, 'show'])->name('view');
    Route::get('/list-data', [InvoiceController::class, 'getInvoicesData'])->name('list.data');
});

/**
 * Group For `/admin/payments/*`
 */
Route::group([
    'prefix' => 'payments',
    'as' => 'payments.'
], function () {
    Route::get('/list', action: [PaymentController::class, 'index'])->name('list');
    Route::get('/revenue/list', [PaymentController::class, 'revenueOverview'])->name('revenue.list');
    Route::get('/add/{invoice}', [PaymentController::class, 'create'])->name('add');
    Route::post('/add', [PaymentController::class, 'store'])->name('store');
    Route::get('/view/{payment}', [PaymentController::class, 'show'])->name('view');
    Route::get('/list-data', [PaymentController::class, 'getPaymentsData'])->name('list.data');
    Route::post('/mark-failed', [PaymentController::class, 'markFailed'])->name('mark.failed');
    Route::post('/mark-completed', [PaymentController::class, 'markCompleted'])->name('mark.completed');
});