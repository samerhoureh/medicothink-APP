import 'dart:async';

import 'api_service.dart';
import 'storage_service.dart';
import '../config/app_config.dart';
import '../exceptions/api_exception.dart';
import '../../features/auth/domain/entities/user_entity.dart';

class AuthService {
  final ApiService _apiService;
  final StorageService _storageService;
  final StreamController<UserEntity?> _authStateController = StreamController<UserEntity?>.broadcast();
  
  AuthService(this._apiService, this._storageService) {
    _initializeAuth();
  }
  
  Stream<UserEntity?> get authStateChanges => _authStateController.stream;
  
  UserEntity? get currentUser => _storageService.getUser();
  
  bool get isLoggedIn => _storageService.hasToken() && currentUser != null;
  
  void _initializeAuth() {
    final token = _storageService.getToken();
    final user = _storageService.getUser();
    
    if (token != null && user != null) {
      _apiService.setAuthToken(token);
      _authStateController.add(user);
    } else {
      _authStateController.add(null);
    }
  }
  
  Future<UserEntity> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.loginEndpoint,
        body: {
          'email': email,
          'password': password,
        },
      );
      
      if (response['success'] == true) {
        final userData = response['data'];
        final token = userData['token'] as String;
        final userJson = userData['user'] as Map<String, dynamic>;
        
        final user = UserEntity.fromJson(userJson);
        
        // Save token and user data
        await _storageService.saveToken(token);
        await _storageService.saveUser(user);
        
        // Set auth token for future requests
        _apiService.setAuthToken(token);
        
        // Notify listeners
        _authStateController.add(user);
        
        return user;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Login failed',
          statusCode: 401,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<UserEntity> register({
    required String name,
    required String email,
    required String password,
    String? phoneNumber,
    DateTime? dateOfBirth,
    String? gender,
  }) async {
    try {
      final body = {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': password,
      };
      
      if (phoneNumber != null) body['phone_number'] = phoneNumber;
      if (dateOfBirth != null) body['date_of_birth'] = dateOfBirth.toIso8601String();
      if (gender != null) body['gender'] = gender;
      
      final response = await _apiService.post(
        AppConfig.registerEndpoint,
        body: body,
      );
      
      if (response['success'] == true) {
        final userData = response['data'];
        final token = userData['token'] as String;
        final userJson = userData['user'] as Map<String, dynamic>;
        
        final user = UserEntity.fromJson(userJson);
        
        // Save token and user data
        await _storageService.saveToken(token);
        await _storageService.saveUser(user);
        
        // Set auth token for future requests
        _apiService.setAuthToken(token);
        
        // Notify listeners
        _authStateController.add(user);
        
        return user;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Registration failed',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<bool> sendOtp(String phoneNumber) async {
    try {
      final response = await _apiService.post(
        AppConfig.otpLoginEndpoint,
        body: {
          'phone_number': phoneNumber,
        },
      );
      
      return response['success'] == true;
    } catch (e) {
      rethrow;
    }
  }
  
  Future<UserEntity> verifyOtp({
    required String phoneNumber,
    required String code,
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.verifyOtpEndpoint,
        body: {
          'phone_number': phoneNumber,
          'code': code,
        },
      );
      
      if (response['success'] == true) {
        final userData = response['data'];
        final token = userData['token'] as String;
        final userJson = userData['user'] as Map<String, dynamic>;
        
        final user = UserEntity.fromJson(userJson);
        
        // Save token and user data
        await _storageService.saveToken(token);
        await _storageService.saveUser(user);
        
        // Set auth token for future requests
        _apiService.setAuthToken(token);
        
        // Notify listeners
        _authStateController.add(user);
        
        return user;
      } else {
        throw ApiException(
          message: response['message'] ?? 'OTP verification failed',
          statusCode: 401,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<void> logout() async {
    try {
      // Try to logout from server
      await _apiService.post(AppConfig.logoutEndpoint);
    } catch (e) {
      // Continue with local logout even if server logout fails
    } finally {
      // Clear local data
      await _storageService.removeToken();
      await _storageService.removeUser();
      await _storageService.removeConversations();
      
      // Clear auth token
      _apiService.setAuthToken(null);
      
      // Notify listeners
      _authStateController.add(null);
    }
  }
  
  Future<String?> refreshToken() async {
    try {
      final response = await _apiService.post(AppConfig.refreshTokenEndpoint);
      
      if (response['success'] == true) {
        final token = response['data']['token'] as String;
        
        // Save new token
        await _storageService.saveToken(token);
        
        // Set auth token for future requests
        _apiService.setAuthToken(token);
        
        return token;
      }
      
      return null;
    } catch (e) {
      // If refresh fails, logout user
      await logout();
      return null;
    }
  }
  
  Future<UserEntity?> getCurrentUser() async {
    try {
      final response = await _apiService.get(AppConfig.profileEndpoint);
      
      if (response['success'] == true) {
        final userJson = response['data']['user'] as Map<String, dynamic>;
        final user = UserEntity.fromJson(userJson);
        
        // Update stored user data
        await _storageService.saveUser(user);
        
        // Notify listeners
        _authStateController.add(user);
        
        return user;
      }
      
      return null;
    } catch (e) {
      return null;
    }
  }
  
  Future<UserEntity> updateProfile({
    String? name,
    String? email,
    String? phoneNumber,
    DateTime? dateOfBirth,
    String? gender,
    List<String>? medicalHistory,
  }) async {
    try {
      final body = <String, dynamic>{};
      
      if (name != null) body['name'] = name;
      if (email != null) body['email'] = email;
      if (phoneNumber != null) body['phone_number'] = phoneNumber;
      if (dateOfBirth != null) body['date_of_birth'] = dateOfBirth.toIso8601String();
      if (gender != null) body['gender'] = gender;
      if (medicalHistory != null) body['medical_history'] = medicalHistory;
      
      final response = await _apiService.post(
        AppConfig.updateProfileEndpoint,
        body: body,
      );
      
      if (response['success'] == true) {
        final userJson = response['data']['user'] as Map<String, dynamic>;
        final user = UserEntity.fromJson(userJson);
        
        // Update stored user data
        await _storageService.saveUser(user);
        
        // Notify listeners
        _authStateController.add(user);
        
        return user;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Profile update failed',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  void dispose() {
    _authStateController.close();
  }
}