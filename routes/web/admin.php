<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
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
