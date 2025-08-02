import 'dart:io';

import 'api_service.dart';
import '../config/app_config.dart';
import '../exceptions/api_exception.dart';

class AiService {
  final ApiService _apiService;
  
  AiService(this._apiService);
  
  Future<String> sendTextMessage({
    required String message,
    String? conversationId,
    List<Map<String, dynamic>>? conversationHistory,
  }) async {
    try {
      final body = {
        'message': message,
      };
      
      if (conversationId != null) {
        body['conversation_id'] = conversationId;
      }
      
      if (conversationHistory != null) {
        body['conversation_history'] = conversationHistory;
      }
      
      final response = await _apiService.post(
        AppConfig.textChatEndpoint,
        body: body,
      );
      
      if (response['success'] == true) {
        return response['data']['response'] as String;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to get AI response',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<String> analyzeImage({
    required File imageFile,
    String? question,
    String? analysisType,
  }) async {
    try {
      final fields = <String, String>{};
      
      if (question != null) {
        fields['question'] = question;
      }
      
      if (analysisType != null) {
        fields['analysis_type'] = analysisType;
      }
      
      final response = await _apiService.postMultipart(
        AppConfig.imageAnalysisEndpoint,
        fields: fields,
        files: {
          'image': imageFile,
        },
      );
      
      if (response['success'] == true) {
        return response['data']['analysis'] as String;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to analyze image',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<String?> textToSpeech({
    required String text,
    String voice = 'alloy',
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.textToSpeechEndpoint,
        body: {
          'text': text,
          'voice': voice,
        },
      );
      
      if (response['success'] == true) {
        return response['data']['audio_url'] as String?;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to convert text to speech',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<String?> speechToText({
    required File audioFile,
  }) async {
    try {
      final response = await _apiService.postMultipart(
        AppConfig.speechToTextEndpoint,
        files: {
          'audio': audioFile,
        },
      );
      
      if (response['success'] == true) {
        return response['data']['text'] as String?;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to convert speech to text',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<String?> generateImage({
    required String prompt,
    String size = '1024x1024',
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.generateImageEndpoint,
        body: {
          'prompt': prompt,
          'size': size,
        },
      );
      
      if (response['success'] == true) {
        return response['data']['image_url'] as String?;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to generate image',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
  
  Future<String?> generateVideo({
    required String prompt,
  }) async {
    try {
      final response = await _apiService.post(
        AppConfig.generateVideoEndpoint,
        body: {
          'prompt': prompt,
        },
      );
      
      if (response['success'] == true) {
        return response['data']['video_url'] as String?;
      } else {
        throw ApiException(
          message: response['message'] ?? 'Failed to generate video',
          statusCode: 400,
        );
      }
    } catch (e) {
      rethrow;
    }
  }
}