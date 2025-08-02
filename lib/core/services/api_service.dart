import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;

import '../config/app_config.dart';
import '../exceptions/api_exception.dart';

class ApiService {
  late final http.Client _client;
  String? _authToken;
  
  ApiService() {
    _client = http.Client();
  }
  
  void setAuthToken(String? token) {
    _authToken = token;
  }
  
  Map<String, String> get _headers {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    
    if (_authToken != null) {
      headers['Authorization'] = 'Bearer $_authToken';
    }
    
    return headers;
  }
  
  Future<Map<String, dynamic>> get(String endpoint) async {
    try {
      final url = Uri.parse('${AppConfig.baseUrl}$endpoint');
      final response = await _client.get(url, headers: _headers);
      
      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }
  
  Future<Map<String, dynamic>> post(
    String endpoint, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final url = Uri.parse('${AppConfig.baseUrl}$endpoint');
      final response = await _client.post(
        url,
        headers: _headers,
        body: body != null ? json.encode(body) : null,
      );
      
      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }
  
  Future<Map<String, dynamic>> put(
    String endpoint, {
    Map<String, dynamic>? body,
  }) async {
    try {
      final url = Uri.parse('${AppConfig.baseUrl}$endpoint');
      final response = await _client.put(
        url,
        headers: _headers,
        body: body != null ? json.encode(body) : null,
      );
      
      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }
  
  Future<Map<String, dynamic>> delete(String endpoint) async {
    try {
      final url = Uri.parse('${AppConfig.baseUrl}$endpoint');
      final response = await _client.delete(url, headers: _headers);
      
      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }
  
  Future<Map<String, dynamic>> postMultipart(
    String endpoint, {
    Map<String, String>? fields,
    Map<String, File>? files,
  }) async {
    try {
      final url = Uri.parse('${AppConfig.baseUrl}$endpoint');
      final request = http.MultipartRequest('POST', url);
      
      // Add headers
      request.headers.addAll(_headers);
      request.headers.remove('Content-Type'); // Let http handle multipart content type
      
      // Add fields
      if (fields != null) {
        request.fields.addAll(fields);
      }
      
      // Add files
      if (files != null) {
        for (final entry in files.entries) {
          final file = entry.value;
          final multipartFile = await http.MultipartFile.fromPath(
            entry.key,
            file.path,
          );
          request.files.add(multipartFile);
        }
      }
      
      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      
      return _handleResponse(response);
    } catch (e) {
      throw _handleError(e);
    }
  }
  
  Map<String, dynamic> _handleResponse(http.Response response) {
    final statusCode = response.statusCode;
    final body = response.body;
    
    Map<String, dynamic> data;
    try {
      data = json.decode(body) as Map<String, dynamic>;
    } catch (e) {
      throw ApiException(
        message: 'Invalid JSON response',
        statusCode: statusCode,
      );
    }
    
    if (statusCode >= 200 && statusCode < 300) {
      return data;
    } else {
      final message = data['message'] as String? ?? 'Unknown error occurred';
      final errors = data['errors'] as Map<String, dynamic>?;
      
      throw ApiException(
        message: message,
        statusCode: statusCode,
        errors: errors,
      );
    }
  }
  
  ApiException _handleError(dynamic error) {
    if (error is ApiException) {
      return error;
    } else if (error is SocketException) {
      return ApiException(
        message: 'No internet connection',
        statusCode: 0,
      );
    } else if (error is HttpException) {
      return ApiException(
        message: error.message,
        statusCode: 0,
      );
    } else {
      return ApiException(
        message: 'An unexpected error occurred',
        statusCode: 0,
      );
    }
  }
  
  void dispose() {
    _client.close();
  }
}