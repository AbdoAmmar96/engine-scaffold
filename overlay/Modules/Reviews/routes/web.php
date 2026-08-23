<?php

use Illuminate\Support\Facades\Route;
use Modules\Reviews\Http\Controllers\ReviewAdminController;

/*
 |----------------------------------------------------------------------
 | Reviews Module — إدارة آراء العملاء في اللوحة
 | (فورم «قيّم تجربتك» متسجّل في routes/web.php تحت مساحة الحساب،
 |  عشان ياخد بادئة اللغة وميدل وير الدخول)
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage content'])->group(function () {
    Route::get('reviews', [ReviewAdminController::class, 'index'])->name('reviews.index');
    Route::get('reviews/create', [ReviewAdminController::class, 'create'])->name('reviews.create');
    Route::post('reviews', [ReviewAdminController::class, 'store'])->name('reviews.store');
    Route::get('reviews/{id}/edit', [ReviewAdminController::class, 'edit'])->name('reviews.edit');
    Route::put('reviews/{id}', [ReviewAdminController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{id}', [ReviewAdminController::class, 'destroy'])->name('reviews.destroy');
});
