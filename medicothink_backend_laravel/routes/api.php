<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health Check
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'MedicoThink API is running',
        'timestamp' => now(),
        'version' => '1.0.0',
        'environment' => config('app.env'),
    ]);
});

// System Status Check
Route::get('/status', function () {
    $aiService = app(\App\Services\AiService::class);
    $payClickService = app(\App\Services\PayClickService::class);
    
    return response()->json([
        'success' => true,
        'message' => 'System status check',
        'data' => [
            'database' => 'connected',
            'ai_services' => $aiService->testConnection(),
            'payment_gateway' => $payClickService->testConnection(),
            'storage' => Storage::disk('public')->exists('test') ? 'connected' : 'available',
        ],
        'timestamp' => now(),
    ]);
});

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('otp-login', [AuthController::class, 'otpLogin']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
    });
});

// AI Features Routes
Route::prefix('ai')->middleware('auth:sanctum')->group(function () {
    Route::post('chat', [AiController::class, 'chat'])->middleware('subscription.check');
    Route::post('analyze-image', [AiController::class, 'analyzeImage'])->middleware('subscription.check');
    Route::post('generate-image', [AiController::class, 'generateImage'])->middleware('subscription.check');
    Route::post('generate-video', [AiController::class, 'generateVideo'])->middleware('subscription.check');
    Route::post('generate-flashcards', [AiController::class, 'generateFlashcards'])->middleware('subscription.check');
});

// Conversations Routes
Route::prefix('conversations')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [ConversationController::class, 'index']);
    Route::get('/{id}', [ConversationController::class, 'show']);
    Route::post('/', [ConversationController::class, 'store']);
    Route::post('/{id}/archive', [ConversationController::class, 'archive']);
    Route::post('/{id}/unarchive', [ConversationController::class, 'unarchive']);
    Route::delete('/{id}', [ConversationController::class, 'destroy']);
});

// Subscription Routes
Route::prefix('subscription')->middleware('auth:sanctum')->group(function () {
    Route::get('status', [SubscriptionController::class, 'status']);
    Route::get('plans', [SubscriptionController::class, 'plans']);
    Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
    Route::post('cancel', [SubscriptionController::class, 'cancel']);
});

// Payment Routes
Route::prefix('payment')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('history', [PaymentController::class, 'history']);
        Route::post('payclick', [PaymentController::class, 'payclick']);
    });
    
    // Webhook routes (no authentication required)
    Route::post('payclick/webhook', [PaymentController::class, 'payClickWebhook']);
});