# MedicoThink - دليل النشر الشامل

## 🚀 نشر النظام الخلفي (Laravel)

### 1. متطلبات الخادم
```bash
# تحديث النظام
sudo apt update && sudo apt upgrade -y

# تثبيت المتطلبات الأساسية
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath
sudo apt install -y mysql-server nginx composer nodejs npm redis-server
```

### 2. إعداد قاعدة البيانات
```bash
# الدخول إلى MySQL
sudo mysql -u root -p

# إنشاء قاعدة البيانات والمستخدم
CREATE DATABASE medicothink;
CREATE USER 'medicothink_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON medicothink.* TO 'medicothink_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. رفع الملفات ونشر Laravel
```bash
# رفع الملفات إلى الخادم
cd /var/www/
sudo git clone your-repository medicothink
cd medicothink

# تثبيت التبعيات
sudo composer install --optimize-autoloader --no-dev
sudo npm install
sudo npm run build

# إعداد الصلاحيات
sudo chown -R www-data:www-data /var/www/medicothink
sudo chmod -R 755 /var/www/medicothink
sudo chmod -R 775 /var/www/medicothink/storage
sudo chmod -R 775 /var/www/medicothink/bootstrap/cache

# إعداد البيئة
sudo cp .env.example .env
sudo php artisan key:generate
sudo php artisan jwt:secret
```

### 4. تحديث ملف .env للإنتاج
```env
APP_NAME="MedicoThink"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicothink
DB_USERNAME=medicothink_user
DB_PASSWORD=strong_password_here

# خدمات الذكاء الاصطناعي
OPENAI_API_KEY=your_openai_api_key
ELEVENLABS_API_KEY=your_elevenlabs_api_key
STABILITY_API_KEY=your_stability_api_key

# بوابات الدفع
STRIPE_KEY=pk_live_your_stripe_publishable_key
STRIPE_SECRET=sk_live_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

PAYPAL_CLIENT_ID=your_paypal_live_client_id
PAYPAL_SECRET=your_paypal_live_secret
PAYPAL_MODE=live

# خدمة الرسائل النصية
TWILIO_SID=your_twilio_account_sid
TWILIO_TOKEN=your_twilio_auth_token
TWILIO_FROM=your_twilio_phone_number
```

### 5. تشغيل الهجرات
```bash
sudo php artisan migrate --force
sudo php artisan storage:link
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

### 6. إعداد Nginx
```bash
sudo nano /etc/nginx/sites-available/medicothink
```

```nginx
server {
    listen 80;
    server_name your-domain.com www.your-domain.com;
    root /var/www/medicothink/public;

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

```bash
# تفعيل الموقع
sudo ln -s /etc/nginx/sites-available/medicothink /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. إعداد SSL مع Let's Encrypt
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### 8. إعداد المهام المجدولة
```bash
sudo crontab -e
# إضافة السطر التالي:
* * * * * cd /var/www/medicothink && php artisan schedule:run >> /dev/null 2>&1
```

## 📱 نشر التطبيق المحمول

### Android (Google Play Store)

#### 1. إعداد التوقيع
```bash
# إنشاء مفتاح التوقيع
keytool -genkey -v -keystore ~/medicothink-key.jks -keyalg RSA -keysize 2048 -validity 10000 -alias medicothink
```

#### 2. إعداد ملف android/key.properties
```properties
storePassword=your_store_password
keyPassword=your_key_password
keyAlias=medicothink
storeFile=/path/to/medicothink-key.jks
```

#### 3. تحديث android/app/build.gradle
```gradle
android {
    signingConfigs {
        release {
            keyAlias keystoreProperties['keyAlias']
            keyPassword keystoreProperties['keyPassword']
            storeFile keystoreProperties['storeFile'] ? file(keystoreProperties['storeFile']) : null
            storePassword keystoreProperties['storePassword']
        }
    }
    buildTypes {
        release {
            signingConfig signingConfigs.release
        }
    }
}
```

#### 4. بناء APK للإنتاج
```bash
flutter build apk --release
flutter build appbundle --release
```

### iOS (App Store)

#### 1. إعداد Xcode
- فتح ios/Runner.xcworkspace في Xcode
- تحديث Bundle Identifier
- إعداد Team وCertificates

#### 2. بناء للإنتاج
```bash
flutter build ios --release
```

#### 3. رفع إلى App Store Connect
- استخدام Xcode لرفع البناء
- إكمال معلومات التطبيق في App Store Connect

## 🔧 إعدادات الإنتاج المتقدمة

### 1. إعداد Queue Workers
```bash
sudo nano /etc/systemd/system/medicothink-worker.service
```

```ini
[Unit]
Description=MedicoThink Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/medicothink/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable medicothink-worker
sudo systemctl start medicothink-worker
```

### 2. إعداد Redis للتخزين المؤقت
```bash
# تحديث .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 3. إعداد النسخ الاحتياطي التلقائي
```bash
sudo nano /usr/local/bin/medicothink-backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/medicothink"

# إنشاء مجلد النسخ الاحتياطي
mkdir -p $BACKUP_DIR

# نسخ احتياطي لقاعدة البيانات
mysqldump -u medicothink_user -p'password' medicothink > $BACKUP_DIR/database_$DATE.sql

# نسخ احتياطي للملفات
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/medicothink

# حذف النسخ القديمة (أكثر من 30 يوم)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

```bash
sudo chmod +x /usr/local/bin/medicothink-backup.sh

# إضافة إلى crontab للتشغيل يومياً
sudo crontab -e
0 2 * * * /usr/local/bin/medicothink-backup.sh
```

## 🔐 الأمان والحماية

### 1. إعداد Firewall
```bash
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### 2. تأمين MySQL
```bash
sudo mysql_secure_installation
```

### 3. إعداد Fail2Ban
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

## 📊 المراقبة والصيانة

### 1. مراقبة الأداء
```bash
# مراقبة استخدام الموارد
htop
df -h
free -m

# مراقبة سجلات Laravel
tail -f /var/www/medicothink/storage/logs/laravel.log

# مراقبة سجلات Nginx
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log
```

### 2. تحديث النظام
```bash
# تحديث التبعيات
cd /var/www/medicothink
sudo composer update
sudo npm update
sudo php artisan migrate --force
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

## 🌐 ربط التطبيق المحمول

### تحديث API URL في Flutter
```dart
// lib/config/api_config.dart
static const String baseUrl = 'https://your-domain.com/api';
```

### اختبار الاتصال
```bash
# اختبار API من التطبيق
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

## ✅ قائمة التحقق النهائية

- [ ] Laravel مثبت ويعمل
- [ ] قاعدة البيانات متصلة ومهاجرة
- [ ] SSL مفعل ويعمل
- [ ] بوابات الدفع مكونة
- [ ] خدمات الذكاء الاصطناعي متصلة
- [ ] التطبيق المحمول يتصل بـ API
- [ ] النسخ الاحتياطي مجدول
- [ ] المراقبة مفعلة
- [ ] الأمان محكم

🎉 **النظام جاهز للاستخدام في الإنتاج!**