class ApiConfig {
  // Base URL for your Laravel web dashboard API
  static const String baseUrl = 'https://your-dashboard-domain.com/api';
  
  // API Endpoints
  static const String loginEndpoint = '/auth/login';
  static const String registerEndpoint = '/auth/register';
  static const String otpLoginEndpoint = '/auth/otp-login';
  static const String verifyOtpEndpoint = '/auth/verify-otp';
  static const String refreshTokenEndpoint = '/auth/refresh';
  static const String logoutEndpoint = '/auth/logout';
  static const String profileEndpoint = '/user/profile';
  static const String updateProfileEndpoint = '/user/profile/update';
  
  // Chat endpoints
  static const String conversationsEndpoint = '/conversations';
  static const String messagesEndpoint = '/messages';
  static const String aiChatEndpoint = '/ai/chat';
  static const String imageAnalysisEndpoint = '/ai/analyze-image';
  static const String archiveConversationEndpoint = '/conversations/archive';
  static const String unarchiveConversationEndpoint = '/conversations/unarchive';
  static const String deleteConversationEndpoint = '/conversations/delete';
  static const String conversationSummaryEndpoint = '/conversations/summary';
  
  // Subscription endpoints
  static const String subscriptionStatusEndpoint = '/subscription/status';
  static const String subscriptionPlansEndpoint = '/subscription/plans';
  static const String subscribeEndpoint = '/subscription/subscribe';
  
  // Request timeout
  static const Duration requestTimeout = Duration(seconds: 30);
  static const Duration connectionTimeout = Duration(seconds: 15);
  
  // API Keys (should be set from environment variables)
  static const String apiKey = String.fromEnvironment('API_KEY', defaultValue: '');
  static const String encryptionKey = String.fromEnvironment('ENCRYPTION_KEY', defaultValue: '');
}