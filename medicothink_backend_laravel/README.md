# MedicoThink Backend - Laravel API

مشروع MedicoThink Backend هو واجهة خلفية متكاملة مبنية بـ Laravel لدعم تطبيق الذكاء الاصطناعي الطبي MedicoThink.

## 🚀 الميزات الرئيسية

### 🔐 نظام التوثيق والمستخدمين
- تسجيل الدخول بالبريد الإلكتروني وكلمة المرور
- تسجيل الدخول برمز OTP عبر الهاتف
- إدارة ملفات المستخدمين الشخصية
- نظام الأدوار والصلاحيات

### 🤖 خدمات الذكاء الاصطناعي
- محادثات نصية مع الذكاء الاصطناعي
- تحليل الصور الطبية
- توليد الصور
- توليد الفيديوهات
- إنشاء البطاقات التعليمية

### 💬 إدارة المحادثات
- حفظ وتنظيم المحادثات
- أرشفة المحادثات
- البحث في المحادثات
- دعم أنواع مختلفة من الرسائل

### 💳 نظام الاشتراكات والمدفوعات
- باقات اشتراك متعددة
- دفع عبر PayClick
- تتبع الاستخدام وفرض الحدود
- إدارة التجديد التلقائي

## 📋 متطلبات النظام

- PHP 8.1 أو أحدث
- Composer
- MySQL 8.0 أو أحدث
- Laravel 10.x

## 🛠️ التثبيت والإعداد

### 1. استنساخ المشروع
```bash
git clone https://github.com/your-username/medicothink-backend.git
cd medicothink-backend
```

### 2. تثبيت التبعيات
```bash
composer install
```

### 3. إعداد ملف البيئة
```bash
cp .env.example .env
```

قم بتحديث المتغيرات في ملف `.env`:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicothink
DB_USERNAME=root
DB_PASSWORD=your_password

# AI Services
OPENAI_API_KEY=your_openai_key
GEMINI_API_KEY=your_gemini_key

# PayClick
PAYCLICK_API_KEY=your_payclick_key
PAYCLICK_SECRET_KEY=your_payclick_secret
PAYCLICK_WEBHOOK_URL=https://yourdomain.com/api/payment/payclick/webhook

# SMS Service (Generic)
SMS_API_KEY=your_sms_key
SMS_API_URL=https://sms-provider.com/api
SMS_FROM_NUMBER=+1234567890

# Twilio SMS Service (Alternative)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=+1234567890
```

### 4. توليد مفتاح التطبيق
```bash
php artisan key:generate
```

### 5. تشغيل الهجرات والبذور
```bash
php artisan migrate --seed
```

### 6. إنشاء رابط التخزين
```bash
php artisan storage:link
```

### 7. تثبيت Laravel Sanctum
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

## 🚀 تشغيل المشروع

### تشغيل الخادم المحلي
```bash
php artisan serve
```

سيكون التطبيق متاحاً على: `http://localhost:8000`

### اختبار الاتصال
```bash
# اختبار صحة النظام
curl http://localhost:8000/api/health

# اختبار حالة الخدمات
curl http://localhost:8000/api/status
```

### تشغيل الطوابير (اختياري)
```bash
php artisan queue:work
```

## 🧪 الاختبارات

### تشغيل الاختبارات
```bash
# تشغيل جميع الاختبارات
php artisan test

# تشغيل اختبارات محددة
php artisan test --filter ApiTest

# تشغيل الاختبارات مع التغطية
php artisan test --coverage
```

### استخدام Postman Collection
1. استيراد ملف `tests/postman_collection.json` في Postman
2. تحديث متغير `base_url` إلى `http://localhost:8000/api`
3. تشغيل الاختبارات تلقائياً أو يدوياً

## 📚 توثيق API

### نقاط النهاية الرئيسية

#### 🔍 **النظام**
```
GET  /api/health                 - فحص صحة النظام
GET  /api/status                 - حالة الخدمات المتصلة
```

#### 🔐 التوثيق
```
POST /api/auth/register          - تسجيل مستخدم جديد
POST /api/auth/login             - تسجيل الدخول
POST /api/auth/otp-login         - إرسال رمز OTP
POST /api/auth/verify-otp        - التحقق من رمز OTP
POST /api/auth/logout            - تسجيل الخروج
POST /api/auth/refresh           - تجديد الرمز المميز
GET  /api/auth/me                - معلومات المستخدم الحالي
POST /api/auth/update-profile    - تحديث الملف الشخصي
```

#### 🤖 الذكاء الاصطناعي
```
POST /api/ai/chat                - محادثة نصية
POST /api/ai/analyze-image       - تحليل صورة
POST /api/ai/generate-image      - توليد صورة
POST /api/ai/generate-video      - توليد فيديو
POST /api/ai/generate-flashcards - إنشاء بطاقات تعليمية
```

#### 💬 المحادثات
```
GET    /api/conversations        - قائمة المحادثات
GET    /api/conversations/{id}   - تفاصيل محادثة
POST   /api/conversations        - إنشاء محادثة جديدة
POST   /api/conversations/{id}/archive   - أرشفة محادثة
POST   /api/conversations/{id}/unarchive - إلغاء أرشفة محادثة
DELETE /api/conversations/{id}   - حذف محادثة
```

#### 💳 الاشتراكات
```
GET  /api/subscription/status    - حالة الاشتراك
GET  /api/subscription/plans     - قائمة الباقات
POST /api/subscription/subscribe - الاشتراك في باقة
POST /api/subscription/cancel    - إلغاء الاشتراك
```

#### 💰 المدفوعات
```
GET  /api/payment/history        - تاريخ المدفوعات
POST /api/payment/payclick       - دفع عبر PayClick
POST /api/payment/payclick/webhook - webhook للمدفوعات
```

## 🔧 الميزات المتقدمة

### 🤖 خدمات الذكاء الاصطناعي

#### دعم متعدد المقدمين
- **OpenAI GPT-4**: للمحادثات وتحليل الصور
- **Google Gemini**: كبديل للمحادثات النصية
- **DALL-E 3**: لتوليد الصور الطبية

#### الميزات المدعومة
- كشف اللغة التلقائي (عربي/إنجليزي)
- تحليل الصور الطبية المتقدم
- إنشاء البطاقات التعليمية
- دعم السياق في المحادثات

### 💳 نظام الدفع PayClick

#### الميزات المدعومة
- إنشاء المدفوعات الآمنة
- معالجة Webhooks
- دعم الاسترداد
- تتبع حالة المدفوعات
- التحقق من التوقيع

#### حالات الدفع المدعومة
- `pending`: في الانتظار
- `completed`: مكتمل
- `failed`: فاشل
- `refunded`: مسترد

### 📱 نظام SMS

#### مقدمي الخدمة المدعومين
- **Twilio**: الخيار الأول المفضل
- **Generic SMS API**: للمقدمين المخصصين
- **Test Mode**: للتطوير والاختبار

#### الرسائل المدعومة
- رموز OTP للتحقق
- رسائل الترحيب
- إشعارات الاشتراك

### 🛡️ نظام الحدود والاستخدام

#### تتبع الاستخدام
- عدد الرموز المستخدمة (المحادثات)
- عدد الصور المحللة/المولدة
- عدد الفيديوهات المولدة
- عدد المحادثات المنشأة

#### فرض الحدود
- فحص تلقائي قبل كل طلب
- رسائل خطأ واضحة
- معلومات الاستخدام المتبقي
- دعم الباقات غير المحدودة (-1)

### أمثلة على الاستخدام

#### فحص حالة النظام
```bash
curl -X GET http://localhost:8000/api/status \
  -H "Content-Type: application/json"
```

#### تسجيل مستخدم جديد
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone_number": "+966501234567"
  }'
```

#### محادثة مع الذكاء الاصطناعي
```bash
curl -X POST http://localhost:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "message": "ما هي أعراض الإنفلونزا؟"
  }'
```

#### تحليل صورة طبية
```bash
curl -X POST http://localhost:8000/api/ai/analyze-image \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@medical_image.jpg" \
  -F "question=ما رأيك في هذه الصورة الطبية؟"
```

#### إنشاء بطاقات تعليمية
```bash
curl -X POST http://localhost:8000/api/ai/generate-flashcards \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "topic": "نظام القلب والأوعية الدموية",
    "count": 5
  }'
```

## 🔍 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ في الاتصال بقاعدة البيانات
```bash
# التحقق من إعدادات قاعدة البيانات
php artisan config:clear
php artisan migrate:status
```

#### 2. مشاكل في خدمات الذكاء الاصطناعي
```bash
# اختبار الاتصال
curl http://localhost:8000/api/status

# التحقق من المفاتيح في .env
OPENAI_API_KEY=sk-...
GEMINI_API_KEY=...
```

#### 3. مشاكل في إرسال SMS
```bash
# تفعيل وضع الاختبار
APP_ENV=testing

# أو استخدام Twilio
TWILIO_SID=your_sid
TWILIO_TOKEN=your_token
```

#### 4. مشاكل في الصور
```bash
# التأكد من رابط التخزين
php artisan storage:link

# التحقق من الصلاحيات
chmod -R 755 storage/
chmod -R 755 public/storage/
```

### سجلات النظام
```bash
# عرض السجلات المباشرة
tail -f storage/logs/laravel.log

# البحث في السجلات
grep "ERROR" storage/logs/laravel.log
```

## 🏗️ بنية المشروع

```
medicothink_backend_laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/     # تحكمات API
│   │   ├── Middleware/          # الوسطاء
│   │   ├── Requests/           # طلبات التحقق
│   │   └── Resources/          # موارد API
│   ├── Models/                 # نماذج البيانات
│   └── Services/              # خدمات الأعمال
├── tests/
│   ├── Feature/               # اختبارات الميزات
│   └── postman_collection.json # مجموعة Postman
├── database/
│   ├── migrations/            # هجرات قاعدة البيانات
│   └── seeders/              # بذور البيانات
├── routes/
│   └── api.php               # مسارات API
└── config/                   # ملفات الإعداد
```

## 📊 مراقبة الأداء

### مؤشرات الأداء الرئيسية
- زمن الاستجابة للـ API
- معدل نجاح الطلبات
- استخدام الذاكرة والمعالج
- حالة قاعدة البيانات

### أدوات المراقبة
```bash
# مراقبة الطوابير
php artisan queue:monitor

# إحصائيات قاعدة البيانات
php artisan db:monitor

# فحص الأداء
php artisan route:list --compact
```

## 🔧 الإعدادات المتقدمة

### تحسين الأداء
```bash
# تحسين التطبيق للإنتاج
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# تحسين Composer
composer install --optimize-autoloader --no-dev
```

### إعداد الطوابير
في ملف `.env`:
```env
QUEUE_CONNECTION=database
```

ثم تشغيل:
```bash
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### إعداد التخزين السحابي
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

### إعداد Redis للتخزين المؤقت
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📊 المراقبة والسجلات

### عرض السجلات
```bash
tail -f storage/logs/laravel.log
```

### مراقبة الطوابير
```bash
php artisan queue:monitor
```

### مراقبة الاستخدام
```bash
# إحصائيات المستخدمين
php artisan tinker
>>> App\Models\User::count()
>>> App\Models\Subscription::where('status', 'active')->count()
```

## 🔒 الأمان

### أفضل الممارسات المطبقة:
- تشفير كلمات المرور باستخدام bcrypt
- حماية CSRF للطلبات
- تحديد معدل الطلبات (Rate Limiting)
- تنظيف المدخلات وحمايتها
- استخدام HTTPS في الإنتاج
- التحقق من التوقيع في Webhooks
- فرض حدود الاستخدام
- تشفير البيانات الحساسة

### إعداد CORS
في ملف `config/cors.php`:
```php
'allowed_origins' => ['https://yourdomain.com'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['*'],
```

### إعداد Rate Limiting
```php
// في RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

## 🚀 النشر

### نشر على خادم Linux
```bash
# تحديث الكود
git pull origin main

# تثبيت التبعيات
composer install --optimize-autoloader --no-dev

# تشغيل الهجرات
php artisan migrate --force

# تحسين الأداء
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إعادة تشغيل الطوابير
php artisan queue:restart
```

### نشر باستخدام Docker
```dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www
```

### إعداد Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/medicothink-backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 📈 التحليلات والإحصائيات

### إحصائيات الاستخدام
```php
// في Controller أو Command
$stats = [
    'total_users' => User::count(),
    'active_subscriptions' => Subscription::where('status', 'active')->count(),
    'total_conversations' => Conversation::count(),
    'total_messages' => Message::count(),
    'ai_requests_today' => Message::where('is_from_user', false)
        ->whereDate('created_at', today())->count(),
];
```

### تقارير الإيرادات
```php
$revenue = Payment::where('status', 'completed')
    ->whereMonth('created_at', now()->month)
    ->sum('amount');
```

## 🤝 المساهمة

1. Fork المشروع
2. إنشاء فرع للميزة الجديدة (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push للفرع (`git push origin feature/amazing-feature`)
5. فتح Pull Request

### معايير الكود
- اتباع PSR-12 coding standards
- كتابة اختبارات للميزات الجديدة
- توثيق الـ API endpoints
- استخدام Type hints
- كتابة تعليقات واضحة

## 📄 الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## 📞 الدعم

للدعم والاستفسارات:
- البريد الإلكتروني: support@medicothink.com
- التوثيق: [docs.medicothink.com](https://docs.medicothink.com)
- المشاكل: [GitHub Issues](https://github.com/your-username/medicothink-backend/issues)

## 🔄 سجل التغييرات

### الإصدار 1.0.0 (2024-01-01)
- ✅ إطلاق النسخة الأولى
- ✅ نظام التوثيق الكامل
- ✅ خدمات الذكاء الاصطناعي
- ✅ نظام الاشتراكات والمدفوعات
- ✅ إدارة المحادثات
- ✅ دعم متعدد اللغات
- ✅ اختبارات شاملة
- ✅ مجموعة Postman

## 🔄 التحديثات المستقبلية

- [ ] 🎥 دعم توليد الفيديو الطبي
- [ ] 🔊 معالجة الصوت والنطق
- [ ] 📱 تطبيق إدارة ويب
- [ ] 📊 لوحة تحكم تحليلية متقدمة
- [ ] 🌍 دعم المزيد من اللغات
- [ ] 🔗 API للمطورين الخارجيين
- [ ] 🤖 نماذج ذكاء اصطناعي مخصصة
- [ ] 📈 تحليلات متقدمة للاستخدام
- [ ] 🔔 نظام إشعارات فوري
- [ ] 🏥 تكامل مع أنظمة المستشفيات

---

**MedicoThink Backend** - تمكين الرعاية الصحية من خلال تقنية الذكاء الاصطناعي 🏥✨