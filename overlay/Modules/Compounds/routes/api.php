<?php

use Illuminate\Support\Facades\Route;
use Modules\Compounds\Http\Controllers\CompoundsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('compounds', CompoundsController::class)->names('compounds');
});
