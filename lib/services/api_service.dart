import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../models/api_response.dart';
import '../models/user.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String? _accessToken;
  String? _refreshToken;
  late http.Client _httpClient;

  // Initialize service
  Future<void> initialize() async {
    _httpClient = http.Client();
    final prefs = await SharedPreferences.getInstance();
    _accessToken = prefs.getString('access_token');
    _refreshToken = prefs.getString('refresh_token');
  }

  // Save tokens securely
  Future<void> _saveTokens(String accessToken, String refreshToken) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _accessToken = accessToken;
      _refreshToken = refreshToken;
      await prefs.setString('access_token', accessToken);
      await prefs.setString('refresh_token', refreshToken);
    } catch (e) {
      debugPrint('Error saving tokens: $e');
      rethrow;
    }
  }

  // Clear tokens
  Future<void> clearTokens() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _accessToken = null;
      _refreshToken = null;
      await prefs.remove('access_token');
      await prefs.remove('refresh_token');
    } catch (e) {
      debugPrint('Error clearing tokens: $e');
    }
  }

  // Get headers with authentication
  Map<String, String> _getHeaders({bool includeAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'User-Agent': 'MedicoThink-Mobile/1.0',
    };
    
    if (ApiConfig.apiKey.isNotEmpty) {
      headers['X-API-Key'] = ApiConfig.apiKey;
    }
    
    if (includeAuth && _accessToken != null) {
      headers['Authorization'] = 'Bearer $_accessToken';
    }
    
    return headers;
  }

  // Handle API response with better error handling
  ApiResponse<T> _handleResponse<T>(
    http.Response response, 
    T Function(Map<String, dynamic>) fromJson,
  ) {
    try {
      if (response.statusCode == 204) {
        // No content response
        return ApiResponse<T>.success(
          data: fromJson({}),
          message: 'Success',
        );
      }

      final Map<String, dynamic> data = json.decode(response.body);
      
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return ApiResponse<T>.success(
          data: fromJson(data['data'] ?? data),
          message: data['message'] ?? 'Success',
        );
      } else {
        return ApiResponse<T>.error(
          message: data['message'] ?? _getErrorMessage(response.statusCode),
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      debugPrint('Response parsing error: $e');
      return ApiResponse<T>.error(
        message: 'Failed to parse response: ${e.toString()}',
        statusCode: response.statusCode,
      );
    }
  }

  String _getErrorMessage(int statusCode) {
    switch (statusCode) {
      case 400:
        return 'Bad request';
      case 401:
        return 'Unauthorized access';
      case 403:
        return 'Access forbidden';
      case 404:
        return 'Resource not found';
      case 422:
        return 'Validation error';
      case 500:
        return 'Internal server error';
      case 503:
        return 'Service unavailable';
      default:
        return 'An error occurred';
    }
  }

  // Refresh access token
  Future<bool> _refreshAccessToken() async {
    if (_refreshToken == null) return false;

    try {
      final response = await _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.refreshTokenEndpoint}'),
        headers: _getHeaders(includeAuth: false),
        body: json.encode({'refresh_token': _refreshToken}),
      ).timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        await _saveTokens(data['access_token'], data['refresh_token']);
        return true;
      }
    } catch (e) {
      debugPrint('Token refresh failed: $e');
    }
    
    return false;
  }

  // Make authenticated request with retry logic
  Future<http.Response> _makeAuthenticatedRequest(
    Future<http.Response> Function() request,
  ) async {
    try {
      http.Response response = await request();
      
      // If token expired, try to refresh and retry
      if (response.statusCode == 401 && _refreshToken != null) {
        final refreshed = await _refreshAccessToken();
        if (refreshed) {
          response = await request();
        }
      }
      
      return response;
    } catch (e) {
      debugPrint('Request failed: $e');
      rethrow;
    }
  }

  // Authentication methods
  Future<ApiResponse<User>> login(String email, String password) async {
    try {
      final response = await _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.loginEndpoint}'),
        headers: _getHeaders(includeAuth: false),
        body: json.encode({
          'email': email,
          'password': password,
        }),
      ).timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        await _saveTokens(data['access_token'], data['refresh_token']);
        return ApiResponse<User>.success(
          data: User.fromJson(data['user']),
          message: data['message'] ?? 'Login successful',
        );
      } else {
        final data = json.decode(response.body);
        return ApiResponse<User>.error(
          message: data['message'] ?? 'Login failed',
          statusCode: response.statusCode,
        );
      }
    } on SocketException {
      return ApiResponse<User>.error(message: 'No internet connection');
    } on HttpException {
      return ApiResponse<User>.error(message: 'Server error occurred');
    } catch (e) {
      return ApiResponse<User>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<User>> register(Map<String, dynamic> userData) async {
    try {
      final response = await _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.registerEndpoint}'),
        headers: _getHeaders(includeAuth: false),
        body: json.encode(userData),
      ).timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 201) {
        final data = json.decode(response.body);
        await _saveTokens(data['access_token'], data['refresh_token']);
        return ApiResponse<User>.success(
          data: User.fromJson(data['user']),
          message: data['message'] ?? 'Registration successful',
        );
      } else {
        final data = json.decode(response.body);
        return ApiResponse<User>.error(
          message: data['message'] ?? 'Registration failed',
          statusCode: response.statusCode,
        );
      }
    } on SocketException {
      return ApiResponse<User>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<User>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> sendOtp(String phoneNumber) async {
    try {
      final response = await _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.otpLoginEndpoint}'),
        headers: _getHeaders(includeAuth: false),
        body: json.encode({'phone_number': phoneNumber}),
      ).timeout(ApiConfig.requestTimeout);

      return _handleResponse(response, (data) => data);
    } on SocketException {
      return ApiResponse<Map<String, dynamic>>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<User>> verifyOtp(String phoneNumber, String otp) async {
    try {
      final response = await _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.verifyOtpEndpoint}'),
        headers: _getHeaders(includeAuth: false),
        body: json.encode({
          'phone_number': phoneNumber,
          'otp': otp,
        }),
      ).timeout(ApiConfig.requestTimeout);

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        await _saveTokens(data['access_token'], data['refresh_token']);
        return ApiResponse<User>.success(
          data: User.fromJson(data['user']),
          message: data['message'] ?? 'OTP verification successful',
        );
      } else {
        final data = json.decode(response.body);
        return ApiResponse<User>.error(
          message: data['message'] ?? 'OTP verification failed',
          statusCode: response.statusCode,
        );
      }
    } on SocketException {
      return ApiResponse<User>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<User>.error(message: 'Network error: ${e.toString()}');
    }
  }

  // Chat methods
  Future<ApiResponse<String>> sendChatMessage(String conversationId, String message) async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.aiChatEndpoint}'),
        headers: _getHeaders(),
        body: json.encode({
          'conversation_id': conversationId,
          'message': message,
        }),
      ).timeout(ApiConfig.requestTimeout));

      return _handleResponse(response, (data) => data['response'] as String);
    } on SocketException {
      return ApiResponse<String>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<String>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<String>> analyzeImage(String conversationId, File imageFile) async {
    try {
      final request = http.MultipartRequest(
        'POST',
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.imageAnalysisEndpoint}'),
      );
      
      request.headers.addAll(_getHeaders());
      request.fields['conversation_id'] = conversationId;
      request.files.add(await http.MultipartFile.fromPath('image', imageFile.path));

      final streamedResponse = await request.send().timeout(ApiConfig.requestTimeout);
      final response = await http.Response.fromStream(streamedResponse);

      return _handleResponse(response, (data) => data['analysis'] as String);
    } on SocketException {
      return ApiResponse<String>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<String>.error(message: 'Network error: ${e.toString()}');
    }
  }

  // Conversation methods
  Future<ApiResponse<List<Map<String, dynamic>>>> getConversations() async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.conversationsEndpoint}'),
        headers: _getHeaders(),
      ).timeout(ApiConfig.requestTimeout));

      return _handleResponse(response, (data) => List<Map<String, dynamic>>.from(data['conversations']));
    } on SocketException {
      return ApiResponse<List<Map<String, dynamic>>>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<List<Map<String, dynamic>>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> archiveConversation(String conversationId) async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.archiveConversationEndpoint}'),
        headers: _getHeaders(),
        body: json.encode({'conversation_id': conversationId}),
      ).timeout(ApiConfig.requestTimeout));

      return _handleResponse(response, (data) => data);
    } on SocketException {
      return ApiResponse<Map<String, dynamic>>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  Future<ApiResponse<Map<String, dynamic>>> getConversationSummary(String conversationId) async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.conversationSummaryEndpoint}/$conversationId'),
        headers: _getHeaders(),
      ).timeout(ApiConfig.requestTimeout));

      return _handleResponse(response, (data) => data);
    } on SocketException {
      return ApiResponse<Map<String, dynamic>>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  // Subscription methods
  Future<ApiResponse<Map<String, dynamic>>> getSubscriptionStatus() async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.get(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.subscriptionStatusEndpoint}'),
        headers: _getHeaders(),
      ).timeout(ApiConfig.requestTimeout));

      return _handleResponse(response, (data) => data);
    } on SocketException {
      return ApiResponse<Map<String, dynamic>>.error(message: 'No internet connection');
    } catch (e) {
      return ApiResponse<Map<String, dynamic>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  // Logout
  Future<ApiResponse<Map<String, dynamic>>> logout() async {
    try {
      final response = await _makeAuthenticatedRequest(() => _httpClient.post(
        Uri.parse('${ApiConfig.baseUrl}${ApiConfig.logoutEndpoint}'),
        headers: _getHeaders(),
      ).timeout(ApiConfig.requestTimeout));

      await clearTokens();
      return _handleResponse(response, (data) => data);
    } catch (e) {
      await clearTokens();
      return ApiResponse<Map<String, dynamic>>.error(message: 'Network error: ${e.toString()}');
    }
  }

  // Check if user is authenticated
  bool get isAuthenticated => _accessToken != null;

  // Dispose resources
  void dispose() {
    _httpClient.close();
  }
}