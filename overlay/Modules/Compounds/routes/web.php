<?php

use Illuminate\Support\Facades\Route;
use Modules\Compounds\Http\Controllers\CompoundAdminController;

/*
 |----------------------------------------------------------------------
 | Compounds Module — Admin CRUD
 | الشاشتين (Index/Form) عامّتين وبيتبنوا من schema الكنترولر.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage catalog|manage projects'])->group(function () {
    Route::get('compounds',                [CompoundAdminController::class, 'index'])->name('compounds.index');
    Route::get('compounds/create',         [CompoundAdminController::class, 'create'])->name('compounds.create');
    Route::post('compounds',               [CompoundAdminController::class, 'store'])->name('compounds.store');
    Route::get('compounds/{id}/edit',      [CompoundAdminController::class, 'edit'])->name('compounds.edit');
    Route::put('compounds/{id}',           [CompoundAdminController::class, 'update'])->name('compounds.update');
    Route::delete('compounds/{id}',        [CompoundAdminController::class, 'destroy'])->name('compounds.destroy');
});
