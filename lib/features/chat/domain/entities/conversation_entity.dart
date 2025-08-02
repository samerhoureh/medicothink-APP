import 'message_entity.dart';

class ConversationEntity {
  final String id;
  final String userId;
  final String title;
  final bool isArchived;
  final DateTime? lastMessageAt;
  final DateTime createdAt;
  final DateTime updatedAt;
  final List<MessageEntity>? messages;
  
  ConversationEntity({
    required this.id,
    required this.userId,
    required this.title,
    required this.isArchived,
    this.lastMessageAt,
    required this.createdAt,
    required this.updatedAt,
    this.messages,
  });
  
  factory ConversationEntity.fromJson(Map<String, dynamic> json) {
    return ConversationEntity(
      id: json['id'].toString(),
      userId: json['user_id'].toString(),
      title: json['title'] as String,
      isArchived: json['is_archived'] as bool? ?? false,
      lastMessageAt: json['last_message_at'] != null
          ? DateTime.parse(json['last_message_at'] as String)
          : null,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
      messages: json['messages'] != null
          ? (json['messages'] as List)
              .map((m) => MessageEntity.fromJson(m as Map<String, dynamic>))
              .toList()
          : null,
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'title': title,
      'is_archived': isArchived,
      'last_message_at': lastMessageAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'messages': messages?.map((m) => m.toJson()).toList(),
    };
  }
  
  ConversationEntity copyWith({
    String? id,
    String? userId,
    String? title,
    bool? isArchived,
    DateTime? lastMessageAt,
    DateTime? createdAt,
    DateTime? updatedAt,
    List<MessageEntity>? messages,
  }) {
    return ConversationEntity(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      title: title ?? this.title,
      isArchived: isArchived ?? this.isArchived,
      lastMessageAt: lastMessageAt ?? this.lastMessageAt,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      messages: messages ?? this.messages,
    );
  }
}