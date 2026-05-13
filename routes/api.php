<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\EliteClubController;
use App\Http\Controllers\API\MindWalletController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\TransferController;
use Illuminate\Support\Facades\Route;


Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register'])->name('register');

Route::get('verify-email/{token}', [AuthController::class, 'verifyEmail']);
Route::post('resend-verification', [AuthController::class, 'resendVerification']);

Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

Route::post('account/verify', [AuthController::class, 'verifyEmail']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('change-password', [AuthController::class, 'changePassword']);

    Route::get('affiliates-list', [AuthController::class, 'affiliatesList']);

    Route::get('dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

    // ELITE CLUB
    Route::get('elite-club', [EliteClubController::class, 'index']);
    Route::post('join-elite-club', [EliteClubController::class, 'joinElite']);
    Route::post('join-elite-v2', [EliteClubController::class, 'joinEliteV2']);

    // MIND STAKING
    Route::get('mind-staking', [MindWalletController::class, 'mindStaking']);
    Route::post('mind-staking/store', [MindWalletController::class, 'mindStakingStore']);
    Route::post('mind-staking-marge', [MindWalletController::class, 'mindStakingMarge']);
    Route::get('mind-staking-history',[MindWalletController::class, 'mindStakingHistory']);

    Route::post('transfer/send-otp', [TransferController::class, 'sendTransferOtp']);
    Route::post('transfer/confirm', [TransferController::class, 'confirmTransfer']);
    Route::post('transfer/resend-otp', [TransferController::class, 'resendTransferOtp']);

    Route::get('transactions', [TransactionController::class, 'index']);
});


