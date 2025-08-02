# MedicoThink Backend - Complete Laravel System

نظام خلفي شامل لتطبيق MedicoThink مع لوحة تحكم إدارية متقدمة وتكامل كامل مع جميع خدمات الذكاء الاصطناعي وبوابات الدفع.

## 🚀 المميزات الشاملة

### 🔧 **API متقدم للتطبيق المحمول**
- **المصادقة المتقدمة**: تسجيل الدخول بالإيميل/كلمة المرور و OTP مع JWT
- **الذكاء الاصطناعي الشامل**:
  - محادثات نصية ذكية (GPT-4)
  - تحليل الصور الطبية (GPT-4 Vision)
  - تحويل النص إلى صوت (ElevenLabs)
  - تحويل الصوت إلى نص (Whisper)
  - توليد الصور (DALL-E 3)
  - توليد الفيديو (Stability AI)
- **إدارة المحادثات**: حفظ، أرشفة، وتلخيص ذكي
- **نظام الاشتراكات**: خطط متعددة مع إدارة متقدمة

### 💳 **بوابات الدفع المتكاملة**
- **Stripe**: دفع بالبطاقات الائتمانية
- **PayPal**: دفع عبر PayPal
- **تتبع المعاملات**: سجل كامل للدفعات
- **إدارة الاشتراكات**: تجديد تلقائي ومتابعة

### 🖥️ **لوحة التحكم الإدارية الشاملة**
- **لوحة المعلومات**: إحصائيات مفصلة ومؤشرات الأداء
- **إدارة المستخدمين**: عرض، تعديل، وإدارة حسابات المستخدمين
- **مراقبة المحادثات**: عرض المحادثات والرسائل مع التفاصيل
- **إدارة الاشتراكات**: متابعة حالة الاشتراكات والتجديد
- **إدارة المدفوعات**: تتبع المعاملات المالية
- **إدارة إصدارات التطبيق**: رفع وإدارة تحديثات التطبيق
- **التحليلات المتقدمة**: تقارير مفصلة عن الاستخدام والإيرادات

## 📋 المتطلبات

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM
- Redis (اختياري للتخزين المؤقت)

## ⚡ التثبيت والإعداد

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

# إضافة بيانات تجريبية (اختياري)
php artisan db:seed
```

### 4. بناء الأصول
```bash
npm run build
```

### 5. إعداد التخزين
```bash
php artisan storage:link
```

### 6. تشغيل الخادم
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

# خدمات الذكاء الاصطناعي
OPENAI_API_KEY=your_openai_key
OPENAI_MODEL=gpt-4
ELEVENLABS_API_KEY=your_elevenlabs_key
STABILITY_API_KEY=your_stability_key

# بوابات الدفع
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret
PAYPAL_MODE=sandbox

# خدمة الرسائل النصية
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number

# JWT للمصادقة
JWT_SECRET=your_jwt_secret
```

## 📱 API Endpoints الشاملة

### المصادقة
```
POST /api/auth/login          - تسجيل الدخول
POST /api/auth/register       - تسجيل مستخدم جديد
POST /api/auth/otp-login      - إرسال OTP
POST /api/auth/verify-otp     - التحقق من OTP
POST /api/auth/logout         - تسجيل الخروج
POST /api/auth/refresh        - تجديد الرمز المميز
```

### الذكاء الاصطناعي المتقدم
```
POST /api/ai/text             - محادثة نصية ذكية
POST /api/ai/image-analysis   - تحليل الصور الطبية
POST /api/ai/text-to-speech   - تحويل النص إلى صوت
POST /api/ai/speech-to-text   - تحويل الصوت إلى نص
POST /api/ai/generate-image   - توليد الصور
POST /api/ai/generate-video   - توليد الفيديو
```

### المحادثات
```
GET  /api/conversations           - جلب المحادثات
GET  /api/conversations/{id}      - جلب محادثة محددة
POST /api/conversations/archive   - أرشفة محادثة
POST /api/conversations/unarchive - إلغاء أرشفة محادثة
DELETE /api/conversations/{id}    - حذف محادثة
GET  /api/conversations/{id}/summary - ملخص المحادثة
```

### الاشتراكات والدفع
```
GET  /api/subscription/status     - حالة الاشتراك
GET  /api/subscription/plans      - خطط الاشتراك
POST /api/subscription/subscribe  - اشتراك جديد

POST /api/payment/stripe          - دفع عبر Stripe
POST /api/payment/paypal          - دفع عبر PayPal
GET  /api/payment/history         - سجل المدفوعات
```

## 🖥️ لوحة التحكم الإدارية

### الوصول
```
http://localhost:8000/admin
```

### الصفحات المتاحة
- **الرئيسية**: `/admin` - إحصائيات شاملة
- **المستخدمين**: `/admin/users` - إدارة المستخدمين
- **المحادثات**: `/admin/conversations` - مراقبة المحادثات
- **الاشتراكات**: `/admin/subscriptions` - إدارة الاشتراكات
- **المدفوعات**: `/admin/payments` - تتبع المعاملات المالية
- **إصدارات التطبيق**: `/admin/app-versions` - إدارة التحديثات
- **التحليلات**: `/admin/analytics` - تقارير مفصلة
- **الإعدادات**: `/admin/settings` - إعدادات النظام

## 🔒 الأمان المتقدم

### JWT Authentication
- رموز الوصول تنتهي صلاحيتها خلال 60 دقيقة
- رموز التحديث تنتهي صلاحيتها خلال 14 يوم
- تشفير آمن لكلمات المرور مع bcrypt

### حماية API
- معدل محدود للطلبات (Rate Limiting)
- التحقق من صحة البيانات الشامل
- حماية من CSRF و XSS
- تشفير البيانات الحساسة

### أمان المدفوعات
- تشفير معلومات الدفع
- التحقق من صحة المعاملات
- حماية من الاحتيال

## 📊 قاعدة البيانات الشاملة

### الجداول الرئيسية
- `users` - بيانات المستخدمين مع التفاصيل الطبية
- `conversations` - المحادثات مع إعدادات الأرشفة
- `messages` - الرسائل مع دعم الوسائط المتعددة
- `subscriptions` - الاشتراكات مع خطط متعددة
- `payment_transactions` - المعاملات المالية
- `otp_codes` - رموز التحقق مع انتهاء الصلاحية
- `conversation_summaries` - ملخصات المحادثات الذكية
- `app_versions` - إصدارات التطبيق للتحديث

## 🚀 النشر على الإنتاج

### 1. إعداد الخادم
```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت المتطلبات
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl mysql-server nginx redis-server -y
```

### 2. إعداد SSL
```bash
# تثبيت Certbot
sudo apt install certbot python3-certbot-nginx -y

# الحصول على شهادة SSL
sudo certbot --nginx -d your-domain.com
```

### 3. إعداد Nginx
```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;
    root /var/www/medicothink/public;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

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

    location ~ /\.ht {
        deny all;
    }
}
```

### 4. إعداد المهام المجدولة
```bash
# إضافة إلى crontab
* * * * * cd /var/www/medicothink && php artisan schedule:run >> /dev/null 2>&1
```

### 5. إعداد Queue Workers
```bash
# إنشاء خدمة systemd
sudo nano /etc/systemd/system/medicothink-worker.service

[Unit]
Description=MedicoThink Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/medicothink/artisan queue:work

[Install]
WantedBy=multi-user.target
```

## 🔧 الصيانة والمراقبة

### تحديث التبعيات
```bash
composer update
npm update
php artisan migrate
```

### تنظيف التخزين المؤقت
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### النسخ الاحتياطي التلقائي
```bash
# إنشاء سكريبت النسخ الاحتياطي
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p medicothink > /backups/medicothink_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/medicothink
```

### مراقبة الأداء
- استخدام Laravel Telescope للتطوير
- تكامل مع New Relic أو Datadog للإنتاج
- مراقبة استخدام API الذكاء الاصطناعي
- تتبع معدلات التحويل للاشتراكات

## 📈 التحليلات والتقارير

### مؤشرات الأداء الرئيسية
- عدد المستخدمين النشطين يومياً/أسبوعياً/شهرياً
- معدل نمو الاشتراكات
- إيرادات شهرية ومعدل النمو
- استخدام ميزات الذكاء الاصطناعي
- معدل الاحتفاظ بالمستخدمين

### التقارير المالية
- تقارير الإيرادات الشهرية
- تحليل أداء خطط الاشتراك
- معدلات الإلغاء والتجديد
- تكاليف خدمات الذكاء الاصطناعي

## 🆘 الدعم والمساعدة

### الوثائق
- دليل المطور: `/docs/developer-guide.md`
- دليل API: `/docs/api-documentation.md`
- دليل النشر: `/docs/deployment-guide.md`

### الاتصال
- البريد الإلكتروني: support@medicothink.com
- الدعم التقني: tech@medicothink.com
- الطوارئ: emergency@medicothink.com

---

**🎉 النظام جاهز للنشر والاستخدام مع جميع المميزات المتقدمة!**

### ✅ ما يتضمنه النظام:
- ✅ API شامل للتطبيق المحمول
- ✅ تكامل كامل مع جميع خدمات الذكاء الاصطناعي
- ✅ بوابات دفع متعددة (Stripe & PayPal)
- ✅ لوحة تحكم إدارية متقدمة
- ✅ نظام اشتراكات متطور
- ✅ أمان متقدم وحماية شاملة
- ✅ تحليلات وتقارير مفصلة
- ✅ جاهز للنشر على الإنتاج