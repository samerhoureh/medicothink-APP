# MedicoThink Backend - Laravel API with Web Dashboard

نظام خلفي كامل لتطبيق MedicoThink المحمول مع لوحة تحكم ويب للإدارة.

## 🚀 المميزات

### 🔧 API للتطبيق المحمول
- **المصادقة**: تسجيل الدخول بالإيميل/كلمة المرور و OTP
- **الذكاء الاصطناعي**: محادثات طبية وتحليل الصور
- **إدارة المحادثات**: حفظ، أرشفة، وتلخيص المحادثات
- **الاشتراكات**: إدارة خطط الاشتراك والدفع

### 🖥️ لوحة التحكم الويب
- **إحصائيات شاملة**: عدد المستخدمين والمحادثات والاشتراكات
- **إدارة المستخدمين**: عرض وتعديل بيانات المستخدمين
- **مراقبة المحادثات**: عرض المحادثات والرسائل
- **إدارة الاشتراكات**: متابعة حالة الاشتراكات

## 📋 المتطلبات

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM

## ⚡ التثبيت السريع

### 1. تثبيت التبعيات
```bash
composer install
npm install
```

### 2. إعداد البيئة
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 3. إعداد قاعدة البيانات
```bash
# إنشاء قاعدة البيانات
mysql -u root -p -e "CREATE DATABASE medicothink"

# تشغيل الهجرات
php artisan migrate
```

### 4. بناء الأصول
```bash
npm run build
```

### 5. تشغيل الخادم
```bash
php artisan serve
```

## 🔧 الإعدادات المطلوبة

### متغيرات البيئة (.env)
```env
# قاعدة البيانات
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicothink
DB_USERNAME=root
DB_PASSWORD=your_password

# OpenAI للذكاء الاصطناعي
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4

# Twilio للرسائل النصية
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number

# JWT للمصادقة
JWT_SECRET=your_jwt_secret
```

## 📱 API Endpoints

### المصادقة
```
POST /api/auth/login          - تسجيل الدخول
POST /api/auth/register       - تسجيل مستخدم جديد
POST /api/auth/otp-login      - إرسال OTP
POST /api/auth/verify-otp     - التحقق من OTP
POST /api/auth/logout         - تسجيل الخروج
```

### الذكاء الاصطناعي
```
POST /api/ai/chat            - إرسال رسالة للذكاء الاصطناعي
POST /api/ai/analyze-image   - تحليل صورة طبية
```

### المحادثات
```
GET  /api/conversations           - جلب المحادثات
GET  /api/conversations/{id}      - جلب محادثة محددة
POST /api/conversations/archive   - أرشفة محادثة
GET  /api/conversations/{id}/summary - ملخص المحادثة
```

### الاشتراكات
```
GET  /api/subscription/status     - حالة الاشتراك
GET  /api/subscription/plans      - خطط الاشتراك
POST /api/subscription/subscribe  - اشتراك جديد
```

## 🖥️ لوحة التحكم

### الوصول
```
http://localhost:8000/dashboard
```

### الصفحات المتاحة
- **الرئيسية**: `/dashboard` - إحصائيات عامة
- **المستخدمين**: `/dashboard/users` - إدارة المستخدمين
- **المحادثات**: `/dashboard/conversations` - مراقبة المحادثات
- **الاشتراكات**: `/dashboard/subscriptions` - إدارة الاشتراكات
- **الإعدادات**: `/dashboard/settings` - إعدادات النظام

## 🔒 الأمان

### JWT Authentication
- رموز الوصول تنتهي صلاحيتها خلال 60 دقيقة
- رموز التحديث تنتهي صلاحيتها خلال 14 يوم
- تشفير آمن لكلمات المرور

### حماية API
- معدل محدود للطلبات
- التحقق من صحة البيانات
- حماية من CSRF

## 📊 قاعدة البيانات

### الجداول الرئيسية
- `users` - بيانات المستخدمين
- `conversations` - المحادثات
- `messages` - الرسائل
- `subscriptions` - الاشتراكات
- `otp_codes` - رموز التحقق
- `conversation_summaries` - ملخصات المحادثات

## 🚀 النشر

### 1. إعداد الخادم
```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت PHP و MySQL
sudo apt install php8.1 php8.1-mysql mysql-server nginx -y
```

### 2. رفع الملفات
```bash
# نسخ الملفات إلى الخادم
rsync -avz . user@server:/var/www/medicothink/
```

### 3. إعداد الأذونات
```bash
sudo chown -R www-data:www-data /var/www/medicothink
sudo chmod -R 755 /var/www/medicothink/storage
sudo chmod -R 755 /var/www/medicothink/bootstrap/cache
```

### 4. إعداد Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/medicothink/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🔧 الصيانة

### تحديث التبعيات
```bash
composer update
npm update
```

### تنظيف التخزين المؤقت
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### النسخ الاحتياطي
```bash
# نسخ احتياطي لقاعدة البيانات
mysqldump -u root -p medicothink > backup.sql

# نسخ احتياطي للملفات
tar -czf medicothink-backup.tar.gz /var/www/medicothink
```

## 📞 الدعم

للحصول على المساعدة:
- البريد الإلكتروني: support@medicothink.com
- التوثيق: https://docs.medicothink.com

---

**جاهز للنشر والربط مع التطبيق المحمول** 🚀