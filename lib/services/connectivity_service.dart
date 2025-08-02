import 'dart:async';
import 'dart:io';
import 'package:flutter/foundation.dart';

class ConnectivityService {
  static final ConnectivityService _instance = ConnectivityService._internal();
  factory ConnectivityService() => _instance;
  ConnectivityService._internal();

  bool _isConnected = true;
  final StreamController<bool> _connectionController = StreamController<bool>.broadcast();

  Stream<bool> get connectionStream => _connectionController.stream;
  bool get isConnected => _isConnected;

  Future<void> initialize() async {
    // Check initial connectivity
    await _checkConnectivity();
    
    // Set up periodic connectivity checks
    Timer.periodic(const Duration(seconds: 30), (timer) {
      _checkConnectivity();
    });
  }

  Future<void> _checkConnectivity() async {
    try {
      final result = await InternetAddress.lookup('google.com');
      final bool wasConnected = _isConnected;
      _isConnected = result.isNotEmpty && result[0].rawAddress.isNotEmpty;
      
      if (wasConnected != _isConnected) {
        debugPrint('Connectivity changed: $_isConnected');
        _connectionController.add(_isConnected);
      }
    } catch (e) {
      final bool wasConnected = _isConnected;
      _isConnected = false;
      
      if (wasConnected != _isConnected) {
        debugPrint('Connectivity lost: $e');
        _connectionController.add(_isConnected);
      }
    }
  }

  Future<bool> hasInternetConnection() async {
    try {
      final result = await InternetAddress.lookup('google.com');
      return result.isNotEmpty && result[0].rawAddress.isNotEmpty;
    } catch (e) {
      debugPrint('Error checking internet connection: $e');
      return false;
    }
  }

  void dispose() {
    _connectionController.close();
  }
}