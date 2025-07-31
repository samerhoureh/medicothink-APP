import 'package:flutter/foundation.dart';
import '../models/api_response.dart';
import '../models/user.dart';
import 'api_service.dart';
import 'auth_service.dart';

class SubscriptionService {
  static final SubscriptionService _instance = SubscriptionService._internal();
  factory SubscriptionService() => _instance;
  SubscriptionService._internal();

  final ApiService _apiService = ApiService();
  final AuthService _authService = AuthService();

  Future<ApiResponse<SubscriptionStatus>> getSubscriptionStatus() async {
    try {
      final response = await _apiService.getSubscriptionStatus();
      if (response.isSuccess && response.data != null) {
        final subscription = SubscriptionStatus.fromJson(response.data!);
        return ApiResponse<SubscriptionStatus>.success(
          data: subscription,
          message: response.message,
        );
      } else {
        return ApiResponse<SubscriptionStatus>.error(
          message: response.message,
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<SubscriptionStatus>.error(message: 'Failed to get subscription status: $e');
    }
  }

  Future<bool> isSubscriptionActive() async {
    try {
      final response = await getSubscriptionStatus();
      if (response.isSuccess && response.data != null) {
        return response.data!.isActive && !response.data!.isExpired;
      }
      return false;
    } catch (e) {
      debugPrint('Error checking subscription status: $e');
      return false;
    }
  }

  Future<bool> isSubscriptionExpiringSoon() async {
    try {
      final response = await getSubscriptionStatus();
      if (response.isSuccess && response.data != null) {
        return response.data!.isExpiringSoon;
      }
      return false;
    } catch (e) {
      debugPrint('Error checking subscription expiry: $e');
      return false;
    }
  }

  Future<int> getDaysRemaining() async {
    try {
      final response = await getSubscriptionStatus();
      if (response.isSuccess && response.data != null) {
        return response.data!.daysRemaining;
      }
      return 0;
    } catch (e) {
      debugPrint('Error getting days remaining: $e');
      return 0;
    }
  }

  // Check if user can access premium features
  Future<bool> canAccessPremiumFeatures() async {
    return await isSubscriptionActive();
  }

  // Handle subscription expiry
  Future<void> handleSubscriptionExpiry() async {
    try {
      // Log out user when subscription expires
      await _authService.logout();
    } catch (e) {
      debugPrint('Error handling subscription expiry: $e');
    }
  }

  // Show subscription alerts
  SubscriptionAlert? getSubscriptionAlert(SubscriptionStatus subscription) {
    if (subscription.isExpired) {
      return SubscriptionAlert(
        type: SubscriptionAlertType.expired,
        title: 'Subscription Expired',
        message: 'Your subscription has expired. Please renew to continue using the app.',
        daysRemaining: 0,
      );
    } else if (subscription.isExpiringSoon) {
      return SubscriptionAlert(
        type: SubscriptionAlertType.expiringSoon,
        title: 'Subscription Expiring Soon',
        message: 'Your subscription expires in ${subscription.daysRemaining} days. Renew now to avoid interruption.',
        daysRemaining: subscription.daysRemaining,
      );
    }
    return null;
  }
}

class SubscriptionAlert {
  final SubscriptionAlertType type;
  final String title;
  final String message;
  final int daysRemaining;

  SubscriptionAlert({
    required this.type,
    required this.title,
    required this.message,
    required this.daysRemaining,
  });
}

enum SubscriptionAlertType {
  expiringSoon,
  expired,
}