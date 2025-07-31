import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import '../utils/helpers.dart';

class ErrorHandlerService {
  static final ErrorHandlerService _instance = ErrorHandlerService._internal();
  factory ErrorHandlerService() => _instance;
  ErrorHandlerService._internal();

  // Handle and log errors
  void handleError(dynamic error, {StackTrace? stackTrace, String? context}) {
    debugPrint('Error in $context: $error');
    if (stackTrace != null) {
      debugPrint('Stack trace: $stackTrace');
    }
    
    // In production, you might want to send errors to a crash reporting service
    if (kReleaseMode) {
      _reportError(error, stackTrace, context);
    }
  }

  // Show user-friendly error messages
  void showErrorToUser(BuildContext context, dynamic error) {
    String message = _getUserFriendlyMessage(error);
    AppHelpers.showSnackBar(
      context,
      message,
      type: SnackBarType.error,
    );
  }

  // Convert technical errors to user-friendly messages
  String _getUserFriendlyMessage(dynamic error) {
    final errorString = error.toString().toLowerCase();
    
    if (errorString.contains('socket') || errorString.contains('network')) {
      return 'فشل في الاتصال بالإنترنت. يرجى التحقق من اتصالك.';
    } else if (errorString.contains('timeout')) {
      return 'انتهت مهلة الطلب. يرجى المحاولة مرة أخرى.';
    } else if (errorString.contains('unauthorized') || errorString.contains('401')) {
      return 'انتهت صلاحية جلسة العمل. يرجى تسجيل الدخول مرة أخرى.';
    } else if (errorString.contains('forbidden') || errorString.contains('403')) {
      return 'ليس لديك صلاحية للوصول إلى هذا المحتوى.';
    } else if (errorString.contains('not found') || errorString.contains('404')) {
      return 'المحتوى المطلوب غير موجود.';
    } else if (errorString.contains('server') || errorString.contains('500')) {
      return 'خطأ في الخادم. يرجى المحاولة لاحقاً.';
    } else {
      return 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.';
    }
  }

  // Report errors to crash reporting service (implement based on your service)
  void _reportError(dynamic error, StackTrace? stackTrace, String? context) {
    // Implement crash reporting here (Firebase Crashlytics, Sentry, etc.)
    debugPrint('Reporting error to crash service: $error');
  }

  // Handle specific API errors
  void handleApiError(BuildContext context, int? statusCode, String? message) {
    switch (statusCode) {
      case 401:
        _handleUnauthorized(context);
        break;
      case 403:
        AppHelpers.showSnackBar(
          context,
          'ليس لديك صلاحية للوصول إلى هذا المحتوى',
          type: SnackBarType.error,
        );
        break;
      case 422:
        AppHelpers.showSnackBar(
          context,
          message ?? 'البيانات المدخلة غير صحيحة',
          type: SnackBarType.error,
        );
        break;
      case 500:
        AppHelpers.showSnackBar(
          context,
          'خطأ في الخادم. يرجى المحاولة لاحقاً',
          type: SnackBarType.error,
        );
        break;
      default:
        AppHelpers.showSnackBar(
          context,
          message ?? 'حدث خطأ غير متوقع',
          type: SnackBarType.error,
        );
    }
  }

  void _handleUnauthorized(BuildContext context) {
    AppHelpers.showSnackBar(
      context,
      'انتهت صلاحية جلسة العمل',
      type: SnackBarType.error,
    );
    
    // Navigate to login screen
    Navigator.of(context).pushNamedAndRemoveUntil(
      '/login',
      (route) => false,
    );
  }
}