<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ConversationController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\EnhancedChatController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/otp-login', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
    // Chat
    Route::post('/ai/chat', [ChatController::class, 'sendMessage']);
    Route::post('/ai/analyze-image', [ChatController::class, 'analyzeImage']);
    
    // Enhanced AI Features
    Route::post('/ai/text', [EnhancedChatController::class, 'sendTextMessage']);
    Route::post('/ai/image-analysis', [EnhancedChatController::class, 'analyzeImage']);
    Route::post('/ai/text-to-speech', [EnhancedChatController::class, 'textToSpeech']);
    Route::post('/ai/speech-to-text', [EnhancedChatController::class, 'speechToText']);
    Route::post('/ai/generate-image', [EnhancedChatController::class, 'generateImage']);
    Route::post('/ai/generate-video', [EnhancedChatController::class, 'generateVideo']);
    
    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::post('/conversations/archive', [ConversationController::class, 'archive']);
    Route::post('/conversations/unarchive', [ConversationController::class, 'unarchive']);
    Route::delete('/conversations/{id}', [ConversationController::class, 'destroy']);
    Route::get('/conversations/{id}/summary', [ConversationController::class, 'summary']);
    
    // Subscriptions
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);
    Route::get('/subscription/plans', [SubscriptionController::class, 'plans']);
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe']);
    
    // Payments
    Route::post('/payment/stripe', [PaymentController::class, 'processStripePayment']);
    Route::post('/payment/paypal', [PaymentController::class, 'processPayPalPayment']);
    Route::get('/payment/history', [PaymentController::class, 'getPaymentHistory']);
});