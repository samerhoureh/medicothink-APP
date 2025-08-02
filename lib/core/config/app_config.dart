class AppConfig {
  static const String appName = 'MedicoThink';
  static const String appVersion = '1.0.0';
  static const String appDescription = 'AI-Powered Medical Assistant';
  
  // API Configuration
  static const String baseUrl = 'http://localhost:8000/api';
  static const String storageUrl = 'http://localhost:8000/storage';
  
  // API Endpoints
  static const String loginEndpoint = '/auth/login';
  static const String registerEndpoint = '/auth/register';
  static const String otpLoginEndpoint = '/auth/otp-login';
  static const String verifyOtpEndpoint = '/auth/verify-otp';
  static const String logoutEndpoint = '/auth/logout';
  static const String refreshTokenEndpoint = '/auth/refresh';
  static const String profileEndpoint = '/auth/me';
  static const String updateProfileEndpoint = '/auth/update-profile';
  
  // AI Endpoints
  static const String textChatEndpoint = '/ai/text';
  static const String imageAnalysisEndpoint = '/ai/image-analysis';
  static const String textToSpeechEndpoint = '/ai/text-to-speech';
  static const String speechToTextEndpoint = '/ai/speech-to-text';
  static const String generateImageEndpoint = '/ai/generate-image';
  static const String generateVideoEndpoint = '/ai/generate-video';
  
  // Conversation Endpoints
  static const String conversationsEndpoint = '/conversations';
  static const String archiveConversationEndpoint = '/conversations/{id}/archive';
  static const String unarchiveConversationEndpoint = '/conversations/{id}/unarchive';
  static const String conversationSummaryEndpoint = '/conversations/{id}/summary';
  
  // Subscription Endpoints
  static const String subscriptionStatusEndpoint = '/subscription/status';
  static const String subscriptionPlansEndpoint = '/subscription/plans';
  static const String subscribeEndpoint = '/subscription/subscribe';
  static const String cancelSubscriptionEndpoint = '/subscription/cancel';
  
  // Payment Endpoints
  static const String stripePaymentEndpoint = '/payment/stripe';
  static const String paypalPaymentEndpoint = '/payment/paypal';
  static const String paymentHistoryEndpoint = '/payment/history';
  
  // App Constants
  static const int maxMessageLength = 1000;
  static const int maxImageSize = 5 * 1024 * 1024; // 5MB
  static const int maxAudioDuration = 300; // 5 minutes
  static const int conversationPageSize = 20;
  static const int messagePageSize = 50;
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String conversationsKey = 'conversations';
  static const String settingsKey = 'app_settings';
  static const String themeKey = 'theme_mode';
  
  // Feature Flags
  static const bool enableVoiceChat = true;
  static const bool enableImageAnalysis = true;
  static const bool enableVideoGeneration = true;
  static const bool enableOfflineMode = true;
  static const bool enableBiometricAuth = true;
  
  // Subscription Plans
  static const Map<String, Map<String, dynamic>> subscriptionPlans = {
    'basic': {
      'name': 'Basic Plan',
      'price': 9.99,
      'currency': 'USD',
      'features': [
        '50 AI conversations per month',
        '10 image analyses per month',
        'Basic conversation history',
        'Email support',
      ],
    },
    'premium': {
      'name': 'Premium Plan',
      'price': 19.99,
      'currency': 'USD',
      'features': [
        '200 AI conversations per month',
        '50 image analyses per month',
        'Full conversation history',
        'Priority support',
        'Advanced AI features',
        'Voice chat',
      ],
    },
    'pro': {
      'name': 'Pro Plan',
      'price': 39.99,
      'currency': 'USD',
      'features': [
        'Unlimited AI conversations',
        'Unlimited image analyses',
        'Full conversation history',
        '24/7 priority support',
        'All advanced AI features',
        'Voice chat',
        'Video generation',
        'API access',
      ],
    },
  };
}