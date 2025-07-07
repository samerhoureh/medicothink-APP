import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/conversation.dart';

class ConversationService {
  static const String _conversationsKey = 'conversations';
  static const String _currentConversationKey = 'current_conversation';

  static Future<List<Conversation>> getConversations() async {
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
  }

  static Future<void> archiveConversation(String conversationId) async {
    final conversations = await getConversations();
    final conversationIndex = conversations.indexWhere((c) => c.id == conversationId);
    
    if (conversationIndex != -1) {
      final updatedConversation = conversations[conversationIndex].copyWith(isArchived: true);
      conversations[conversationIndex] = updatedConversation;
      
      final prefs = await SharedPreferences.getInstance();
      final conversationsJson = conversations
          .map((c) => jsonEncode(c.toJson()))
          .toList();
      
      await prefs.setStringList(_conversationsKey, conversationsJson);
    }
  }

  static Future<void> unarchiveConversation(String conversationId) async {
    final conversations = await getConversations();
    final conversationIndex = conversations.indexWhere((c) => c.id == conversationId);
    
    if (conversationIndex != -1) {
      final updatedConversation = conversations[conversationIndex].copyWith(isArchived: false);
      conversations[conversationIndex] = updatedConversation;
      
      final prefs = await SharedPreferences.getInstance();
      final conversationsJson = conversations
          .map((c) => jsonEncode(c.toJson()))
          .toList();
      
      await prefs.setStringList(_conversationsKey, conversationsJson);
    }
  }

  static Future<void> deleteConversation(String conversationId) async {
    final conversations = await getConversations();
    conversations.removeWhere((c) => c.id == conversationId);
    
    final prefs = await SharedPreferences.getInstance();
    final conversationsJson = conversations
        .map((c) => jsonEncode(c.toJson()))
        .toList();
    
    await prefs.setStringList(_conversationsKey, conversationsJson);
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