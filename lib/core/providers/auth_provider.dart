import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../services/auth_service.dart';
import '../../features/auth/domain/entities/user_entity.dart';
import 'app_providers.dart';

// Auth State Provider
final authStateProvider = StreamProvider<UserEntity?>((ref) {
  final authService = ref.watch(authServiceProvider);
  return authService.authStateChanges;
});

// Current User Provider
final currentUserProvider = Provider<UserEntity?>((ref) {
  return ref.watch(authStateProvider).when(
    data: (user) => user,
    loading: () => null,
    error: (_, __) => null,
  );
});

// Auth Loading Provider
final authLoadingProvider = StateProvider<bool>((ref) => false);

// Login Provider
final loginProvider = FutureProvider.family<UserEntity, Map<String, String>>((ref, credentials) async {
  final authService = ref.watch(authServiceProvider);
  ref.read(authLoadingProvider.notifier).state = true;
  
  try {
    final user = await authService.login(
      email: credentials['email']!,
      password: credentials['password']!,
    );
    return user;
  } finally {
    ref.read(authLoadingProvider.notifier).state = false;
  }
});

// Register Provider
final registerProvider = FutureProvider.family<UserEntity, Map<String, String>>((ref, userData) async {
  final authService = ref.watch(authServiceProvider);
  ref.read(authLoadingProvider.notifier).state = true;
  
  try {
    final user = await authService.register(
      name: userData['name']!,
      email: userData['email']!,
      password: userData['password']!,
      phoneNumber: userData['phoneNumber'],
    );
    return user;
  } finally {
    ref.read(authLoadingProvider.notifier).state = false;
  }
});

// OTP Login Provider
final otpLoginProvider = FutureProvider.family<bool, String>((ref, phoneNumber) async {
  final authService = ref.watch(authServiceProvider);
  ref.read(authLoadingProvider.notifier).state = true;
  
  try {
    return await authService.sendOtp(phoneNumber);
  } finally {
    ref.read(authLoadingProvider.notifier).state = false;
  }
});

// Verify OTP Provider
final verifyOtpProvider = FutureProvider.family<UserEntity, Map<String, String>>((ref, otpData) async {
  final authService = ref.watch(authServiceProvider);
  ref.read(authLoadingProvider.notifier).state = true;
  
  try {
    final user = await authService.verifyOtp(
      phoneNumber: otpData['phoneNumber']!,
      code: otpData['code']!,
    );
    return user;
  } finally {
    ref.read(authLoadingProvider.notifier).state = false;
  }
});

// Logout Provider
final logoutProvider = FutureProvider<void>((ref) async {
  final authService = ref.watch(authServiceProvider);
  await authService.logout();
});