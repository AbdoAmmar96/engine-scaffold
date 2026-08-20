<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\Http\Controllers\PostAdminController;

/*
 |----------------------------------------------------------------------
 | Blog Module — إدارة المقالات في اللوحة
 | (صفحات المدونة العامة متسجّلة في routes/web.php تحت بادئة اللغة)
 |----------------------------------------------------------------------
 */

Route::prefix('admin')->name('admin.')->middleware(['web', 'auth', 'staff', 'permission:manage content'])->group(function () {
    Route::get('posts',           [PostAdminController::class, 'index'])->name('posts.index');
    Route::get('posts/create',    [PostAdminController::class, 'create'])->name('posts.create');
    Route::post('posts',          [PostAdminController::class, 'store'])->name('posts.store');
    Route::get('posts/{id}/edit', [PostAdminController::class, 'edit'])->name('posts.edit');
    Route::put('posts/{id}',      [PostAdminController::class, 'update'])->name('posts.update');
    Route::delete('posts/{id}',   [PostAdminController::class, 'destroy'])->name('posts.destroy');
});
