<?php

use Illuminate\Support\Facades\Route;
use Modules\Properties\Http\Controllers\PropertyAdminController;

/*
 |----------------------------------------------------------------------
 | Properties Module — Admin CRUD
 | الشاشتين (Index/Form) عامّتين وبيتبنوا من schema الكنترولر.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage catalog|manage listings'])->group(function () {
    Route::get('properties',                [PropertyAdminController::class, 'index'])->name('properties.index');
    Route::get('properties/create',         [PropertyAdminController::class, 'create'])->name('properties.create');
    Route::post('properties',               [PropertyAdminController::class, 'store'])->name('properties.store');
    Route::get('properties/{id}/edit',      [PropertyAdminController::class, 'edit'])->name('properties.edit');
    Route::put('properties/{id}',           [PropertyAdminController::class, 'update'])->name('properties.update');
    Route::delete('properties/{id}',        [PropertyAdminController::class, 'destroy'])->name('properties.destroy');
});
