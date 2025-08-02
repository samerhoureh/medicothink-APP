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
      return 'Failed to connect to the internet. Please check your connection.';
    } else if (errorString.contains('timeout')) {
      return 'Request timeout. Please try again.';
    } else if (errorString.contains('unauthorized') || errorString.contains('401')) {
      return 'Session expired. Please login again.';
    } else if (errorString.contains('forbidden') || errorString.contains('403')) {
      return 'You do not have permission to access this content.';
    } else if (errorString.contains('not found') || errorString.contains('404')) {
      return 'The requested content was not found.';
    } else if (errorString.contains('server') || errorString.contains('500')) {
      return 'Server error. Please try again later.';
    } else {
      return 'An unexpected error occurred. Please try again.';
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
          'You do not have permission to access this content',
          type: SnackBarType.error,
        );
        break;
      case 422:
        AppHelpers.showSnackBar(
          context,
          message ?? 'Invalid input data',
          type: SnackBarType.error,
        );
        break;
      case 500:
        AppHelpers.showSnackBar(
          context,
          'Server error. Please try again later',
          type: SnackBarType.error,
        );
        break;
      default:
        AppHelpers.showSnackBar(
          context,
          message ?? 'An unexpected error occurred',
          type: SnackBarType.error,
        );
    }
  }

  void _handleUnauthorized(BuildContext context) {
    AppHelpers.showSnackBar(
      context,
      'Session expired',
      type: SnackBarType.error,
    );
    
    // Navigate to login screen
    Navigator.of(context).pushNamedAndRemoveUntil(
      '/login',
      (route) => false,
    );
  }
}