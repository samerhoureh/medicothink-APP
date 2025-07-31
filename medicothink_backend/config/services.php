@@ .. @@
     'ses' => [
         'key' => env('AWS_ACCESS_KEY_ID'),
         'secret' => env('AWS_SECRET_ACCESS_KEY'),
         'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
     ],
+
+    'twilio' => [
+        'sid' => env('TWILIO_SID'),
+        'token' => env('TWILIO_TOKEN'),
+        'from' => env('TWILIO_FROM'),
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
+    'openai' => [
+        'api_key' => env('OPENAI_API_KEY'),
+        'organization' => env('OPENAI_ORGANIZATION'),
+    ],