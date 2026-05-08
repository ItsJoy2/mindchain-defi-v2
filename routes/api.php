<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UsdtWalletController;
use App\Http\Controllers\MigrationController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');
Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('resend-verification', [AuthController::class, 'resendVerification']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

    Route::post('join-elite-club', [UsdtWalletController::class, 'joinElite']);

    Route::get('transactions', [TransactionController::class, 'index']);
});



Route::get('migrate-test-users-to-users', [MigrationController::class, 'migrateTestUsersToUsers']);
