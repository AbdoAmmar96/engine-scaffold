<?php

use Illuminate\Support\Facades\Route;
use Modules\Developers\Http\Controllers\DeveloperAdminController;

/*
 |----------------------------------------------------------------------
 | Developers Module — Admin CRUD
 | الشاشتين (Index/Form) عامّتين وبيتبنوا من schema الكنترولر.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage catalog'])->group(function () {
    Route::get('developers',                [DeveloperAdminController::class, 'index'])->name('developers.index');
    Route::get('developers/create',         [DeveloperAdminController::class, 'create'])->name('developers.create');
    Route::post('developers',               [DeveloperAdminController::class, 'store'])->name('developers.store');
    Route::get('developers/{id}/edit',      [DeveloperAdminController::class, 'edit'])->name('developers.edit');
    Route::put('developers/{id}',           [DeveloperAdminController::class, 'update'])->name('developers.update');
    Route::delete('developers/{id}',        [DeveloperAdminController::class, 'destroy'])->name('developers.destroy');
});
