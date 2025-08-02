import 'api_service.dart';
import '../config/app_config.dart';
import '../exceptions/api_exception.dart';
import '../../features/subscription/domain/entities/subscription_entity.dart';
import '../../features/subscription/domain/entities/subscription_plan_entity.dart';

class SubscriptionService {
  final ApiService _apiService;
  
  SubscriptionService(this._apiService);
  
  Future<SubscriptionEntity?> getSubscriptionStatus() async {
    try {
      final response = await _apiService.get(AppConfig.subscriptionStatusEndpoint);
      
      if (response['success'] == true) {
        final subscriptionData = response['data']['subscription'];
        if (subscriptionData != null) {
          return SubscriptionEntity.fromJson(subscriptionData as Map<String, dynamic>);
        }
      }
      
      return null;
    } catch (e) {
      rethrow;
    }
  }
  
  Future<List<SubscriptionPlanEntity>> getSubscriptionPlans() async {
    try {
      final response = await _apiService.get(AppConfig.subscriptionPlansEndpoint);
      
      if (response['success'] == true) {
        final plansData = response['data']['plans'] as List;
        return plansData
            .map((p) => SubscriptionPlanEntity.fromJson(p as Map<String, dynamic>))
            .toList();
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to fetch subscription plans',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<Map<String, dynamic>> subscribe({
    required String planType,
    required String paymentMethod,
    Map<String, dynamic>? paymentData,
  }) async {
    try {
      final body = {
        'plan_type': planType,
        'payment_method': paymentMethod,
      };
      
      if (paymentData != null) {
        body.addAll(paymentData);
      }
      
      final response = await _apiService.post(
        AppConfig.subscribeEndpoint,
        body: body,
      );
      
      if (response['success'] == true) {
        return response['data'] as Map<String, dynamic>;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to create subscription',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<void> cancelSubscription() async {
    try {
      final response = await _apiService.post(AppConfig.cancelSubscriptionEndpoint);
      
      if (response['success'] != true) {
        throw ApiException(
          message: response['message'] ?? 'Failed to cancel subscription',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<List<Map<String, dynamic>>> getPaymentHistory() async {
    try {
      final response = await _apiService.get(AppConfig.paymentHistoryEndpoint);
      
      if (response['success'] == true) {
        return List<Map<String, dynamic>>.from(response['data']['payments']);
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to fetch payment history',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<Map<String, dynamic>> processStripePayment({
    required double amount,
    required String currency,
    String? subscriptionId,
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.stripePaymentEndpoint,
        body: {
          'amount': amount,
          'currency': currency,
          if (subscriptionId != null) 'subscription_id': subscriptionId,
        },
      );
      
      if (response['success'] == true) {
        return response['data'] as Map<String, dynamic>;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to process Stripe payment',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<Map<String, dynamic>> processPayPalPayment({
    required double amount,
    required String currency,
    String? subscriptionId,
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.paypalPaymentEndpoint,
        body: {
          'amount': amount,
          'currency': currency,
          if (subscriptionId != null) 'subscription_id': subscriptionId,
        },
      );
      
      if (response['success'] == true) {
        return response['data'] as Map<String, dynamic>;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to process PayPal payment',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
}