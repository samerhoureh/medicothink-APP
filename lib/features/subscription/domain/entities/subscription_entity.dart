class SubscriptionEntity {
  final String id;
  final String userId;
  final String planName;
  final String planType;
  final double price;
  final String currency;
  final String status;
  final DateTime startsAt;
  final DateTime endsAt;
  final bool autoRenewal;
  final Map<String, dynamic> features;
  final DateTime createdAt;
  final DateTime updatedAt;
  
  SubscriptionEntity({
    required this.id,
    required this.userId,
    required this.planName,
    required this.planType,
    required this.price,
    required this.currency,
    required this.status,
    required this.startsAt,
    required this.endsAt,
    required this.autoRenewal,
    required this.features,
    required this.createdAt,
    required this.updatedAt,
  });
  
  factory SubscriptionEntity.fromJson(Map<String, dynamic> json) {
    return SubscriptionEntity(
      id: json['id'].toString(),
      userId: json['user_id'].toString(),
      planName: json['plan_name'] as String,
      planType: json['plan_type'] as String,
      price: double.parse(json['price'].toString()),
      currency: json['currency'] as String,
      status: json['status'] as String,
      startsAt: DateTime.parse(json['starts_at'] as String),
      endsAt: DateTime.parse(json['ends_at'] as String),
      autoRenewal: json['auto_renewal'] as bool,
      features: json['features'] as Map<String, dynamic>,
      createdAt: DateTime.parse(json['created_at'] as String),
      updatedAt: DateTime.parse(json['updated_at'] as String),
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'plan_name': planName,
      'plan_type': planType,
      'price': price,
      'currency': currency,
      'status': status,
      'starts_at': startsAt.toIso8601String(),
      'ends_at': endsAt.toIso8601String(),
      'auto_renewal': autoRenewal,
      'features': features,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }
  
  bool get isActive => status == 'active' && endsAt.isAfter(DateTime.now());
  bool get isExpired => endsAt.isBefore(DateTime.now());
  bool get isPending => status == 'pending';
  bool get isCancelled => status == 'cancelled';
  
  int get daysUntilExpiry => endsAt.difference(DateTime.now()).inDays;
  
  SubscriptionEntity copyWith({
    String? id,
    String? userId,
    String? planName,
    String? planType,
    double? price,
    String? currency,
    String? status,
    DateTime? startsAt,
    DateTime? endsAt,
    bool? autoRenewal,
    Map<String, dynamic>? features,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return SubscriptionEntity(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      planName: planName ?? this.planName,
      planType: planType ?? this.planType,
      price: price ?? this.price,
      currency: currency ?? this.currency,
      status: status ?? this.status,
      startsAt: startsAt ?? this.startsAt,
      endsAt: endsAt ?? this.endsAt,
      autoRenewal: autoRenewal ?? this.autoRenewal,
      features: features ?? this.features,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }
}