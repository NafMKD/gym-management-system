<?php

use Illuminate\Support\Facades\Route;

// redirecting `/` route to `/login` route
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * Admin Routes
 */
Route::group([
    'middleware' => [
        'auth',
        'user-access:admin'
    ],
    'prefix' => 'admin',
    'as' => 'admin.'
], function () {
    require_once 'web/admin.php';
});

require __DIR__.'/auth.php';