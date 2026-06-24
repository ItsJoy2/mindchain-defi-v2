<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin'])->name('admin.login');

// Route::get('account/verify/{token}', [AuthController::class, 'verifyEmail'])->name('user.verify');


Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('login', [AuthController::class, 'login'])->name('admin.login.submit');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    
    Route::get('users/search',[UserController::class, 'searchUsers'])->name('admin.users.search');
    Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::put('users/{user}',[UserController::class, 'update'])->name('admin.users.update');
    Route::put('users/{user}/password',[UserController::class, 'updatePassword'])->name('admin.users.password.update');

});
