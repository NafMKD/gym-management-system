<?php

/**
 * Admin-only: financial visibility, inventory administration, staff, audits, KPI dashboard.
 */

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GymClassController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TrainerCommissionController;
use App\Http\Controllers\Admin\TrainerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/home', [DashboardController::class, 'index'])->name('home');
Route::get('/export/memberships-csv', [DashboardController::class, 'exportMembershipsCsv'])->name('export.memberships_csv');
Route::get('/export/payments-csv', [DashboardController::class, 'exportPaymentsCsv'])->name('export.payments_csv');

/**
 * Packages: create / edit / delete (read-only routes live in admin_shared.php).
 */
Route::group([
    'prefix' => 'packages',
    'as' => 'packages.',
], function () {
    Route::get('/add', [PackageController::class, 'create'])->name('add');
    Route::post('/add', [PackageController::class, 'store'])->name('store');
    Route::get('/edit/{package}', [PackageController::class, 'edit'])->name('edit');
    Route::post('/update/{package}', [PackageController::class, 'update'])->name('update');
    Route::get('/delete/{package}', [PackageController::class, 'destroy'])->name('delete');
});

/**
 * Gym classes: administration.
 */
Route::group([
    'prefix' => 'gym-classes',
    'as' => 'gym_classes.',
], function () {
    Route::get('/add', [GymClassController::class, 'create'])->name('add');
    Route::post('/add', [GymClassController::class, 'store'])->name('store');
    Route::get('/edit/{gym_class}', [GymClassController::class, 'edit'])->name('edit');
    Route::post('/update/{gym_class}', [GymClassController::class, 'update'])->name('update');
    Route::get('/delete/{gym_class}', [GymClassController::class, 'destroy'])->name('delete');
});

/**
 * Class schedules: administration.
 */
Route::group([
    'prefix' => 'class-schedules',
    'as' => 'class_schedules.',
], function () {
    Route::get('/add', [ClassScheduleController::class, 'create'])->name('add');
    Route::post('/add', [ClassScheduleController::class, 'store'])->name('store');
    Route::get('/edit/{class_schedule}', [ClassScheduleController::class, 'edit'])->name('edit');
    Route::post('/update/{class_schedule}', [ClassScheduleController::class, 'update'])->name('update');
    Route::get('/delete/{class_schedule}', [ClassScheduleController::class, 'destroy'])->name('delete');
});

/**
 * Group For `/admin/products/*` (inventory)
 */
Route::group([
    'prefix' => 'products',
    'as' => 'products.',
], function () {
    Route::get('/list', [ProductController::class, 'index'])->name('list');
    Route::get('/add', [ProductController::class, 'create'])->name('add');
    Route::post('/add', [ProductController::class, 'store'])->name('store');
    Route::get('/view/{product}', [ProductController::class, 'show'])->name('view');
    Route::get('/edit/{product}', [ProductController::class, 'edit'])->name('edit');
    Route::post('/update/{product}', [ProductController::class, 'update'])->name('update');
    Route::post('/stock/{product}', [ProductController::class, 'updateStock'])->name('stock');
    Route::get('/delete/{product}', [ProductController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [ProductController::class, 'getListData'])->name('list.data');
});

/**
 * Group For `/admin/audit-trail/*`
 */
Route::group([
    'prefix' => 'audit-trail',
    'as' => 'audit_trail.',
], function () {
    Route::get('/list', [AuditTrailController::class, 'index'])->name('list');
    Route::get('/view/{audit_trail}', [AuditTrailController::class, 'show'])->name('view');
    Route::get('/list-data', [AuditTrailController::class, 'getTrailsData'])->name('list.data');
});

/**
 * Group For `/admin/invoices/*`
 */
Route::group([
    'prefix' => 'invoices',
    'as' => 'invoices.',
], function () {
    Route::get('/list', [InvoiceController::class, 'index'])->name('list');
    Route::get('/list-data', [InvoiceController::class, 'getInvoicesData'])->name('list.data');
    Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');
    Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('send.email');
    Route::get('/view/{invoice}', [InvoiceController::class, 'show'])->name('view');
});

/**
 * Group For `/admin/payments/*`
 */
Route::group([
    'prefix' => 'payments',
    'as' => 'payments.',
], function () {
    Route::get('/list', [PaymentController::class, 'index'])->name('list');
    Route::get('/revenue/list', [PaymentController::class, 'revenueOverview'])->name('revenue.list');
    Route::get('/revenue/total', [PaymentController::class, 'getTotalRevenue'])->name('revenue.total');
    Route::get('/add/{invoice}', [PaymentController::class, 'create'])->name('add');
    Route::post('/add', [PaymentController::class, 'store'])->name('store');
    Route::post('/refund', [PaymentController::class, 'storeRefund'])->name('refund');
    Route::get('/view/{payment}', [PaymentController::class, 'show'])->name('view');
    Route::get('/list-data', [PaymentController::class, 'getPaymentsData'])->name('list.data');
    Route::post('/mark-failed', [PaymentController::class, 'markFailed'])->name('mark.failed');
    Route::post('/mark-completed', [PaymentController::class, 'markCompleted'])->name('mark.completed');
});

/**
 * Trainer commissions (session-derived + manual)
 */
Route::group([
    'prefix' => 'trainer-commissions',
    'as' => 'trainer_commissions.',
], function () {
    Route::get('/list', [TrainerCommissionController::class, 'index'])->name('list');
    Route::get('/add', [TrainerCommissionController::class, 'create'])->name('add');
    Route::post('/add', [TrainerCommissionController::class, 'store'])->name('store');
    Route::get('/list-data', [TrainerCommissionController::class, 'getListData'])->name('list.data');
});

/**
 * Group For `/admin/staffs/*`
 */
Route::group([
    'prefix' => 'staffs',
    'as' => 'staffs.',
], function () {
    Route::get('/add', [StaffController::class, 'create'])->name('add');
    Route::post('/add', [StaffController::class, 'store'])->name('store');
    Route::get('/list', [StaffController::class, 'index'])->name('list');
    Route::get('/view/{user}', [StaffController::class, 'show'])->name('view');
    Route::get('/edit/{user}', [StaffController::class, 'edit'])->name('edit');
    Route::post('/update/{user}', [StaffController::class, 'update'])->name('update');
    Route::get('/delete/{user}', [StaffController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [StaffController::class, 'getStaffData'])->name('list.data');
    Route::get('/trainer-profile/{user}', [TrainerProfileController::class, 'edit'])->name('trainer_profile.edit');
    Route::post('/trainer-profile/{user}', [TrainerProfileController::class, 'update'])->name('trainer_profile.update');
});
