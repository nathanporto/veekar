<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentReminderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceHistoryController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok', 'app' => 'Veekar API']));

// Rotas públicas — sem auth
Route::get('/public/quotes/{token}', [QuoteController::class, 'publicShow']);
Route::post('/public/quotes/{token}/respond', [QuoteController::class, 'respond'])->middleware('throttle:10,1');
Route::get('/public/service/{token}', [ServiceHistoryController::class, 'publicShow'])->middleware('throttle:30,1');

// Stripe webhook — público, sem auth
Route::post('/webhook/stripe', [SubscriptionController::class, 'webhook']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,60');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->middleware('throttle:5,60');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->middleware('throttle:10,1');

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/accept-terms', [AuthController::class, 'acceptTerms']);
    });
});

// Rotas de assinatura — auth obrigatório, mas sem bloqueio por status expirado
Route::middleware('auth:api')->group(function () {
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout']);
    Route::get('/subscription/verify-session', [SubscriptionController::class, 'verifySession']);
    Route::post('/subscription/cancel', [SubscriptionController::class, 'cancel']);
});

Route::middleware(['auth:api', 'terms', 'subscription'])->group(function () {
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/recent-services', [DashboardController::class, 'recentServices']);
    Route::get('/dashboard/upcoming-returns', [DashboardController::class, 'upcomingReturns']);
    Route::get('/dashboard/upcoming-payment-reminders', [DashboardController::class, 'upcomingPaymentReminders']);
    Route::get('/dashboard/agenda', [DashboardController::class, 'agenda']);
    Route::get('/dashboard/kanban', [DashboardController::class, 'kanban']);

    Route::get('/payments', [PaymentController::class, 'index']);
    Route::apiResource('payment-reminders', PaymentReminderController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::apiResource('employees', EmployeeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/commissions', [CommissionController::class, 'index']);

    Route::get('/reports/financial', [ReportController::class, 'financial']);
    Route::get('/reports/financial/pdf', [ReportController::class, 'exportPdf']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/entrada', [ProductController::class, 'registerEntrada']);
    Route::post('/products/{product}/saida', [ProductController::class, 'registerSaida']);

    Route::apiResource('quotes', QuoteController::class)->only(['index', 'store', 'show', 'destroy']);

    Route::apiResource('customers', CustomerController::class);

    Route::get('/vehicles/search', [VehicleController::class, 'search']);
    Route::apiResource('vehicles', VehicleController::class);

    Route::get('vehicles/{vehicle}/service-histories', [ServiceHistoryController::class, 'index']);
    Route::post('vehicles/{vehicle}/service-histories', [ServiceHistoryController::class, 'store']);
    Route::get('vehicles/{vehicle}/service-histories/{serviceHistory}/checklist-pdf', [ServiceHistoryController::class, 'checklistPdf']);
    Route::put('vehicles/{vehicle}/service-histories/{serviceHistory}', [ServiceHistoryController::class, 'update']);
    Route::patch('vehicles/{vehicle}/service-histories/{serviceHistory}/status', [ServiceHistoryController::class, 'updateStatus']);
    Route::patch('vehicles/{vehicle}/service-histories/{serviceHistory}/payment-status', [ServiceHistoryController::class, 'updatePaymentStatus']);
    Route::get('vehicles/{vehicle}/service-histories/{serviceHistory}/client-summary-pdf', [ServiceHistoryController::class, 'clientSummaryPdf']);
    Route::delete('vehicles/{vehicle}/service-histories/{serviceHistory}', [ServiceHistoryController::class, 'destroy']);
});
