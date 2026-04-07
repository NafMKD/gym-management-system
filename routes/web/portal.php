<?php

use App\Http\Controllers\Member\GymClassBookingController;
use App\Http\Controllers\Member\HomeController as MemberHomeController;
use App\Http\Controllers\Reception\HomeController as ReceptionHomeController;
use App\Http\Controllers\Trainer\HomeController as TrainerHomeController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => [
        'auth',
        'user-access:trainer',
    ],
    'prefix' => 'trainer',
    'as' => 'trainer.',
], function () {
    Route::get('/home', [TrainerHomeController::class, 'index'])->name('home');
});

Route::group([
    'middleware' => [
        'auth',
        'user-access:reception',
    ],
    'prefix' => 'reception',
    'as' => 'reception.',
], function () {
    Route::get('/home', [ReceptionHomeController::class, 'index'])->name('home');
});

Route::group([
    'middleware' => [
        'auth',
        'user-access:member',
    ],
    'prefix' => 'member',
    'as' => 'member.',
], function () {
    Route::get('/home', [MemberHomeController::class, 'index'])->name('home');
    Route::get('/classes', [GymClassBookingController::class, 'index'])->name('classes.index');
    Route::post('/classes/book', [GymClassBookingController::class, 'store'])->name('classes.book');
    Route::post('/classes/cancel/{class_booking}', [GymClassBookingController::class, 'cancel'])->name('classes.cancel');
});
