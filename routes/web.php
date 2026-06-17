<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');


Route::get('/php-config', function () {
    return [
        'ini' => php_ini_loaded_file(),
        'scan_dir' => php_ini_scanned_files(),
    ];
});
