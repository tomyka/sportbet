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

    Route::post('/auth/register', RegisterController::class)->middleware('throttle:register');

    Route::post('/auth/login', [SessionController::class, 'store'])
        ->middleware('throttle:login');

    // Public password-management routes
    Route::post('/auth/password/forgot', ForgotPasswordController::class)
        ->middleware('throttle:forgot-password');
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

    // ─── Public Tournament Routes (no auth required) ─────────────────────────
    Route::prefix('tournaments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TournamentController::class, 'index']);
        Route::get('/{tournament:slug}', [\App\Http\Controllers\Api\TournamentController::class, 'show']);
        Route::get('/{tournament:slug}/stages', [\App\Http\Controllers\Api\StageController::class, 'index']);
        Route::get('/{tournament:slug}/teams', [\App\Http\Controllers\Api\TeamController::class, 'index']);
        Route::get('/{tournament:slug}/fixtures', [\App\Http\Controllers\Api\FixtureController::class, 'index']);
    });

    // ─── Authenticated Prediction Routes ─────────────────────────────────────
    Route::prefix('tournaments/{tournament:slug}')
        ->middleware(['auth:sanctum', 'verified'])
        ->scopeBindings()
        ->group(function () {
            // Score predictions
            Route::get(
                'predictions/score',
                [\App\Http\Controllers\Api\ScorePredictionController::class, 'index']
            );
            Route::put(
                'fixtures/{fixture}/prediction',
                [\App\Http\Controllers\Api\ScorePredictionController::class, 'upsert']
            );
        });

    // ─── Admin Routes (auth:sanctum + verified) ──────────────────────────────
    Route::prefix('admin')
        ->middleware(['auth:sanctum', 'verified'])
        ->group(function () {
            // Tournament CRUD
            Route::post('tournaments', [\App\Http\Controllers\Api\Admin\TournamentAdminController::class, 'store']);
            Route::put('tournaments/{tournament:slug}', [\App\Http\Controllers\Api\Admin\TournamentAdminController::class, 'update']);
            Route::delete('tournaments/{tournament:slug}', [\App\Http\Controllers\Api\Admin\TournamentAdminController::class, 'destroy']);

            // Nested resources — scopeBindings() prevents cross-tournament writes
            Route::prefix('tournaments/{tournament:slug}')
                ->scopeBindings()
                ->group(function () {
                    Route::post('teams', [\App\Http\Controllers\Api\Admin\TeamAdminController::class, 'store']);
                    Route::put('teams/{team}', [\App\Http\Controllers\Api\Admin\TeamAdminController::class, 'update']);
                    Route::delete('teams/{team}', [\App\Http\Controllers\Api\Admin\TeamAdminController::class, 'destroy']);

                    Route::post('stages', [\App\Http\Controllers\Api\Admin\StageAdminController::class, 'store']);
                    Route::delete('stages/{stage}', [\App\Http\Controllers\Api\Admin\StageAdminController::class, 'destroy']);

                    Route::post('stages/{stage}/rounds', [\App\Http\Controllers\Api\Admin\RoundAdminController::class, 'store']);
                    Route::delete('stages/{stage}/rounds/{round}', [\App\Http\Controllers\Api\Admin\RoundAdminController::class, 'destroy']);

                    Route::post('groups', [\App\Http\Controllers\Api\Admin\GroupAdminController::class, 'store']);
                    Route::delete('groups/{group}', [\App\Http\Controllers\Api\Admin\GroupAdminController::class, 'destroy']);

                    Route::post('fixtures', [\App\Http\Controllers\Api\Admin\FixtureAdminController::class, 'store']);
                    Route::put('fixtures/{fixture}', [\App\Http\Controllers\Api\Admin\FixtureAdminController::class, 'update']);
                    Route::delete('fixtures/{fixture}', [\App\Http\Controllers\Api\Admin\FixtureAdminController::class, 'destroy']);
                });
        });
});
