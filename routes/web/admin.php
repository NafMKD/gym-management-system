<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\ClassBookingController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\GymClassController;
use App\Http\Controllers\Admin\MerchandiseCheckoutController;
use App\Http\Controllers\Admin\MembershipExtensionRequestController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\TrainerCommissionController;
use App\Http\Controllers\Admin\TrainerProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\UserController;

Route::get('/home', [DashboardController::class, 'index'])->name('home');
Route::get('/export/memberships-csv', [DashboardController::class, 'exportMembershipsCsv'])->name('export.memberships_csv');
Route::get('/export/payments-csv', [DashboardController::class, 'exportPaymentsCsv'])->name('export.payments_csv');

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
 * Group For `/admin/gym-classes/*`
 */
Route::group([
    'prefix' => 'gym-classes',
    'as' => 'gym_classes.',
], function () {
    Route::get('/list', [GymClassController::class, 'index'])->name('list');
    Route::get('/add', [GymClassController::class, 'create'])->name('add');
    Route::post('/add', [GymClassController::class, 'store'])->name('store');
    Route::get('/view/{gym_class}', [GymClassController::class, 'show'])->name('view');
    Route::get('/edit/{gym_class}', [GymClassController::class, 'edit'])->name('edit');
    Route::post('/update/{gym_class}', [GymClassController::class, 'update'])->name('update');
    Route::get('/delete/{gym_class}', [GymClassController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [GymClassController::class, 'getListData'])->name('list.data');
});

/**
 * Group For `/admin/class-schedules/*`
 */
Route::group([
    'prefix' => 'class-schedules',
    'as' => 'class_schedules.',
], function () {
    Route::get('/list', [ClassScheduleController::class, 'index'])->name('list');
    Route::get('/add', [ClassScheduleController::class, 'create'])->name('add');
    Route::post('/add', [ClassScheduleController::class, 'store'])->name('store');
    Route::get('/view/{class_schedule}', [ClassScheduleController::class, 'show'])->name('view');
    Route::get('/edit/{class_schedule}', [ClassScheduleController::class, 'edit'])->name('edit');
    Route::post('/update/{class_schedule}', [ClassScheduleController::class, 'update'])->name('update');
    Route::get('/delete/{class_schedule}', [ClassScheduleController::class, 'destroy'])->name('delete');
    Route::get('/list-data', [ClassScheduleController::class, 'getListData'])->name('list.data');
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
 * Merchandise POS (unified invoice + payment)
 */
Route::group([
    'prefix' => 'merchandise',
    'as' => 'merchandise.',
], function () {
    Route::get('/checkout', [MerchandiseCheckoutController::class, 'create'])->name('checkout');
    Route::post('/checkout', [MerchandiseCheckoutController::class, 'store'])->name('checkout.store');
});

/**
 * Group For `/admin/class-bookings/*`
 */
Route::group([
    'prefix' => 'class-bookings',
    'as' => 'class_bookings.',
], function () {
    Route::get('/list', [ClassBookingController::class, 'index'])->name('list');
    Route::get('/add', [ClassBookingController::class, 'create'])->name('add');
    Route::post('/add', [ClassBookingController::class, 'store'])->name('store');
    Route::get('/view/{class_booking}', [ClassBookingController::class, 'show'])->name('view');
    Route::post('/cancel/{class_booking}', [ClassBookingController::class, 'cancel'])->name('cancel');
    Route::post('/mark-attended/{class_booking}', [ClassBookingController::class, 'markAttended'])->name('mark_attended');
    Route::post('/feedback/{class_booking}', [ClassBookingController::class, 'storeFeedback'])->name('feedback');
    Route::get('/list-data', [ClassBookingController::class, 'getListData'])->name('list.data');
});

/**
 * Group For `/admin/memberships/*`
 */
Route::group([
    'prefix' => 'memberships',
    'as' => 'memberships.'
], function () {
    Route::get('/renew/{membership}', [MembershipController::class, 'renew'])->name('renew');
    Route::get('/upgrade/{membership}', [MembershipController::class, 'showUpgrade'])->name('upgrade');
    Route::post('/upgrade/{membership}', [MembershipController::class, 'updateUpgrade'])->name('upgrade.update');

    Route::prefix('extension-requests')->as('extension_requests.')->group(function () {
        Route::get('/list', [MembershipExtensionRequestController::class, 'index'])->name('list');
        Route::get('/add', [MembershipExtensionRequestController::class, 'create'])->name('add');
        Route::post('/add', [MembershipExtensionRequestController::class, 'store'])->name('store');
        Route::get('/list-data', [MembershipExtensionRequestController::class, 'getListData'])->name('list.data');
        Route::get('/view/{membership_extension_request}', [MembershipExtensionRequestController::class, 'show'])->name('view');
        Route::post('/approve', [MembershipExtensionRequestController::class, 'approve'])->name('approve');
        Route::post('/reject', [MembershipExtensionRequestController::class, 'reject'])->name('reject');
    });

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
    'as' => 'payments.'
], function () {
    Route::get('/list', action: [PaymentController::class, 'index'])->name('list');
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
    'as' => 'staffs.'
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