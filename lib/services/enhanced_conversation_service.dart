import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/conversation.dart';
import '../models/api_response.dart';
import 'api_service.dart';

class EnhancedConversationService {
  static const String _conversationsKey = 'conversations';
  static const String _currentConversationKey = 'current_conversation';
  
  static final ApiService _apiService = ApiService();

  // Sync conversations with server
  static Future<void> syncConversations() async {
    try {
      final response = await _apiService.getConversations();
      if (response.isSuccess && response.data != null) {
        final serverConversations = response.data!
            .map((json) => Conversation.fromJson(json))
            .toList();
        
        // Save to local storage
        final prefs = await SharedPreferences.getInstance();
        final conversationsJson = serverConversations
            .map((c) => jsonEncode(c.toJson()))
            .toList();
        
        await prefs.setStringList(_conversationsKey, conversationsJson);
      }
    } catch (e) {
      // If sync fails, continue with local data
      print('Sync failed: $e');
    }
  }

  static Future<List<Conversation>> getConversations() async {
    // Try to sync first
    await syncConversations();
    
    final prefs = await SharedPreferences.getInstance();
    final conversationsJson = prefs.getStringList(_conversationsKey) ?? [];
    
    return conversationsJson
        .map((json) => Conversation.fromJson(jsonDecode(json)))
        .toList()
        ..sort((a, b) => b.lastMessageAt.compareTo(a.lastMessageAt));
  }

  static Future<List<Conversation>> getActiveConversations() async {
    final conversations = await getConversations();
    return conversations.where((c) => !c.isArchived).toList();
  }

  static Future<List<Conversation>> getArchivedConversations() async {
    final conversations = await getConversations();
    return conversations.where((c) => c.isArchived).toList();
  }

  static Future<void> saveConversation(Conversation conversation) async {
    // Save locally first
    final prefs = await SharedPreferences.getInstance();
    final conversations = await getConversations();
    
    final existingIndex = conversations.indexWhere((c) => c.id == conversation.id);
    if (existingIndex != -1) {
      conversations[existingIndex] = conversation;
    } else {
      conversations.add(conversation);
    }

    final conversationsJson = conversations
        .map((c) => jsonEncode(c.toJson()))
        .toList();
    
    await prefs.setStringList(_conversationsKey, conversationsJson);

    // Try to sync with server (fire and forget)
    _syncConversationToServer(conversation);
  }

  static Future<void> _syncConversationToServer(Conversation conversation) async {
    try {
      // This would be implemented to sync individual conversations to server
      // For now, we'll just print a debug message
      print('Syncing conversation ${conversation.id} to server...');
    } catch (e) {
      print('Failed to sync conversation to server: $e');
    }
  }

  static Future<void> archiveConversation(String conversationId) async {
    // Archive locally
    final conversations = await getConversations();
    final conversationIndex = conversations.indexWhere((c) => c.id == conversationId);
    
    if (conversationIndex != -1) {
      final updatedConversation = conversations[conversationIndex].copyWith(isArchived: true);
      await saveConversation(updatedConversation);
      
      // Sync with server
      try {
        await _apiService.archiveConversation(conversationId);
      } catch (e) {
        print('Failed to archive conversation on server: $e');
      }
    }
  }

  static Future<void> unarchiveConversation(String conversationId) async {
    final conversations = await getConversations();
    final conversationIndex = conversations.indexWhere((c) => c.id == conversationId);
    
    if (conversationIndex != -1) {
      final updatedConversation = conversations[conversationIndex].copyWith(isArchived: false);
      await saveConversation(updatedConversation);
    }
  }

  static Future<void> deleteConversation(String conversationId) async {
    // Delete locally
    final conversations = await getConversations();
    conversations.removeWhere((c) => c.id == conversationId);
    
    final prefs = await SharedPreferences.getInstance();
    final conversationsJson = conversations
        .map((c) => jsonEncode(c.toJson()))
        .toList();
    
    await prefs.setStringList(_conversationsKey, conversationsJson);

    // Delete on server
    try {
      // This would be implemented in ApiService
      print('Deleting conversation $conversationId from server...');
    } catch (e) {
      print('Failed to delete conversation from server: $e');
    }
  }

  static Future<String?> getCurrentConversationId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_currentConversationKey);
  }

  static Future<void> setCurrentConversationId(String conversationId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_currentConversationKey, conversationId);
  }

  static String generateConversationTitle(String firstMessage) {
    if (firstMessage.length <= 30) return firstMessage;
    return '${firstMessage.substring(0, 30)}...';
  }
}