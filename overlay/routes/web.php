<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// الجذر → العربية (اللغة الافتراضية)
Route::redirect('/', '/ar');

// كل صفحات الموقع العام تحت بادئة اللغة: /ar/... و /en/...
Route::prefix('{locale}')
    ->whereIn('locale', ['ar', 'en'])
    ->middleware('locale')
    ->group(function () {

        Route::get('/', fn (string $locale) => Inertia::render('Site/Home', [
            'latestProperties' => array_slice(\App\Support\DemoContent::properties($locale), 0, 3),
            'latestCompounds'  => array_slice(\App\Support\DemoContent::compounds($locale), 0, 2),
        ]))->name('home');

        Route::get('/properties', fn (string $locale) => Inertia::render('Site/Properties', [
            'properties' => \App\Support\DemoContent::properties($locale),
        ]))->name('properties');

        Route::get('/compounds', fn (string $locale) => Inertia::render('Site/Compounds', [
            'compounds' => \App\Support\DemoContent::compounds($locale),
        ]))->name('compounds');

        Route::get('/about', fn () => Inertia::render('Site/About'))->name('about');

        Route::get('/contact', fn () => Inertia::render('Site/Contact'))->name('contact');

        // ← في المرحلة 4 بيانات العقارات والكمبوندات بتتبدل من DemoContent
        //   لموديلات Properties/Compounds الحقيقية بنفس الـ props بالظبط.

    });
