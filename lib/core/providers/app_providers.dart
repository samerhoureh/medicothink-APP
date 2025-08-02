import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/storage_service.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/ai_service.dart';
import '../services/conversation_service.dart';
import '../services/subscription_service.dart';

// Storage Service Provider
final storageServiceProvider = Provider<StorageService>((ref) {
  throw UnimplementedError('StorageService must be overridden');
});

// API Service Provider
final apiServiceProvider = Provider<ApiService>((ref) {
  throw UnimplementedError('ApiService must be overridden');
});

// Auth Service Provider
final authServiceProvider = Provider<AuthService>((ref) {
  throw UnimplementedError('AuthService must be overridden');
});

// AI Service Provider
final aiServiceProvider = Provider<AiService>((ref) {
  final apiService = ref.watch(apiServiceProvider);
  return AiService(apiService);
});

// Conversation Service Provider
final conversationServiceProvider = Provider<ConversationService>((ref) {
  final apiService = ref.watch(apiServiceProvider);
  final storageService = ref.watch(storageServiceProvider);
  return ConversationService(apiService, storageService);
});

// Subscription Service Provider
final subscriptionServiceProvider = Provider<SubscriptionService>((ref) {
  final apiService = ref.watch(apiServiceProvider);
  return SubscriptionService(apiService);
});