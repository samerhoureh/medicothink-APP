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

# SMS Service
SMS_API_KEY=your_sms_key
SMS_API_URL=https://sms-provider.com/api
SMS_FROM_NUMBER=+1234567890
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

### 7. تثبيت Sanctum
```bash
php artisan sanctum:install
```

## 🚀 تشغيل المشروع

### تشغيل الخادم المحلي
```bash
php artisan serve
```

سيكون التطبيق متاحاً على: `http://localhost:8000`

### تشغيل الطوابير (اختياري)
```bash
php artisan queue:work
```

## 📚 توثيق API

### نقاط النهاية الرئيسية

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

### أمثلة على الاستخدام

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
├── database/
│   ├── migrations/            # هجرات قاعدة البيانات
│   └── seeders/              # بذور البيانات
├── routes/
│   └── api.php               # مسارات API
└── config/                   # ملفات الإعداد
```

## 🔧 الإعدادات المتقدمة

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

## 🧪 الاختبارات

### تشغيل الاختبارات
```bash
php artisan test
```

### إنشاء اختبار جديد
```bash
php artisan make:test UserRegistrationTest
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

## 🔒 الأمان

### أفضل الممارسات المطبقة:
- تشفير كلمات المرور باستخدام bcrypt
- حماية CSRF للطلبات
- تحديد معدل الطلبات (Rate Limiting)
- تنظيف المدخلات وحمايتها
- استخدام HTTPS في الإنتاج

### إعداد CORS
في ملف `config/cors.php`:
```php
'allowed_origins' => ['https://yourdomain.com'],
'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
'allowed_headers' => ['*'],
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

## 🤝 المساهمة

1. Fork المشروع
2. إنشاء فرع للميزة الجديدة (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push للفرع (`git push origin feature/amazing-feature`)
5. فتح Pull Request

## 📄 الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## 📞 الدعم

للدعم والاستفسارات:
- البريد الإلكتروني: support@medicothink.com
- التوثيق: [docs.medicothink.com](https://docs.medicothink.com)
- المشاكل: [GitHub Issues](https://github.com/your-username/medicothink-backend/issues)

## 🔄 التحديثات المستقبلية

- [ ] دعم المزيد من مقدمي خدمات الذكاء الاصطناعي
- [ ] تحسين أداء تحليل الصور
- [ ] إضافة المزيد من طرق الدفع
- [ ] دعم الإشعارات الفورية
- [ ] لوحة تحكم إدارية متقدمة
- [ ] تقارير تحليلية مفصلة
- [ ] دعم اللغات المتعددة
- [ ] API للمطورين الخارجيين

---

**MedicoThink Backend** - تمكين الرعاية الصحية من خلال تقنية الذكاء الاصطناعي 🏥✨