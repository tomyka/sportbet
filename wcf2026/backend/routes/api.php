<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class)->name('health');

    Route::post('/auth/register', RegisterController::class)->middleware('throttle:60,1');

    Route::post('/auth/login', [SessionController::class, 'store'])
        ->middleware('throttle:5,1');

    // Public password-management routes
    Route::post('/auth/password/forgot', ForgotPasswordController::class)
        ->middleware('throttle:3,60');
    Route::post('/auth/password/reset', ResetPasswordController::class)
        ->middleware('throttle:6,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [SessionController::class, 'show']);
        Route::post('/auth/logout', [SessionController::class, 'destroy']);

        // Email verification (auth required, not yet requiring verified)
        Route::get('/auth/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/auth/email/resend', [EmailVerificationController::class, 'resend'])
            ->middleware('throttle:6,1');

        // Password change (requires current password)
        Route::post('/auth/password', PasswordController::class);

        // Profile update
        Route::patch('/auth/me', ProfileController::class);
    });
});
