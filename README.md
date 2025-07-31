# MedicoThink - Medical AI Assistant

تطبيق مساعد طبي ذكي يستخدم الذكاء الاصطناعي لتقديم المشورة الطبية والتحليل الطبي للصور.

## المميزات الرئيسية

### 🔐 نظام المصادقة
- تسجيل الدخول بالبريد الإلكتروني وكلمة المرور
- تسجيل الدخول باستخدام OTP عبر رقم الهاتف
- إنشاء حساب جديد مع معلومات طبية مفصلة
- إدارة الملف الشخصي

### 🤖 الذكاء الاصطناعي
- محادثة ذكية مع مساعد طبي AI
- تحليل الصور الطبية باستخدام AI
- إنشاء ملخصات للمحادثات في شكل بطاقات تعليمية
- تصنيف المعلومات الطبية (أعراض، تشخيص، علاج، إلخ)

### 💬 إدارة المحادثات
- حفظ المحادثات تلقائياً
- أرشفة المحادثات القديمة
- البحث في المحادثات السابقة
- مزامنة المحادثات مع الخادم

### 📱 واجهة المستخدم
- تصميم عصري ومتجاوب
- دعم اللغة العربية
- رسوم متحركة سلسة
- تجربة مستخدم محسنة

### 💳 إدارة الاشتراكات
- مراقبة حالة الاشتراك
- تنبيهات انتهاء الاشتراك
- إغلاق الحساب عند انتهاء الاشتراك
- خطط اشتراك متعددة

## التقنيات المستخدمة

### Frontend (Flutter)
- **Flutter**: إطار عمل تطوير التطبيقات
- **Dart**: لغة البرمجة
- **HTTP**: للتواصل مع API
- **SharedPreferences**: لحفظ البيانات محلياً
- **ImagePicker**: لاختيار الصور
- **PermissionHandler**: لإدارة الأذونات

### Backend Integration
- **Laravel API**: نظام إدارة الخلفية
- **JWT Authentication**: نظام المصادقة
- **RESTful APIs**: واجهات برمجة التطبيقات
- **File Upload**: رفع الصور للتحليل

### خدمات إضافية
- **AI Integration**: تكامل مع خدمات الذكاء الاصطناعي
- **Push Notifications**: الإشعارات الفورية
- **Crash Reporting**: تتبع الأخطاء
- **Analytics**: تحليل استخدام التطبيق

## هيكل المشروع

```
lib/
├── config/           # إعدادات التطبيق
├── models/           # نماذج البيانات
├── services/         # خدمات التطبيق
├── UI/              # واجهات المستخدم
│   ├── auth/        # شاشات المصادقة
│   ├── home/        # الشاشة الرئيسية والمحادثة
│   ├── splash/      # شاشات البداية
│   └── widgets/     # المكونات المشتركة
├── utils/           # أدوات مساعدة
└── main.dart        # نقطة البداية
```

## التثبيت والتشغيل

### المتطلبات
- Flutter SDK (3.0+)
- Dart SDK (3.0+)
- Android Studio / VS Code
- جهاز Android أو iOS للاختبار

### خطوات التثبيت

1. **استنساخ المشروع**
```bash
git clone https://github.com/your-repo/medicothink.git
cd medicothink
```

2. **تثبيت التبعيات**
```bash
flutter pub get
```

3. **إعداد API**
- قم بتحديث `lib/config/api_config.dart` برابط API الخاص بك
- تأكد من تشغيل خادم Laravel

4. **تشغيل التطبيق**
```bash
flutter run
```

## إعداد Laravel Backend

### متطلبات الخادم
- PHP 8.1+
- Laravel 10+
- MySQL/PostgreSQL
- Redis (اختياري)

### نقاط النهاية المطلوبة

#### المصادقة
- `POST /api/auth/login` - تسجيل الدخول
- `POST /api/auth/register` - إنشاء حساب
- `POST /api/auth/otp-login` - إرسال OTP
- `POST /api/auth/verify-otp` - التحقق من OTP
- `POST /api/auth/logout` - تسجيل الخروج

#### المحادثات
- `GET /api/conversations` - جلب المحادثات
- `POST /api/ai/chat` - إرسال رسالة للـ AI
- `POST /api/ai/analyze-image` - تحليل صورة
- `GET /api/conversations/summary/{id}` - ملخص المحادثة

#### الاشتراكات
- `GET /api/subscription/status` - حالة الاشتراك
- `POST /api/subscription/subscribe` - اشتراك جديد

## الأمان

### حماية البيانات
- تشفير كلمات المرور
- JWT tokens آمنة
- HTTPS إجباري
- تشفير البيانات الحساسة

### الخصوصية
- عدم حفظ الصور الطبية
- حذف المحادثات القديمة
- إخفاء الهوية في التحليلات
- موافقة المستخدم على جمع البيانات

## الاختبار

### اختبار الوحدة
```bash
flutter test
```

### اختبار التكامل
```bash
flutter test integration_test/
```

### اختبار الأداء
```bash
flutter test --profile
```

## النشر

### Android
```bash
flutter build apk --release
```

### iOS
```bash
flutter build ios --release
```

### Web (إذا كان مدعوماً)
```bash
flutter build web --release
```

## المساهمة

نرحب بالمساهمات! يرجى اتباع الخطوات التالية:

1. Fork المشروع
2. إنشاء branch جديد (`git checkout -b feature/amazing-feature`)
3. Commit التغييرات (`git commit -m 'Add amazing feature'`)
4. Push إلى Branch (`git push origin feature/amazing-feature`)
5. فتح Pull Request

## الترخيص

هذا المشروع مرخص تحت رخصة MIT - راجع ملف [LICENSE](LICENSE) للتفاصيل.

## الدعم

للحصول على الدعم:
- فتح issue في GitHub
- التواصل عبر البريد الإلكتروني: support@medicothink.com
- زيارة الموقع الرسمي: https://medicothink.com

## الإصدارات

### v1.0.0 (الحالي)
- نظام المصادقة الكامل
- محادثة AI مع تحليل الصور
- إدارة المحادثات والأرشفة
- بطاقات الملخص التعليمية
- إدارة الاشتراكات

### الإصدارات القادمة
- دعم المزيد من اللغات
- تحسينات الأداء
- ميزات AI إضافية
- تطبيق ويب

---

**تم تطويره بـ ❤️ لخدمة المجتمع الطبي**