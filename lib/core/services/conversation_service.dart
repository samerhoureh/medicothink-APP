import 'api_service.dart';
import 'storage_service.dart';
import '../config/app_config.dart';
import '../exceptions/api_exception.dart';
import '../../features/chat/domain/entities/conversation_entity.dart';
import '../../features/chat/domain/entities/message_entity.dart';

class ConversationService {
  final ApiService _apiService;
  final StorageService _storageService;
  
  ConversationService(this._apiService, this._storageService);
  
  Future<List<ConversationEntity>> getConversations({
    int page = 1,
    int limit = 20,
    bool includeArchived = false,
  }) async {
    try {
      final queryParams = {
        'page': page.toString(),
        'limit': limit.toString(),
        'include_archived': includeArchived.toString(),
      };
      
      final queryString = queryParams.entries
          .map((e) => '${e.key}=${e.value}')
          .join('&');
      
      final response = await _apiService.get(
        '${AppConfig.conversationsEndpoint}?$queryString',
      );
      
      if (response['success'] == true) {
        final conversationsData = response['data']['conversations'] as List;
        final conversations = conversationsData
            .map((c) => ConversationEntity.fromJson(c as Map<String, dynamic>))
            .toList();
        
        // Cache conversations locally
        await _storageService.saveConversations(conversations);
        
        return conversations;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to fetch conversations',
          statusCode: 400,
        );
      }
    } catch (e) {
      // Return cached conversations if API fails
      return _storageService.getConversations();
    }
  }
  
  Future<ConversationEntity> getConversation(String conversationId) async {
    try {
      final response = await _apiService.get(
        '${AppConfig.conversationsEndpoint}/$conversationId',
      );
      
      if (response['success'] == true) {
        final conversationData = response['data']['conversation'] as Map<String, dynamic>;
        return ConversationEntity.fromJson(conversationData);
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to fetch conversation',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<ConversationEntity> createConversation({
    required String title,
    String? firstMessage,
  }) async {
    try {
      final body = {
        'title': title,
      };
      
      if (firstMessage != null) {
        body['first_message'] = firstMessage;
      }
      
      final response = await _apiService.post(
        AppConfig.conversationsEndpoint,
        body: body,
      );
      
      if (response['success'] == true) {
        final conversationData = response['data']['conversation'] as Map<String, dynamic>;
        return ConversationEntity.fromJson(conversationData);
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to create conversation',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<void> archiveConversation(String conversationId) async {
    try {
      final endpoint = AppConfig.archiveConversationEndpoint
          .replaceAll('{id}', conversationId);
      
      final response = await _apiService.post(endpoint);
      
      if (response['success'] != true) {
        throw ApiException(
          message: response['message'] ?? 'Failed to archive conversation',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<void> unarchiveConversation(String conversationId) async {
    try {
      final endpoint = AppConfig.unarchiveConversationEndpoint
          .replaceAll('{id}', conversationId);
      
      final response = await _apiService.post(endpoint);
      
      if (response['success'] != true) {
        throw ApiException(
          message: response['message'] ?? 'Failed to unarchive conversation',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<void> deleteConversation(String conversationId) async {
    try {
      final response = await _apiService.delete(
        '${AppConfig.conversationsEndpoint}/$conversationId',
      );
      
      if (response['success'] != true) {
        throw ApiException(
          message: response['message'] ?? 'Failed to delete conversation',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<Map<String, dynamic>> getConversationSummary(String conversationId) async {
    try {
      final endpoint = AppConfig.conversationSummaryEndpoint
          .replaceAll('{id}', conversationId);
      
      final response = await _apiService.get(endpoint);
      
      if (response['success'] == true) {
        return response['data']['summary'] as Map<String, dynamic>;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to get conversation summary',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  // Local conversation management
  List<ConversationEntity> getCachedConversations() {
    return _storageService.getConversations();
  }
  
  Future<void> saveCachedConversations(List<ConversationEntity> conversations) async {
    await _storageService.saveConversations(conversations);
  }
  
  Future<void> clearCachedConversations() async {
    await _storageService.removeConversations();
  }
}