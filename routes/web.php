<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');


Route::get('/server-check', function () {
    return [
        'php_version' => PHP_VERSION,
        'curl' => extension_loaded('curl'),
        'openssl' => extension_loaded('openssl'),
        'allow_url_fopen' => ini_get('allow_url_fopen'),
        'sapi' => php_sapi_name(),
    ];
});
