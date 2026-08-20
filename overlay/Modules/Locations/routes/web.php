<?php

use Illuminate\Support\Facades\Route;
use Modules\Locations\Http\Controllers\LocationAdminController;

/*
 |----------------------------------------------------------------------
 | Locations Module — Admin CRUD
 | الشاشتين (Index/Form) عامّتين وبيتبنوا من schema الكنترولر.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'permission:manage catalog'])->group(function () {
    Route::get('locations',                [LocationAdminController::class, 'index'])->name('locations.index');
    Route::get('locations/create',         [LocationAdminController::class, 'create'])->name('locations.create');
    Route::post('locations',               [LocationAdminController::class, 'store'])->name('locations.store');
    Route::get('locations/{id}/edit',      [LocationAdminController::class, 'edit'])->name('locations.edit');
    Route::put('locations/{id}',           [LocationAdminController::class, 'update'])->name('locations.update');
    Route::delete('locations/{id}',        [LocationAdminController::class, 'destroy'])->name('locations.destroy');
});
