<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('health');

    Route::post('/auth/register', RegisterController::class)->middleware('throttle:60,1');

    Route::post('/auth/login', [SessionController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [SessionController::class, 'show']);
        Route::post('/auth/logout', [SessionController::class, 'destroy']);
    });
});
