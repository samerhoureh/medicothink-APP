import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

import '../config/app_config.dart';
import '../../features/auth/domain/entities/user_entity.dart';
import '../../features/chat/domain/entities/conversation_entity.dart';

class StorageService {
  final SharedPreferences _prefs;
  
  StorageService(this._prefs);
  
  // Token Management
  Future<void> saveToken(String token) async {
    await _prefs.setString(AppConfig.tokenKey, token);
  }
  
  String? getToken() {
    return _prefs.getString(AppConfig.tokenKey);
  }
  
  Future<void> removeToken() async {
    await _prefs.remove(AppConfig.tokenKey);
  }
  
  bool hasToken() {
    return _prefs.containsKey(AppConfig.tokenKey);
  }
  
  // User Data Management
  Future<void> saveUser(UserEntity user) async {
    final userJson = json.encode(user.toJson());
    await _prefs.setString(AppConfig.userKey, userJson);
  }
  
  UserEntity? getUser() {
    final userJson = _prefs.getString(AppConfig.userKey);
    if (userJson != null) {
      final userMap = json.decode(userJson) as Map<String, dynamic>;
      return UserEntity.fromJson(userMap);
    }
    return null;
  }
  
  Future<void> removeUser() async {
    await _prefs.remove(AppConfig.userKey);
  }
  
  // Conversations Management
  Future<void> saveConversations(List<ConversationEntity> conversations) async {
    final conversationsJson = json.encode(
      conversations.map((c) => c.toJson()).toList(),
    );
    await _prefs.setString(AppConfig.conversationsKey, conversationsJson);
  }
  
  List<ConversationEntity> getConversations() {
    final conversationsJson = _prefs.getString(AppConfig.conversationsKey);
    if (conversationsJson != null) {
      final conversationsList = json.decode(conversationsJson) as List;
      return conversationsList
          .map((c) => ConversationEntity.fromJson(c as Map<String, dynamic>))
          .toList();
    }
    return [];
  }
  
  Future<void> removeConversations() async {
    await _prefs.remove(AppConfig.conversationsKey);
  }
  
  // Settings Management
  Future<void> saveSetting(String key, dynamic value) async {
    if (value is String) {
      await _prefs.setString(key, value);
    } else if (value is int) {
      await _prefs.setInt(key, value);
    } else if (value is double) {
      await _prefs.setDouble(key, value);
    } else if (value is bool) {
      await _prefs.setBool(key, value);
    } else if (value is List<String>) {
      await _prefs.setStringList(key, value);
    }
  }
  
  T? getSetting<T>(String key) {
    return _prefs.get(key) as T?;
  }
  
  Future<void> removeSetting(String key) async {
    await _prefs.remove(key);
  }
  
  // Theme Management
  Future<void> saveThemeMode(String themeMode) async {
    await _prefs.setString(AppConfig.themeKey, themeMode);
  }
  
  String getThemeMode() {
    return _prefs.getString(AppConfig.themeKey) ?? 'light';
  }
  
  // Clear All Data
  Future<void> clearAll() async {
    await _prefs.clear();
  }
  
  // First Launch Check
  bool isFirstLaunch() {
    return !_prefs.containsKey('first_launch_completed');
  }
  
  Future<void> setFirstLaunchCompleted() async {
    await _prefs.setBool('first_launch_completed', true);
  }
  
  // Biometric Settings
  Future<void> setBiometricEnabled(bool enabled) async {
    await _prefs.setBool('biometric_enabled', enabled);
  }
  
  bool isBiometricEnabled() {
    return _prefs.getBool('biometric_enabled') ?? false;
  }
  
  // Notification Settings
  Future<void> setNotificationsEnabled(bool enabled) async {
    await _prefs.setBool('notifications_enabled', enabled);
  }
  
  bool areNotificationsEnabled() {
    return _prefs.getBool('notifications_enabled') ?? true;
  }
  
  // Language Settings
  Future<void> setLanguage(String languageCode) async {
    await _prefs.setString('language', languageCode);
  }
  
  String getLanguage() {
    return _prefs.getString('language') ?? 'en';
  }
}