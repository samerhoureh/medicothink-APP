# MedicoThink Laravel API Backend

Laravel API backend for the MedicoThink Flutter mobile application.

## 🚀 Quick Setup

### 1. Install Dependencies
```bash
composer require tymon/jwt-auth openai-php/laravel twilio/sdk stripe/stripe-php intervention/image
```

### 2. Configure Environment
```bash
# Generate JWT secret
php artisan jwt:secret

# Update .env with your API keys
OPENAI_API_KEY=your_openai_key
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
STRIPE_SECRET=your_stripe_secret
```

### 3. Run Migrations
```bash
php artisan migrate
```

### 4. Start Server
```bash
php artisan serve
```

## 📚 API Endpoints

### Authentication
- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/profile` - Get user profile

### Chat
- `POST /api/ai/chat` - Send message to AI
- `POST /api/ai/analyze-image` - Analyze medical image

### Conversations
- `GET /api/conversations` - Get user conversations
- `POST /api/conversations/archive` - Archive conversation
- `DELETE /api/conversations/{id}` - Delete conversation

### Subscriptions
- `GET /api/subscription/status` - Get subscription status
- `GET /api/subscription/plans` - Get available plans
- `POST /api/subscription/subscribe` - Subscribe to plan

## 🔧 Configuration

Update your Flutter app's `lib/config/api_config.dart`:
```dart
static const String baseUrl = 'http://your-domain.com/api';
```

## 📱 Ready to Connect

Your Laravel backend is now ready to connect with the Flutter app!