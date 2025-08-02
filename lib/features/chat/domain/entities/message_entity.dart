enum MessageType {
  text,
  image,
  audio,
  video,
}

class MessageEntity {
  final String id;
  final String conversationId;
  final String content;
  final bool isFromUser;
  final MessageType messageType;
  final String? imagePath;
  final String? audioPath;
  final String? videoPath;
  final Map<String, dynamic>? metadata;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  MessageEntity({
    required this.id,
    required this.conversationId,
    required this.content,
    required this.isFromUser,
    required this.messageType,
    this.imagePath,
    this.audioPath,
    this.videoPath,
    this.metadata,
    required this.createdAt,
    required this.updatedAt,
  });
  
  factory MessageEntity.fromJson(Map<String, dynamic> json) {
    return MessageEntity(
      id: json['id'].toString(),
      conversationId: json['conversation_id'].toString(),
      content: json['content'] as String,
      isFromUser: json['is_from_user'] as bool,
      messageType: _parseMessageType(json['message_type'] as String?),
      imagePath: json['image_path'] as String?,
      audioPath: json['audio_path'] as String?,
      videoPath: json['video_path'] as String?,
      metadata: json['metadata'] as Map<String, dynamic>?,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'conversation_id': conversationId,
      'content': content,
      'is_from_user': isFromUser,
      'message_type': messageType.name,
      'image_path': imagePath,
      'audio_path': audioPath,
      'video_path': videoPath,
      'metadata': metadata,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  static MessageType _parseMessageType(String? type) {
    switch (type) {
      case 'image':
        return MessageType.image;
      case 'audio':
        return MessageType.audio;
      case 'video':
        return MessageType.video;
      default:
        return MessageType.text;
    }
  }
  
  MessageEntity copyWith({
    String? id,
    String? conversationId,
    String? content,
    bool? isFromUser,
    MessageType? messageType,
    String? imagePath,
    String? audioPath,
    String? videoPath,
    Map<String, dynamic>? metadata,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return MessageEntity(
      id: id ?? this.id,
      conversationId: conversationId ?? this.conversationId,
      content: content ?? this.content,
      isFromUser: isFromUser ?? this.isFromUser,
      messageType: messageType ?? this.messageType,
      imagePath: imagePath ?? this.imagePath,
      audioPath: audioPath ?? this.audioPath,
      videoPath: videoPath ?? this.videoPath,
      metadata: metadata ?? this.metadata,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}