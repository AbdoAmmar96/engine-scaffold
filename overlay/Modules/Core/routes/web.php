<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\ActivityController;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\MediaController;
use Modules\Core\Http\Controllers\MenuAdminController;
use Modules\Core\Http\Controllers\SettingsController;
use Modules\Core\Http\Controllers\UserAdminController;

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
        // 5 محاولات في الدقيقة لكل IP — يوقف تخمين كلمات المرور
        Route::post('login', [AuthController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    // موظف مسجّل بيدخل اللوحة، وكل قسم بعد كده على صلاحيته
    Route::middleware(['auth', 'staff'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

        Route::middleware('permission:manage settings')->group(function () {
            Route::get('settings/{group}', [SettingsController::class, 'edit'])->name('settings.edit');
            Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
        });

        // مكتبة الميديا — الشاشة + JSON للـ MediaPicker.
        // صلاحية مستقلة عن المحتوى: مدخل البيانات محتاج يرفع صور وحدات
        // من غير ما ياخد المدونة والقوايم معاها.
        Route::middleware('permission:manage media')->group(function () {
            Route::get('media', [MediaController::class, 'index'])->name('media.index');
            Route::get('media/files', [MediaController::class, 'list'])->name('media.list');
            Route::post('media/files', [MediaController::class, 'store'])->name('media.store');
            Route::delete('media/files', [MediaController::class, 'destroy'])->name('media.destroy');
        });

        Route::middleware('permission:manage content')->group(function () {
            // قوائم الهيدر والفوتر
            Route::get('menus', [MenuAdminController::class, 'index'])->name('menus.index');
            Route::get('menus/create', [MenuAdminController::class, 'create'])->name('menus.create');
            Route::post('menus', [MenuAdminController::class, 'store'])->name('menus.store');
            Route::get('menus/{id}/edit', [MenuAdminController::class, 'edit'])->name('menus.edit');
            Route::put('menus/{id}', [MenuAdminController::class, 'update'])->name('menus.update');
            Route::delete('menus/{id}', [MenuAdminController::class, 'destroy'])->name('menus.destroy');
        });

        // سجل النشاط — قراءة بس، ومع اللي بيدير المستخدمين (أدمن وسوبر أدمن)
        Route::middleware('permission:manage users')->group(function () {
            Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
        });

        // إدارة مستخدمي اللوحة (إضافة/حذف حساب وتغيير كلمة المرور والدور)
        Route::middleware('permission:manage users|manage roles')->group(function () {
            Route::get('users', [UserAdminController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserAdminController::class, 'create'])->name('users.create');
            Route::post('users', [UserAdminController::class, 'store'])->name('users.store');
            Route::get('users/{id}/edit', [UserAdminController::class, 'edit'])->name('users.edit');
            Route::put('users/{id}', [UserAdminController::class, 'update'])->name('users.update');
            Route::delete('users/{id}', [UserAdminController::class, 'destroy'])->name('users.destroy');
        });

    });
});
