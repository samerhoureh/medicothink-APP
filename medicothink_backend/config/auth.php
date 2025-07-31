@@ .. @@
     'defaults' => [
-        'guard' => 'web',
+        'guard' => 'api',
         'passwords' => 'users',
     ],
@@ .. @@
         'api' => [
-            'driver' => 'sanctum',
+            'driver' => 'jwt',
             'provider' => 'users',
         ],