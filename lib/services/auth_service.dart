import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/api_response.dart';
import 'api_service.dart';

class AuthService {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  final ApiService _apiService = ApiService();
  User? _currentUser;

  User? get currentUser => _currentUser;
  bool get isAuthenticated => _apiService.isAuthenticated && _currentUser != null;

  Future<void> initialize() async {
    await _apiService.initialize();
    if (_apiService.isAuthenticated) {
      // Try to get user profile to validate token
      await getCurrentUser();
    }
  }

  Future<ApiResponse<User>> login(String email, String password) async {
    try {
      final response = await _apiService.login(email, password);
      if (response.isSuccess && response.data != null) {
        _currentUser = response.data;
      }
      return response;
    } catch (e) {
      return ApiResponse<User>.error(message: 'Login failed: $e');
    }
  }

  Future<ApiResponse<User>> register(Map<String, dynamic> userData) async {
    try {
      final response = await _apiService.register(userData);
      if (response.isSuccess && response.data != null) {
        _currentUser = response.data;
      }
      return response;
    } catch (e) {
      return ApiResponse<User>.error(message: 'Registration failed: $e');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> sendOtp(String phoneNumber) async {
    try {
      return await _apiService.sendOtp(phoneNumber);
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>.error(message: 'Failed to send OTP: $e');
    }
  }

  Future<ApiResponse<User>> verifyOtp(String phoneNumber, String otp) async {
    try {
      final response = await _apiService.verifyOtp(phoneNumber, otp);
      if (response.isSuccess && response.data != null) {
        _currentUser = response.data;
      }
      return response;
    } catch (e) {
      return ApiResponse<User>.error(message: 'OTP verification failed: $e');
    }
  }

  Future<ApiResponse<User>> getCurrentUser() async {
    try {
      // This would be implemented in ApiService to get current user profile
      // For now, we'll return the cached user
      if (_currentUser != null) {
        return ApiResponse<User>.success(
          data: _currentUser!,
          message: 'User retrieved successfully',
        );
      } else {
        return ApiResponse<User>.error(message: 'No user found');
      }
    } catch (e) {
      return ApiResponse<User>.error(message: 'Failed to get user: $e');
    }
  }

  Future<void> logout() async {
    try {
      await _apiService.logout();
    } catch (e) {
      debugPrint('Logout error: $e');
    } finally {
      _currentUser = null;
    }
  }

  // Check subscription status
  Future<bool> checkSubscriptionStatus() async {
    if (_currentUser?.subscription == null) return false;
    
    final subscription = _currentUser!.subscription!;
    return subscription.isActive && !subscription.isExpired;
  }

  // Check if subscription is expiring soon
  bool isSubscriptionExpiringSoon() {
    if (_currentUser?.subscription == null) return false;
    return _currentUser!.subscription!.isExpiringSoon;
  }

  // Get days remaining in subscription
  int getSubscriptionDaysRemaining() {
    if (_currentUser?.subscription == null) return 0;
    return _currentUser!.subscription!.daysRemaining;
  }
}