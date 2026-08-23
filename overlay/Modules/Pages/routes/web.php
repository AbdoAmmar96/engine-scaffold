<?php

use Illuminate\Support\Facades\Route;
use Modules\Pages\Http\Controllers\PageAdminController;

/*
 |----------------------------------------------------------------------
 | Pages Module — إدارة صفحات المحتوى في اللوحة
 | (عرض الصفحة نفسها متسجّل في routes/web.php آخر مجموعة اللغة،
 |  عشان أي مسار حقيقي يغلب الـ catch-all)
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage content'])->group(function () {
    Route::get('pages', [PageAdminController::class, 'index'])->name('pages.index');
    Route::get('pages/create', [PageAdminController::class, 'create'])->name('pages.create');
    Route::post('pages', [PageAdminController::class, 'store'])->name('pages.store');
    Route::get('pages/{id}/edit', [PageAdminController::class, 'edit'])->name('pages.edit');
    Route::put('pages/{id}', [PageAdminController::class, 'update'])->name('pages.update');
    Route::delete('pages/{id}', [PageAdminController::class, 'destroy'])->name('pages.destroy');
});
