class ConversationSummary {
  final String id;
  final String conversationId;
  final String title;
  final String summary;
  final List<String> keyPoints;
  final List<String> recommendations;
  final String? diagnosis;
  final List<String> symptoms;
  final String? treatment;
  final DateTime createdAt;

  ConversationSummary({
    required this.id,
    required this.conversationId,
    required this.title,
    required this.summary,
    required this.keyPoints,
    required this.recommendations,
    this.diagnosis,
    required this.symptoms,
    this.treatment,
    required this.createdAt,
  });

  factory ConversationSummary.fromJson(Map<String, dynamic> json) {
    return ConversationSummary(
      id: json['id'].toString(),
      conversationId: json['conversation_id'].toString(),
      title: json['title'] ?? '',
      summary: json['summary'] ?? '',
      keyPoints: List<String>.from(json['key_points'] ?? []),
      recommendations: List<String>.from(json['recommendations'] ?? []),
      diagnosis: json['diagnosis'],
      symptoms: List<String>.from(json['symptoms'] ?? []),
      treatment: json['treatment'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'conversation_id': conversationId,
      'title': title,
      'summary': summary,
      'key_points': keyPoints,
      'recommendations': recommendations,
      'diagnosis': diagnosis,
      'symptoms': symptoms,
      'treatment': treatment,
      'created_at': createdAt.toIso8601String(),
    };
  }
}