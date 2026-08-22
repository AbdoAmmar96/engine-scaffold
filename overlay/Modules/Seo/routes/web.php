<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\LandingPageAdminController;
use Modules\Seo\Http\Controllers\SitemapController;

/*
 |----------------------------------------------------------------------
 | Seo Module — خريطة الموقع و robots
 | (بره بادئة اللغة: العناكب بتدوّر عليهم في الجذر)
 |----------------------------------------------------------------------
 */

Route::middleware('web')->group(function () {
    Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');
});

/*
 |----------------------------------------------------------------------
 | Seo Module — تحرير صفحات الهبوط
 | التوليد من الأمر seo:landing-pages — الشاشة دي للنصوص والميتا.
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage content'])->group(function () {
    Route::get('landing-pages',           [LandingPageAdminController::class, 'index'])->name('landing-pages.index');
    Route::get('landing-pages/create',    [LandingPageAdminController::class, 'create'])->name('landing-pages.create');
    Route::post('landing-pages',          [LandingPageAdminController::class, 'store'])->name('landing-pages.store');
    Route::get('landing-pages/{id}/edit', [LandingPageAdminController::class, 'edit'])->name('landing-pages.edit');
    Route::put('landing-pages/{id}',      [LandingPageAdminController::class, 'update'])->name('landing-pages.update');
    Route::delete('landing-pages/{id}',   [LandingPageAdminController::class, 'destroy'])->name('landing-pages.destroy');
});
