<?php

use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');

Route::get('/gallery', [PublicSiteController::class, 'gallery'])->name('gallery');
Route::get('/trainers', [PublicSiteController::class, 'trainers'])->name('trainers');

/**
 * Role-based dashboard entry (used after login, verification, etc.)
 */
Route::middleware('auth')->get('/dashboard', function () {
    $role = Auth::user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.home'),
        'accountant' => redirect()->route('accountant.home'),
        'trainer' => redirect()->route('trainer.home'),
        'reception' => redirect()->route('reception.home'),
        'member' => redirect()->route('member.home'),
        default => redirect()->route('login')->with('error', 'Invalid Role Type, please contact admin!'),
    };
})->name('dashboard');

/**
 * Admin + reception + accountant:
 * reception keeps desk routes, accountant gets read-only report routes from the shared file.
 */
Route::group([
    'middleware' => [
        'auth',
        'user-access:admin,reception,accountant',
    ],
    'prefix' => 'admin',
    'as' => 'admin.',
], function () {
    require __DIR__.'/web/admin_shared.php';
});

/**
 * Admin + accountant:
 * accountant gets read-only finance / inventory reporting routes, while write routes stay narrowed inside the file.
 */
Route::group([
    'middleware' => [
        'auth',
        'user-access:admin,accountant',
    ],
    'prefix' => 'admin',
    'as' => 'admin.',
], function () {
    require __DIR__.'/web/admin_only.php';
});

require __DIR__.'/web/portal.php';

require __DIR__.'/auth.php';
