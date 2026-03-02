<?php

use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ─── Public Routes ────────────────────────────────────────────────
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    Route::get('/invitations/{token}/accept', [TeamController::class, 'acceptInvitation']);

    // ─── Authenticated Routes (no verified required) ─────────────────
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationNotification']);
        Route::post('/2fa/enable', [AuthController::class, 'enableTwoFactor']);
        Route::post('/2fa/disable', [AuthController::class, 'disableTwoFactor']);
    });

    // ─── Authenticated + Verified Routes ──────────────────────────────
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {

        // ─── AI ───────────────────────────────────────────────────────
        Route::prefix('ai')->group(function () {
            Route::post('/generate',       [AiController::class, 'generate']);
            Route::post('/generate-async', [AiController::class, 'generateAsync']);
            Route::get('/usage',           [AiController::class, 'usage']);
        });

        // ─── Team ─────────────────────────────────────────────────────
        Route::prefix('team')->group(function () {
            Route::get('/',                   [TeamController::class, 'show']);
            Route::put('/',                   [TeamController::class, 'update']);
            Route::post('/invite',            [TeamController::class, 'inviteMember']);
            Route::delete('/members/{userId}', [TeamController::class, 'removeMember']);
        });

        // ─── Billing ──────────────────────────────────────────────────
        Route::prefix('billing')->group(function () {
            Route::get('/plans',     [BillingController::class, 'plans']);
            Route::post('/subscribe', [BillingController::class, 'subscribe']);
            Route::post('/cancel',   [BillingController::class, 'cancel']);
            Route::post('/resume',   [BillingController::class, 'resume']);
            Route::get('/invoices',  [BillingController::class, 'invoices']);
        });
    });

    // ─── Stripe Webhooks (no auth) ────────────────────────────────────
    Route::post('/webhooks/stripe', [BillingController::class, 'webhook']);
});