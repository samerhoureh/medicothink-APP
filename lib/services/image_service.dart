import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/foundation.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:device_info_plus/device_info_plus.dart';

class ImageService {
  static final ImagePicker _picker = ImagePicker();

  static Future<String?> pickImageFromGallery() async {
    try {
      // Request permission for gallery access based on platform
      Permission permission;
      if (Platform.isAndroid) {
        final androidInfo = await DeviceInfoPlugin().androidInfo;
        if (androidInfo.version.sdkInt <= 32) {
          permission = Permission.storage;
        } else {
          permission = Permission.photos;
        }
      } else {
        permission = Permission.photos;
      }
      
      final status = await permission.request();
      if (!status.isGranted) {
        debugPrint('Gallery permission denied');
        return null;
      }

      final XFile? image = await _picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 85,
      );
      
      return image?.path;
    } catch (e) {
      debugPrint('Error picking image from gallery: $e');
      return null;
    }
  }

  static Future<String?> pickImageFromCamera() async {
    try {
      // Request camera permission
      final status = await Permission.camera.request();
      if (!status.isGranted) {
        debugPrint('Camera permission denied');
        return null;
      }

      final XFile? image = await _picker.pickImage(
        source: ImageSource.camera,
        maxWidth: 1920,
        maxHeight: 1080,
        imageQuality: 85,
      );
      
      return image?.path;
    } catch (e) {
      debugPrint('Error taking photo: $e');
      return null;
    }
  }

  static Future<String> analyzeImage(String imagePath) async {
    // Simulate image analysis - in a real app, this would call an AI service
    await Future.delayed(const Duration(seconds: 2));
    
    // Mock analysis based on common medical scenarios
    final responses = [
      "I can see what appears to be a skin condition in the image. Based on the visual characteristics, this could be eczema or dermatitis. I recommend consulting with a dermatologist for proper diagnosis and treatment.",
      "The image shows what looks like a rash or skin irritation. It's important to keep the area clean and avoid scratching. Please consult a healthcare provider for proper evaluation.",
      "I can observe some symptoms in the image. While I can provide general information, it's crucial to have this examined by a medical professional for accurate diagnosis and appropriate treatment.",
      "The image appears to show a medical concern that would benefit from professional evaluation. I recommend scheduling an appointment with your healthcare provider to discuss this properly.",
    ];
    
    return responses[DateTime.now().millisecond % responses.length];
  }

  static Future<bool> isValidImageFile(String path) async {
    try {
      final file = File(path);
      if (!await file.exists()) return false;
      
      final bytes = await file.readAsBytes();
      if (bytes.isEmpty) return false;
      
      // Check for common image file signatures
      if (bytes.length < 4) return false;
      
      // JPEG
      if (bytes[0] == 0xFF && bytes[1] == 0xD8) return true;
      // PNG
      if (bytes[0] == 0x89 && bytes[1] == 0x50 && bytes[2] == 0x4E && bytes[3] == 0x47) return true;
      // GIF
      if (bytes[0] == 0x47 && bytes[1] == 0x49 && bytes[2] == 0x46) return true;
      // WebP
      if (bytes.length >= 12 && 
          bytes[0] == 0x52 && bytes[1] == 0x49 && bytes[2] == 0x46 && bytes[3] == 0x46 &&
          bytes[8] == 0x57 && bytes[9] == 0x45 && bytes[10] == 0x42 && bytes[11] == 0x50) return true;
      
      return false;
    } catch (e) {
      return false;
    }
  }

  static String getImageDisplayName(String path) {
    return path.split('/').last;
  }

  static Future<Uint8List?> getImageBytes(String path) async {
    try {
      final file = File(path);
      if (await file.exists()) {
        return await file.readAsBytes();
      }
    } catch (e) {
      debugPrint('Error reading image: $e');
    }
    return null;
  }
}