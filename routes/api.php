<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\EnhancedChatController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('otp-login', [AuthController::class, 'otpLogin']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    
    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
    });
});

// Protected Routes
Route::middleware('auth:api')->group(function () {
    
    // AI Chat Routes
    Route::prefix('ai')->group(function () {
        Route::post('text', [ChatController::class, 'textChat']);
        Route::post('image-analysis', [ChatController::class, 'imageAnalysis']);
        Route::post('text-to-speech', [EnhancedChatController::class, 'textToSpeech']);
        Route::post('speech-to-text', [EnhancedChatController::class, 'speechToText']);
        Route::post('generate-image', [EnhancedChatController::class, 'generateImage']);
        Route::post('generate-video', [EnhancedChatController::class, 'generateVideo']);
    });

    // Conversation Management Routes
    Route::prefix('conversations')->group(function () {
        Route::get('/', [ConversationController::class, 'index']);
        Route::get('/{id}', [ConversationController::class, 'show']);
        Route::post('/', [ConversationController::class, 'store']);
        Route::post('/{id}/archive', [ConversationController::class, 'archive']);
        Route::post('/{id}/unarchive', [ConversationController::class, 'unarchive']);
        Route::delete('/{id}', [ConversationController::class, 'destroy']);
        Route::get('/{id}/summary', [ConversationController::class, 'summary']);
    });

    // Subscription Routes
    Route::prefix('subscription')->group(function () {
        Route::get('status', [SubscriptionController::class, 'status']);
        Route::get('plans', [SubscriptionController::class, 'plans']);
        Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
        Route::post('cancel', [SubscriptionController::class, 'cancel']);
    });

    // Payment Routes
    Route::prefix('payment')->group(function () {
        Route::post('stripe', [PaymentController::class, 'stripePayment']);
        Route::post('paypal', [PaymentController::class, 'paypalPayment']);
        Route::get('history', [PaymentController::class, 'history']);
        Route::post('stripe/confirm', [PaymentController::class, 'confirmStripePayment']);
    });
});

// Webhook Routes (No authentication required)
Route::prefix('webhooks')->group(function () {
    Route::post('stripe', [PaymentController::class, 'stripeWebhook']);
    Route::post('paypal', [PaymentController::class, 'paypalWebhook']);
});

// Health Check
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'MedicoThink Backend API'
    ]);
});