@@ .. @@
 <?php

 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Route;
+use App\Http\Controllers\AuthController;
+use App\Http\Controllers\ChatController;
+use App\Http\Controllers\SubscriptionController;

-Route::get('/user', function (Request $request) {
-    return $request->user();
-})->middleware('auth:sanctum');
+// Public routes
+Route::prefix('auth')->group(function () {
+    Route::post('register', [AuthController::class, 'register']);
+    Route::post('login', [AuthController::class, 'login']);
+    Route::post('otp-login', [AuthController::class, 'otpLogin']);
+    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
+    Route::post('refresh', [AuthController::class, 'refresh']);
+});
+
+// Protected routes
+Route::middleware('auth:api')->group(function () {
+    // Auth routes
+    Route::prefix('auth')->group(function () {
+        Route::post('logout', [AuthController::class, 'logout']);
+        Route::get('profile', [AuthController::class, 'profile']);
+    });
+
+    // Chat routes
+    Route::prefix('ai')->group(function () {
+        Route::post('chat', [ChatController::class, 'sendMessage']);
+        Route::post('analyze-image', [ChatController::class, 'analyzeImage']);
+    });
+
+    // Conversation routes
+    Route::prefix('conversations')->group(function () {
+        Route::get('/', [ChatController::class, 'getConversations']);
+        Route::get('/{conversationId}', [ChatController::class, 'getConversation']);
+        Route::post('archive', [ChatController::class, 'archiveConversation']);
+        Route::post('unarchive', [ChatController::class, 'unarchiveConversation']);
+        Route::delete('/{conversationId}', [ChatController::class, 'deleteConversation']);
+        Route::get('/{conversationId}/summary', [ChatController::class, 'getConversationSummary']);
+    });
+
+    // Subscription routes
+    Route::prefix('subscription')->group(function () {
+        Route::get('status', [SubscriptionController::class, 'getStatus']);
+        Route::get('plans', [SubscriptionController::class, 'getPlans']);
+        Route::post('subscribe', [SubscriptionController::class, 'subscribe']);
+        Route::post('cancel', [SubscriptionController::class, 'cancel']);
+    });
+});
+
+// Health check
+Route::get('health', function () {
+    return response()->json([
+        'success' => true,
+        'message' => 'MedicoThink API is running',
+        'timestamp' => now(),
+        'version' => '1.0.0'
+    ]);
+});