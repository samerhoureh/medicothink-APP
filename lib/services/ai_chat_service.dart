import 'dart:io';
import '../models/api_response.dart';
import '../models/conversation_summary.dart';
import 'api_service.dart';

class AiChatService {
  static final AiChatService _instance = AiChatService._internal();
  factory AiChatService() => _instance;
  AiChatService._internal();

  final ApiService _apiService = ApiService();

  Future<ApiResponse<String>> sendMessage(String conversationId, String message) async {
    try {
      return await _apiService.sendChatMessage(conversationId, message);
    } catch (e) {
      return ApiResponse<String>.error(message: 'Failed to send message: $e');
    }
  }

  Future<ApiResponse<String>> analyzeImage(String conversationId, File imageFile) async {
    try {
      return await _apiService.analyzeImage(conversationId, imageFile);
    } catch (e) {
      return ApiResponse<String>.error(message: 'Failed to analyze image: $e');
    }
  }

  Future<ApiResponse<ConversationSummary>> getConversationSummary(String conversationId) async {
    try {
      final response = await _apiService.getConversationSummary(conversationId);
      if (response.isSuccess && response.data != null) {
        final summary = ConversationSummary.fromJson(response.data!);
        return ApiResponse<ConversationSummary>.success(
          data: summary,
          message: response.message,
        );
      } else {
        return ApiResponse<ConversationSummary>.error(
          message: response.message,
          statusCode: response.statusCode,
        );
      }
    } catch (e) {
      return ApiResponse<ConversationSummary>.error(message: 'Failed to get summary: $e');
    }
  }

  // Generate flash cards from conversation summary
  List<FlashCard> generateFlashCards(ConversationSummary summary) {
    List<FlashCard> flashCards = [];

    // Summary card
    flashCards.add(FlashCard(
      id: '${summary.id}_summary',
      title: 'Conversation Summary',
      content: summary.summary,
      type: FlashCardType.summary,
    ));

    // Symptoms cards
    if (summary.symptoms.isNotEmpty) {
      for (int i = 0; i < summary.symptoms.length; i++) {
        flashCards.add(FlashCard(
          id: '${summary.id}_symptom_$i',
          title: 'Symptom ${i + 1}',
          content: summary.symptoms[i],
          type: FlashCardType.symptom,
        ));
      }
    }

    // Diagnosis card
    if (summary.diagnosis != null && summary.diagnosis!.isNotEmpty) {
      flashCards.add(FlashCard(
        id: '${summary.id}_diagnosis',
        title: 'Potential Diagnosis',
        content: summary.diagnosis!,
        type: FlashCardType.diagnosis,
      ));
    }

    // Recommendations cards
    if (summary.recommendations.isNotEmpty) {
      for (int i = 0; i < summary.recommendations.length; i++) {
        flashCards.add(FlashCard(
          id: '${summary.id}_recommendation_$i',
          title: 'Recommendation ${i + 1}',
          content: summary.recommendations[i],
          type: FlashCardType.recommendation,
        ));
      }
    }

    // Treatment card
    if (summary.treatment != null && summary.treatment!.isNotEmpty) {
      flashCards.add(FlashCard(
        id: '${summary.id}_treatment',
        title: 'Treatment Plan',
        content: summary.treatment!,
        type: FlashCardType.treatment,
      ));
    }

    // Key points cards
    if (summary.keyPoints.isNotEmpty) {
      for (int i = 0; i < summary.keyPoints.length; i++) {
        flashCards.add(FlashCard(
          id: '${summary.id}_keypoint_$i',
          title: 'Key Point ${i + 1}',
          content: summary.keyPoints[i],
          type: FlashCardType.keyPoint,
        ));
      }
    }

    return flashCards;
  }
}

class FlashCard {
  final String id;
  final String title;
  final String content;
  final FlashCardType type;

  FlashCard({
    required this.id,
    required this.title,
    required this.content,
    required this.type,
  });
}

enum FlashCardType {
  summary,
  symptom,
  diagnosis,
  recommendation,
  treatment,
  keyPoint,
}