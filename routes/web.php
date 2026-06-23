<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');
