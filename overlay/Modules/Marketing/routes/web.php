<?php

use Illuminate\Support\Facades\Route;
use Modules\Marketing\Http\Controllers\FeaturedAdAdminController;
use Modules\Marketing\Http\Controllers\ReportController;

/*
 |----------------------------------------------------------------------
 | Marketing Module — المساحات الإعلانية
 | راوت تتبّع الضغطة نفسه في routes/web.php لأنه تحت بادئة اللغة.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:feature listings'])->group(function () {
    Route::get('featured-ads', [FeaturedAdAdminController::class, 'index'])->name('featured-ads.index');
    Route::get('featured-ads/create', [FeaturedAdAdminController::class, 'create'])->name('featured-ads.create');
    Route::post('featured-ads', [FeaturedAdAdminController::class, 'store'])->name('featured-ads.store');
    Route::get('featured-ads/{id}/edit', [FeaturedAdAdminController::class, 'edit'])->name('featured-ads.edit');
    Route::put('featured-ads/{id}', [FeaturedAdAdminController::class, 'update'])->name('featured-ads.update');
    Route::delete('featured-ads/{id}', [FeaturedAdAdminController::class, 'destroy'])->name('featured-ads.destroy');
});

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:view reports'])->group(function () {
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});
