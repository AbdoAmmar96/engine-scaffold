<?php

use Illuminate\Support\Facades\Route;
use Modules\Leads\Http\Controllers\LeadAdminController;

/*
 |----------------------------------------------------------------------
 | Leads Module — صندوق وارد الطلبات في اللوحة
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage leads'])->group(function () {
    Route::get('leads', [LeadAdminController::class, 'index'])->name('leads.index');
    Route::get('leads/create', [LeadAdminController::class, 'create'])->name('leads.create');
    Route::post('leads', [LeadAdminController::class, 'store'])->name('leads.store');
    Route::get('leads/{id}/edit', [LeadAdminController::class, 'edit'])->name('leads.edit');
    Route::put('leads/{id}', [LeadAdminController::class, 'update'])->name('leads.update');
    Route::delete('leads/{id}', [LeadAdminController::class, 'destroy'])->name('leads.destroy');
});
