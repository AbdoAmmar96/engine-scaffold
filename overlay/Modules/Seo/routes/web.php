<?php

use Illuminate\Support\Facades\Route;
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
