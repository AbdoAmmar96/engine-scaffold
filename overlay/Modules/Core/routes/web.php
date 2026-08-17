<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\SettingsController;

/*
 |----------------------------------------------------------------------
 | Core Module — Admin Routes
 | (الملف ده بيتحمّل جوه مجموعة web من RouteServiceProvider بتاع الموديول)
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->group(function () {

    // ضيوف: تسجيل الدخول
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store'])->name('login.store');
    });

    // مسجّلين بدور admin
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('settings/{group}', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');

        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
    });
});
