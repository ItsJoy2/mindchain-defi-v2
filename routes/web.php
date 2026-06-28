<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvestmentHistoryController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');


Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');


    Route::get('users/search',[UserController::class, 'searchUsers'])->name('users.search');
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('users/{user}',[UserController::class, 'update'])->name('users.update');
    Route::put('users/{user}/password',[UserController::class, 'updatePassword'])->name('users.password.update');
    Route::post('users/{user}/wallet-adjust',[UserController::class, 'adjustWallet'])->name('users.wallet.adjust');


    Route::get('transactions', [TransactionsController::class, 'index'])->name('transactions.index');
    Route::get( 'ambassador-history',[TransactionsController::class, 'ambassadorHistory'])->name('ambassador-history.index');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('angel', [SettingController::class, 'updateAngel'])->name('angel');
        Route::post('elite', [SettingController::class, 'updateElite'])->name('elite');
        Route::post('elite-v2', [SettingController::class, 'updateEliteV2'])->name('elite-v2');
        Route::post('mind-staking', [SettingController::class, 'updateMind'])->name('mind');
        Route::post('bmind-staking', [SettingController::class, 'updateBMind'])->name('bmind');
        Route::post('musd-staking', [SettingController::class, 'updateMusd'])->name('musd');
        Route::post('mkids-staking', [SettingController::class, 'updateMkids'])->name('mkids');

        Route::get('wallet-icons', [SettingController::class, 'walletIcons'])->name('wallet-icons');
        Route::post('wallet-icons', [SettingController::class, 'updateWalletIcons'])->name('wallet-icons.update');
    });
    
    //investment History
    Route::prefix('history')->name('history.')->group(function () {
        Route::get('angel-staking', [InvestmentHistoryController::class, 'angelStaking'])
        ->name('angel-staking');
    });
});
