# MedicoThink Laravel API Backend

Laravel API backend for the MedicoThink Flutter mobile application - A comprehensive medical AI assistant platform.

## 🚀 Features

### 🔐 Authentication System
- JWT-based authentication
- Email/password login
- OTP login via phone number
- User registration with medical profile
- Token refresh mechanism
- Secure logout

### 🤖 AI Integration
- OpenAI GPT integration for medical conversations
- Image analysis capabilities
- Conversation summarization
- Medical advice generation
- Context-aware responses

### 💬 Conversation Management
- Real-time chat with AI
- Conversation archiving/unarchiving
- Message history storage
- Conversation summaries with flash cards
- Image message support

### 💳 Subscription System
- Multiple subscription plans (Basic, Premium, Professional)
- Subscription status monitoring
- Automatic expiry handling
- Grace period management
- Stripe integration ready

### 📱 Mobile API Features
- RESTful API design
- JSON responses
- Error handling
- Rate limiting
- CORS support
- File upload support

## 🛠 Installation

### Prerequisites
- PHP 8.1+
- Composer
- MySQL/PostgreSQL
- Redis (optional)
- Node.js & NPM (for frontend assets)

### Setup Steps

1. **Clone the repository**
```bash
git clone <repository-url>
cd laravel-backend
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

4. **Configure environment variables**
Edit `.env` file with your settings:
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medicothink
DB_USERNAME=your_username
DB_PASSWORD=your_password

# JWT
JWT_SECRET=your_jwt_secret

# OpenAI
OPENAI_API_KEY=your_openai_api_key

# Twilio (for OTP)
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token
TWILIO_FROM=your_twilio_phone

# Stripe (for payments)
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
```

5. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

6. **Storage setup**
```bash
php artisan storage:link
```

7. **Start the server**
```bash
php artisan serve
```

## 📚 API Documentation

### Authentication Endpoints

#### Register User
```http
POST /api/auth/register
Content-Type: application/json

{
    "username": "Dr. John Doe",
    "email": "john@example.com",
    "phone_number": "+1234567890",
    "password": "password123",
    "password_confirmation": "password123",
    "age": 35,
    "city": "New York",
    "nationality": "American",
    "specialization": "Medical",
    "education_level": "Master"
}
```

#### Login
```http
POST /api/auth/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

#### OTP Login
```http
POST /api/auth/otp-login
Content-Type: application/json

{
    "phone_number": "+1234567890"
}
```

#### Verify OTP
```http
POST /api/auth/verify-otp
Content-Type: application/json

{
    "phone_number": "+1234567890",
    "otp": "1234"
}
```

### Chat Endpoints

#### Send Message
```http
POST /api/ai/chat
Authorization: Bearer {token}
Content-Type: application/json

{
    "conversation_id": "conv_123456789",
    "message": "I have a headache, what should I do?"
}
```

#### Analyze Image
```http
POST /api/ai/analyze-image
Authorization: Bearer {token}
Content-Type: multipart/form-data

conversation_id: conv_123456789
image: [image file]
```

#### Get Conversations
```http
GET /api/conversations
Authorization: Bearer {token}
```

#### Get Conversation Summary
```http
GET /api/conversations/{conversationId}/summary
Authorization: Bearer {token}
```

### Subscription Endpoints

#### Get Subscription Status
```http
GET /api/subscription/status
Authorization: Bearer {token}
```

#### Subscribe to Plan
```http
POST /api/subscription/subscribe
Authorization: Bearer {token}
Content-Type: application/json

{
    "plan_id": "premium",
    "payment_method": "stripe_payment_method_id"
}
```

## 🏗 Architecture

### Models
- **User**: User accounts with medical profiles
- **Conversation**: Chat conversations between users and AI
- **Message**: Individual messages in conversations
- **Subscription**: User subscription management
- **ConversationSummary**: AI-generated conversation summaries
- **OtpCode**: OTP verification codes

### Services
- **OpenAIService**: AI integration and response generation
- **TwilioService**: SMS and OTP delivery
- **SubscriptionService**: Subscription management

### Middleware
- **CheckActiveSubscription**: Ensures user has active subscription
- **JWT Authentication**: Token-based authentication

### Resources
- **UserResource**: User data transformation
- **ConversationResource**: Conversation data transformation
- **MessageResource**: Message data transformation
- **SubscriptionResource**: Subscription data transformation

## 🔒 Security Features

### Authentication & Authorization
- JWT tokens with expiration
- Refresh token mechanism
- Role-based access control
- API rate limiting

### Data Protection
- Password hashing (bcrypt)
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF protection

### API Security
- CORS configuration
- Request size limits
- File upload validation
- Secure headers

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Feature Tests
- Authentication flow testing
- API endpoint testing
- Subscription management testing
- AI integration testing

### Unit Tests
- Model testing
- Service testing
- Validation testing

## 📊 Monitoring & Logging

### Logging
- Application logs
- Error tracking
- API request logging
- Performance monitoring

### Health Checks
```http
GET /api/health
```

## 🚀 Deployment

### Production Setup
1. Configure production environment
2. Set up SSL certificates
3. Configure web server (Nginx/Apache)
4. Set up database
5. Configure Redis for caching
6. Set up queue workers
7. Configure monitoring

### Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Production database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=medicothink_prod

# Production services
OPENAI_API_KEY=prod_openai_key
TWILIO_SID=prod_twilio_sid
STRIPE_SECRET=prod_stripe_secret
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🆘 Support

For support and questions:
- Email: support@medicothink.com
- Documentation: https://docs.medicothink.com
- Issues: GitHub Issues

## 🔄 Version History

### v1.0.0
- Initial release
- Complete authentication system
- AI chat integration
- Subscription management
- Conversation archiving
- Flash card summaries

---

**Built with ❤️ for the medical community**