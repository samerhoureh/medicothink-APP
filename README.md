# MedicoThink - Flutter Mobile App

A comprehensive Flutter mobile application for AI-powered medical consultations and health management.

## 🚀 Features

### 🔐 **Authentication**
- Email/Password login and registration
- OTP-based phone number authentication
- Biometric authentication support
- Secure JWT token management

### 🤖 **AI-Powered Medical Assistant**
- **Text Conversations**: Chat with AI medical assistant
- **Image Analysis**: Upload and analyze medical images
- **Voice Chat**: Speech-to-text and text-to-speech
- **Video Generation**: AI-generated medical explanations
- **Smart Recommendations**: Personalized health insights

### 💬 **Conversation Management**
- Save and organize medical conversations
- Archive/unarchive conversations
- Conversation summaries with key insights
- Search through conversation history
- Offline conversation access

### 💳 **Subscription & Payments**
- Multiple subscription plans (Basic, Premium, Pro)
- Stripe and PayPal payment integration
- Subscription management and renewal
- Payment history tracking

### 👤 **User Profile**
- Complete profile management
- Medical history tracking
- Personal health information
- Settings and preferences

## 📱 Current Project Structure

```
medicothink/
├── lib/                              # Flutter Dart code
│   ├── main.dart                     # App entry point
│   ├── core/                         # Core functionality
│   │   ├── config/                   # App configuration
│   │   │   ├── app_config.dart       # API endpoints & constants
│   │   │   └── theme_config.dart     # UI theme configuration
│   │   ├── router/                   # Navigation setup
│   │   │   └── app_router.dart       # Go Router configuration
│   │   ├── services/                 # Core services
│   │   │   ├── api_service.dart      # HTTP client
│   │   │   ├── auth_service.dart     # Authentication
│   │   │   ├── ai_service.dart       # AI features
│   │   │   ├── conversation_service.dart # Chat management
│   │   │   ├── subscription_service.dart # Subscriptions
│   │   │   └── storage_service.dart  # Local storage
│   │   ├── providers/                # Riverpod providers
│   │   │   ├── app_providers.dart    # Service providers
│   │   │   └── auth_provider.dart    # Auth state
│   │   └── exceptions/               # Custom exceptions
│   │       └── api_exception.dart
│   └── features/                     # Feature modules
│       ├── auth/                     # Authentication
│       │   └── domain/
│       │       └── entities/
│       │           └── user_entity.dart
│       ├── chat/                     # AI Chat
│       │   └── domain/
│       │       └── entities/
│       │           ├── conversation_entity.dart
│       │           └── message_entity.dart
│       └── subscription/             # Subscriptions
│           └── domain/
│               └── entities/
│                   ├── subscription_entity.dart
│                   └── subscription_plan_entity.dart
├── android/                          # Android configuration
│   └── app/
│       ├── build.gradle
│       └── src/main/
│           ├── AndroidManifest.xml
│           └── kotlin/com/medicothink/app/
│               └── MainActivity.kt
├── ios/                              # iOS configuration
│   └── Runner/
│       └── Info.plist
├── web/                              # Web configuration
│   ├── index.html
│   └── manifest.json
├── pubspec.yaml                      # Flutter dependencies
├── analysis_options.yaml            # Dart code analysis
├── README.md                         # This file
└── .gitignore                        # Git ignore rules
```

## 🛠️ Tech Stack

- **Framework**: Flutter 3.x
- **State Management**: Riverpod
- **Navigation**: Go Router
- **HTTP Client**: Dio + Retrofit
- **Local Storage**: Hive + SharedPreferences
- **Authentication**: JWT + Local Auth
- **Media**: Image Picker, Audio Players
- **UI**: Material Design 3

## 📦 Dependencies

### Core Dependencies
```yaml
flutter_riverpod: ^2.4.9      # State management
go_router: ^12.1.3            # Navigation
dio: ^5.4.0                   # HTTP client
hive_flutter: ^1.1.0          # Local database
shared_preferences: ^2.2.2    # Simple storage
```

### UI & Media
```yaml
cached_network_image: ^3.3.0  # Image caching
flutter_svg: ^2.0.9           # SVG support
lottie: ^2.7.0                # Animations
image_picker: ^1.0.4          # Camera/Gallery
audioplayers: ^5.2.1          # Audio playback
```

### Authentication & Security
```yaml
local_auth: ^2.1.7            # Biometric auth
jwt_decoder: ^2.0.1           # JWT handling
crypto: ^3.0.3                # Encryption
```

## 🚀 Getting Started

### Prerequisites
- Flutter SDK (3.0.0 or higher)
- Dart SDK (3.0.0 or higher)
- Android Studio / VS Code
- iOS development setup (for iOS builds)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/your-username/medicothink-flutter.git
cd medicothink-flutter
```

2. **Install dependencies**
```bash
flutter pub get
```

3. **Configure API endpoints**
Edit `lib/core/config/app_config.dart` and update the base URL:
```dart
static const String baseUrl = 'https://your-api-domain.com/api';
```

4. **Run the app**
```bash
# Debug mode
flutter run

# Release mode
flutter run --release
```

## 🔧 Configuration

### API Configuration
Update the API base URL in `lib/core/config/app_config.dart`:
```dart
class AppConfig {
  static const String baseUrl = 'https://your-backend-url.com/api';
  // ... other configurations
}
```

### Theme Customization
Modify colors and styles in `lib/core/config/theme_config.dart`:
```dart
class ThemeConfig {
  static const Color primaryColor = Color(0xFF2563EB);
  // ... other theme settings
}
```

## 🧪 Testing

### Run Tests
```bash
# Unit tests
flutter test

# Integration tests
flutter test integration_test/
```

## 📱 Build & Release

### Android
```bash
# Build APK
flutter build apk --release

# Build App Bundle
flutter build appbundle --release
```

### iOS
```bash
# Build iOS
flutter build ios --release
```

## 🔐 Security Features

- **JWT Token Management**: Secure token storage and refresh
- **Biometric Authentication**: Fingerprint/Face ID support
- **API Security**: Request/response encryption
- **Local Data Encryption**: Sensitive data protection
- **Certificate Pinning**: Network security

## 🌐 Localization

The app supports multiple languages:
- English (default)
- Arabic
- Spanish
- French

Add new languages in `lib/l10n/` directory.

## 📊 Analytics & Monitoring

- **Firebase Analytics**: User behavior tracking
- **Crashlytics**: Crash reporting
- **Performance Monitoring**: App performance metrics

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support and questions:
- Email: support@medicothink.com
- Documentation: [docs.medicothink.com](https://docs.medicothink.com)
- Issues: [GitHub Issues](https://github.com/your-username/medicothink-flutter/issues)

## 🚀 Roadmap

- [ ] Complete UI implementation for all screens
- [ ] Add presentation layer (pages and widgets)
- [ ] Implement state providers for features
- [ ] Add comprehensive testing
- [ ] Offline AI capabilities
- [ ] Wearable device integration
- [ ] Telemedicine video calls
- [ ] Health data synchronization
- [ ] Advanced analytics dashboard
- [ ] Multi-language AI support

---

**MedicoThink** - Empowering healthcare through AI technology 🏥✨