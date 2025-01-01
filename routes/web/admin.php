<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/home', [DashboardController::class, 'index'])->name('home');
