<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');


Route::get('/curl-check', function () {
    return [
        'curl_loaded' => extension_loaded('curl'),
        'curl_version' => function_exists('curl_version')
            ? curl_version()
            : 'not available',
        'curl_init' => function_exists('curl_init'),
    ];
});
