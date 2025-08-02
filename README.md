# MedicoThink Backend - Complete Laravel System

A comprehensive backend system for the MedicoThink application featuring an advanced admin dashboard and full integration with AI services and payment gateways.

## 🚀 Comprehensive Features

### 🔧 **Advanced Mobile API**
- **Advanced Authentication**: Email/password & OTP login with JWT
- **Comprehensive AI Integration**:
  - Smart text conversations (GPT-4)
  - Medical image analysis (GPT-4 Vision)
  - Text-to-speech conversion (ElevenLabs)
  - Speech-to-text conversion (Whisper)
  - Image generation (DALL-E 3)
  - Video generation (Stability AI)
- **Conversation Management**: Save, archive, and intelligent summarization
- **Subscription System**: Multiple plans with advanced management

### 💳 **Integrated Payment Gateways**
- **Stripe**: Credit card payments
- **PayPal**: PayPal payments
- **Transaction Tracking**: Complete payment history
- **Subscription Management**: Auto-renewal and monitoring

### 🖥️ **Comprehensive Admin Dashboard**
- **Dashboard Overview**: Detailed statistics and performance indicators
- **User Management**: View, edit, and manage user accounts
- **Conversation Monitoring**: View conversations and messages with details
- **Subscription Management**: Monitor subscription status and renewals
- **Payment Management**: Track financial transactions
- **App Version Management**: Upload and manage app updates
- **Advanced Analytics**: Detailed reports on usage and revenue

## 📋 Requirements

- PHP 8.1+
- MySQL 5.7+
- Composer
- Node.js & NPM
- Redis (optional for caching)

## ⚡ Installation & Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 3. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE medicothink"

# Run migrations
php artisan migrate

# Seed sample data (optional)
php artisan db:seed
```

### 4. Build Assets
```bash
npm run build
```

### 5. Storage Setup
```bash
php artisan storage:link
```

### 6. Start Server
```bash
php artisan serve
```

## 🔧 Required Configuration

### Environment Variables (.env)
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
OPENAI_MODEL=gpt-4
ELEVENLABS_API_KEY=your_elevenlabs_key
STABILITY_API_KEY=your_stability_key

# Payment Gateways
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret

PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret
PAYPAL_MODE=sandbox

# SMS Service
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_number

# JWT Authentication
JWT_SECRET=your_jwt_secret
```

## 📱 Comprehensive API Endpoints

### Authentication
```
POST /api/auth/login          - User login
POST /api/auth/register       - Register new user
POST /api/auth/otp-login      - Send OTP
POST /api/auth/verify-otp     - Verify OTP
POST /api/auth/logout         - User logout
POST /api/auth/refresh        - Refresh token
```

### Advanced AI Features
```
POST /api/ai/text             - Smart text conversation
POST /api/ai/image-analysis   - Medical image analysis
POST /api/ai/text-to-speech   - Text to speech conversion
POST /api/ai/speech-to-text   - Speech to text conversion
POST /api/ai/generate-image   - Image generation
POST /api/ai/generate-video   - Video generation
```

### Conversations
```
GET  /api/conversations           - Get conversations
GET  /api/conversations/{id}      - Get specific conversation
POST /api/conversations/archive   - Archive conversation
POST /api/conversations/unarchive - Unarchive conversation
DELETE /api/conversations/{id}    - Delete conversation
GET  /api/conversations/{id}/summary - Conversation summary
```

### Subscriptions & Payments
```
GET  /api/subscription/status     - Subscription status
GET  /api/subscription/plans      - Subscription plans
POST /api/subscription/subscribe  - New subscription

POST /api/payment/stripe          - Stripe payment
POST /api/payment/paypal          - PayPal payment
GET  /api/payment/history         - Payment history
```

## 🖥️ Admin Dashboard

### Access
```
http://localhost:8000/admin
```

### Available Pages
- **Dashboard**: `/admin` - Comprehensive statistics
- **Users**: `/admin/users` - User management
- **Conversations**: `/admin/conversations` - Conversation monitoring
- **Subscriptions**: `/admin/subscriptions` - Subscription management
- **Payments**: `/admin/payments` - Transaction tracking
- **App Versions**: `/admin/app-versions` - Update management
- **Analytics**: `/admin/analytics` - Detailed reports
- **Settings**: `/admin/settings` - System configuration

## 🔒 Advanced Security

### JWT Authentication
- Access tokens expire in 60 minutes
- Refresh tokens expire in 14 days
- Secure password encryption with bcrypt

### API Protection
- Rate limiting for requests
- Comprehensive data validation
- CSRF & XSS protection
- Sensitive data encryption

### Payment Security
- Payment information encryption
- Transaction verification
- Fraud protection

## 📊 Comprehensive Database

### Main Tables
- `users` - User data with medical details
- `conversations` - Conversations with archive settings
- `messages` - Messages with multimedia support
- `subscriptions` - Subscriptions with multiple plans
- `payment_transactions` - Financial transactions
- `otp_codes` - Verification codes with expiration
- `conversation_summaries` - Smart conversation summaries
- `app_versions` - App versions for updates

## 🚀 Production Deployment

### 1. Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install requirements
sudo apt install php8.1 php8.1-mysql php8.1-mbstring php8.1-xml php8.1-curl mysql-server nginx redis-server -y
```

### 2. SSL Setup
```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y

# Get SSL certificate
sudo certbot --nginx -d your-domain.com
```

### 3. Nginx Configuration
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

### 4. Scheduled Tasks Setup
```bash
# Add to crontab
* * * * * cd /var/www/medicothink && php artisan schedule:run >> /dev/null 2>&1
```

### 5. Queue Workers Setup
```bash
# Create systemd service
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

## 🔧 Maintenance & Monitoring

### Update Dependencies
```bash
composer update
npm update
php artisan migrate
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Automated Backup
```bash
# Create backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p medicothink > /backups/medicothink_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/medicothink
```

### Performance Monitoring
- Use Laravel Telescope for development
- Integrate with New Relic or Datadog for production
- Monitor AI API usage
- Track subscription conversion rates

## 📈 Analytics & Reports

### Key Performance Indicators
- Daily/Weekly/Monthly active users
- Subscription growth rate
- Monthly revenue and growth rate
- AI feature usage
- User retention rate

### Financial Reports
- Monthly revenue reports
- Subscription plan performance analysis
- Cancellation and renewal rates
- AI service costs

## 🆘 Support & Help

### Documentation
- Developer Guide: `/docs/developer-guide.md`
- API Documentation: `/docs/api-documentation.md`
- Deployment Guide: `/docs/deployment-guide.md`

### Contact
- Email: support@medicothink.com
- Technical Support: tech@medicothink.com
- Emergency: emergency@medicothink.com

---

## 📱 Mobile App Features

### Core Features
- ✅ Advanced authentication system
- ✅ Real-time AI chat interface
- ✅ Medical image analysis
- ✅ Conversation management
- ✅ Subscription monitoring
- ✅ Profile settings

### Technical Features
- ✅ Offline message queuing
- ✅ Image picker (camera/gallery)
- ✅ Permission handling
- ✅ Connectivity monitoring
- ✅ Local data persistence
- ✅ Error handling

### Store Deployment Ready
- ✅ Android APK/Bundle ready
- ✅ iOS IPA ready
- ✅ App icons and splash screens
- ✅ Permissions configured
- ✅ Store metadata prepared

## 🌟 System Architecture

```
Mobile App (Flutter) ←→ Laravel API ←→ Web Dashboard
     ↓                        ↓              ↓
Local Storage          MySQL Database   Admin Panel
     ↓                        ↓              ↓
Image Storage         AI Services    Analytics & Reports
```

## 🎉 **System Ready for Production!**

### ✅ What's Included:
- ✅ Complete mobile API
- ✅ Full AI services integration
- ✅ Multiple payment gateways (Stripe & PayPal)
- ✅ Advanced admin dashboard
- ✅ Sophisticated subscription system
- ✅ Advanced security and protection
- ✅ Detailed analytics and reports
- ✅ Production deployment ready

### 🚀 Next Steps:
1. **Deploy Laravel Backend** - Follow deployment guide
2. **Configure API Keys** - Set up all service credentials
3. **Test Mobile App** - Verify all functionality
4. **Deploy to Stores** - Submit to App Store and Google Play

**The complete system is now ready for production deployment! 🎯**