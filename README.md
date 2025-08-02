# MedicoThink - Medical AI Assistant Mobile App

A Flutter mobile application that provides AI-powered medical assistance and consultation.

## 📱 Mobile Application Features

### 🔐 Authentication System
- Email/password login
- OTP login via phone number
- User registration with medical profile
- Secure token-based authentication

### 🤖 AI Medical Assistant
- Real-time chat with medical AI
- Medical image analysis
- Conversation summaries with flash cards
- Context-aware medical advice

### 💬 Conversation Management
- Save conversations automatically
- Archive old conversations
- Search through conversation history
- Sync with backend server

### 📱 Mobile-Optimized UI
- Modern, responsive design
- Arabic language support
- Smooth animations
- Optimized user experience

### 💳 Subscription Management
- Monitor subscription status
- Subscription expiry alerts
- Account lockout on expiry
- Multiple subscription plans

## 🛠 Technology Stack

### Mobile App (Flutter)
- **Flutter**: Cross-platform mobile framework
- **Dart**: Programming language
- **HTTP**: API communication
- **SharedPreferences**: Local data storage
- **ImagePicker**: Camera and gallery access
- **PermissionHandler**: Device permissions

### Backend Integration
- **RESTful APIs**: Communication with Laravel backend
- **JWT Authentication**: Secure token-based auth
- **File Upload**: Image analysis capabilities
- **Real-time Sync**: Conversation synchronization

## 📁 Project Structure

```
lib/
├── config/           # App configuration
├── models/           # Data models
├── services/         # Business logic services
├── UI/              # User interface screens
│   ├── auth/        # Authentication screens
│   ├── home/        # Main app screens
│   ├── splash/      # Onboarding screens
│   └── widgets/     # Reusable UI components
├── utils/           # Helper utilities
└── main.dart        # App entry point
```

## 🚀 Getting Started

### Prerequisites
- Flutter SDK (3.0+)
- Dart SDK (3.0+)
- Android Studio / VS Code
- Android/iOS device or emulator

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/your-repo/medicothink-mobile.git
cd medicothink-mobile
```

2. **Install dependencies**
```bash
flutter pub get
```

3. **Configure API endpoint**
Update `lib/config/api_config.dart` with your backend URL:
```dart
static const String baseUrl = 'https://your-api-domain.com/api';
```

4. **Run the app**
```bash
flutter run
```

## 🔧 Configuration

### API Configuration
Update the API base URL in `lib/config/api_config.dart`:
```dart
class ApiConfig {
  static const String baseUrl = 'https://your-backend-domain.com/api';
  // ... other configurations
}
```

### Permissions
The app requires the following permissions:
- **Camera**: For taking medical photos
- **Photo Library**: For selecting images
- **Internet**: For API communication
- **Storage**: For temporary file storage

## 📱 Mobile App Features

### Authentication Flow
1. Splash screen with app branding
2. Onboarding screens explaining features
3. Login/Register options
4. OTP verification for phone login
5. Profile setup and management

### Main Chat Interface
1. AI-powered medical conversations
2. Image upload and analysis
3. Conversation history sidebar
4. Real-time message sync
5. Offline message queuing

### Conversation Management
1. Automatic conversation saving
2. Archive/unarchive functionality
3. Conversation search and filtering
4. Summary generation with flash cards
5. Export conversation data

## 🔒 Security Features

### Data Protection
- Secure token storage
- Encrypted API communication
- Local data encryption
- Automatic session management

### Privacy
- No permanent image storage
- Conversation data encryption
- User consent for data collection
- GDPR compliance ready

## 🧪 Testing

### Run Tests
```bash
flutter test
```

### Build for Release
```bash
# Android
flutter build apk --release

# iOS
flutter build ios --release
```

## 📦 Backend Requirements

The mobile app requires a Laravel backend with the following endpoints:

### Authentication
- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/otp-login`
- `POST /api/auth/verify-otp`

### Chat & AI
- `POST /api/ai/chat`
- `POST /api/ai/analyze-image`
- `GET /api/conversations`
- `GET /api/conversations/{id}/summary`

### Subscription
- `GET /api/subscription/status`
- `POST /api/subscription/subscribe`

## 🚀 Deployment

### Android Play Store
1. Build release APK
2. Sign with release keystore
3. Upload to Play Console
4. Configure app listing

### iOS App Store
1. Build for iOS release
2. Archive in Xcode
3. Upload to App Store Connect
4. Submit for review

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support:
- Email: support@medicothink.com
- Documentation: https://docs.medicothink.com
- Issues: GitHub Issues

---

**Built for mobile-first medical assistance**