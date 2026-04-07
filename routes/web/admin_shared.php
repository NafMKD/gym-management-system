<?php

/**
 * Admin + reception: front desk, members, classes operations, POS — no invoices/payments/dashboard exports.
 * See admin_only.php for finance, inventory CRUD, staff, and class/package administration.
 */

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ClassBookingController;
use App\Http\Controllers\Admin\ClassScheduleController;
use App\Http\Controllers\Admin\GymClassController;
use App\Http\Controllers\Admin\MerchandiseCheckoutController;
use App\Http\Controllers\Admin\MembershipExtensionRequestController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

/**
 * Group For `/admin/users/*`
 */
Route::group([
    'prefix' => 'users',
    'as' => 'users.',
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
 * Packages: read-only for reception (membership flow needs list + package JSON).
 */
Route::group([
    'prefix' => 'packages',
    'as' => 'packages.',
], function () {
    Route::get('/list', [PackageController::class, 'index'])->name('list');
    Route::get('/view/{package}', [PackageController::class, 'show'])->name('view');
    Route::get('/list-data', [PackageController::class, 'getPackagesData'])->name('list.data');
    Route::get('/package-data', [PackageController::class, 'getPackageData'])->name('package.data');
});

/**
 * Gym classes: list + view for desk reference.
 */
Route::group([
    'prefix' => 'gym-classes',
    'as' => 'gym_classes.',
], function () {
    Route::get('/list', [GymClassController::class, 'index'])->name('list');
    Route::get('/view/{gym_class}', [GymClassController::class, 'show'])->name('view');
    Route::get('/list-data', [GymClassController::class, 'getListData'])->name('list.data');
});

/**
 * Class schedules: list + view.
 */
Route::group([
    'prefix' => 'class-schedules',
    'as' => 'class_schedules.',
], function () {
    Route::get('/list', [ClassScheduleController::class, 'index'])->name('list');
    Route::get('/view/{class_schedule}', [ClassScheduleController::class, 'show'])->name('view');
    Route::get('/list-data', [ClassScheduleController::class, 'getListData'])->name('list.data');
});

/**
 * Merchandise POS (unified invoice + payment in backend; reception may sell without seeing invoice module).
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
    'as' => 'memberships.',
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
    'as' => 'attendance.',
], function () {
    Route::get('/scan', [AttendanceController::class, 'showScanPage'])->name('scan');
    Route::post('/scan', [AttendanceController::class, 'recordAttendance'])->name('record');
});
