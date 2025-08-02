@@ .. @@
         'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
     ],
 
+    'openai' => [
+        'api_key' => env('OPENAI_API_KEY'),
+        'model' => env('OPENAI_MODEL', 'gpt-4'),
+    ],
+
+    'elevenlabs' => [
+        'api_key' => env('ELEVENLABS_API_KEY'),
+    ],
+
+    'stability' => [
+        'api_key' => env('STABILITY_API_KEY'),
+    ],
+
+    'stripe' => [
+        'key' => env('STRIPE_KEY'),
+        'secret' => env('STRIPE_SECRET'),
+        'webhook' => [
+            'secret' => env('STRIPE_WEBHOOK_SECRET'),
+        ],
+    ],
+
+    'paypal' => [
+        'client_id' => env('PAYPAL_CLIENT_ID'),
+        'secret' => env('PAYPAL_SECRET'),
+        'settings' => [
+            'mode' => env('PAYPAL_MODE', 'sandbox'),
+            'http.ConnectionTimeOut' => 30,
+            'log.LogEnabled' => true,
+            'log.FileName' => storage_path() . '/logs/paypal.log',
+            'log.LogLevel' => 'ERROR'
+        ],
+    ],
+    'twilio' => [
+        'sid' => env('TWILIO_SID'),
+        'token' => env('TWILIO_TOKEN'),
+        'from' => env('TWILIO_FROM'),
+    ],
+
 ];