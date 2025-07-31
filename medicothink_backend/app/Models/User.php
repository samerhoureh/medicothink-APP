@@ .. @@
 use Illuminate\Foundation\Auth\User as Authenticatable;
 use Illuminate\Notifications\Notifiable;
+use Tymon\JWTAuth\Contracts\JWTSubject;
+use Illuminate\Database\Eloquent\SoftDeletes;
 
-class User extends Authenticatable
+class User extends Authenticatable implements JWTSubject
 {
-    use HasFactory, Notifiable;
+    use HasFactory, Notifiable, SoftDeletes;
 
     protected $fillable = [
-        'name',
+        'username',
         'email',
+        'phone_number',
+        'age',
+        'city',
+        'nationality',
+        'specialization',
+        'education_level',
+        'profile_image',
         'password',
+        'email_verified_at',
+        'phone_verified_at',
+        'is_active',
+        'last_login_at',
     ];
@@ .. @@
     protected $casts = [
         'email_verified_at' => 'datetime',
+        'phone_verified_at' => 'datetime',
+        'last_login_at' => 'datetime',
+        'is_active' => 'boolean',
+        'age' => 'integer',
         'password' => 'hashed',
     ];
+
+    // JWT Methods
+    public function getJWTIdentifier()
+    {
+        return $this->getKey();
+    }
+
+    public function getJWTCustomClaims()
+    {
+        return [];
+    }
+
+    // Relationships
+    public function conversations()
+    {
+        return $this->hasMany(Conversation::class);
+    }
+
+    public function subscription()
+    {
+        return $this->hasOne(Subscription::class);
+    }
+
+    // Methods
+    public function hasActiveSubscription()
+    {
+        return $this->subscription && 
+               $this->subscription->is_active && 
+               $this->subscription->expires_at > now();
+    }