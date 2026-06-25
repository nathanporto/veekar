<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => 'Veekar API']));

// Orçamentos públicos — sem auth
Route::get('/public/quotes/{token}', [QuoteController::class, 'publicShow']);
Route::post('/public/quotes/{token}/respond', [QuoteController::class, 'respond']);

// Stripe webhook — público, sem auth
Route::post('/webhook/stripe', [SubscriptionController::class, 'webhook']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Rotas de assinatura — auth obrigatório, mas sem bloqueio por status expirado
Route::middleware('auth:api')->group(function () {
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::get('/subscription/verify-session', [SubscriptionController::class, 'verifySession']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
});

Route::middleware(['auth:api', 'subscription'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-services', [DashboardController::class, 'recentServices']);

    Route::get('/reports/financial', [ReportController::class, 'financial']);
    Route::get('/reports/financial/pdf', [ReportController::class, 'exportPdf']);

    Route::apiResource('quotes', QuoteController::class)->only(['index', 'store', 'show', 'destroy']);

    Route::apiResource('customers', CustomerController::class);

    Route::get('/vehicles/search', [VehicleController::class, 'search']);
    Route::apiResource('vehicles', VehicleController::class);

    Route::get('vehicles/{vehicle}/service-histories', [ServiceHistoryController::class, 'index']);
    Route::post('vehicles/{vehicle}/service-histories', [ServiceHistoryController::class, 'store']);
    Route::delete('vehicles/{vehicle}/service-histories/{serviceHistory}', [ServiceHistoryController::class, 'destroy']);
});
