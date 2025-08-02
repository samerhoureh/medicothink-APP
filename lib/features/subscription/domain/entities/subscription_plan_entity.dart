class SubscriptionPlanEntity {
  final String id;
  final String name;
  final String type;
  final double price;
  final String currency;
  final String duration;
  final List<String> features;
  final bool isPopular;
  final bool isActive;
  final Map<String, dynamic>? metadata;
  
  SubscriptionPlanEntity({
    required this.id,
    required this.name,
    required this.type,
    required this.price,
    required this.currency,
    required this.duration,
    required this.features,
    required this.isPopular,
    required this.isActive,
    this.metadata,
  });
  
  factory SubscriptionPlanEntity.fromJson(Map<String, dynamic> json) {
    return SubscriptionPlanEntity(
      id: json['id'].toString(),
      name: json['name'] as String,
      type: json['type'] as String,
      price: double.parse(json['price'].toString()),
      currency: json['currency'] as String,
      duration: json['duration'] as String,
      features: List<String>.from(json['features'] as List),
      isPopular: json['is_popular'] as bool? ?? false,
      isActive: json['is_active'] as bool? ?? true,
      metadata: json['metadata'] as Map<String, dynamic>?,
    );
  }
  
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'type': type,
      'price': price,
      'currency': currency,
      'duration': duration,
      'features': features,
      'is_popular': isPopular,
      'is_active': isActive,
      'metadata': metadata,
    };
  }
  
  String get formattedPrice {
    return '\$${price.toStringAsFixed(2)}';
  }
  
  String get formattedDuration {
    switch (duration.toLowerCase()) {
      case 'monthly':
        return 'per month';
      case 'yearly':
        return 'per year';
      case 'weekly':
        return 'per week';
      default:
        return duration;
    }
  }
  
  SubscriptionPlanEntity copyWith({
    String? id,
    String? name,
    String? type,
    double? price,
    String? currency,
    String? duration,
    List<String>? features,
    bool? isPopular,
    bool? isActive,
    Map<String, dynamic>? metadata,
  }) {
    return SubscriptionPlanEntity(
      id: id ?? this.id,
      name: name ?? this.name,
      type: type ?? this.type,
      price: price ?? this.price,
      currency: currency ?? this.currency,
      duration: duration ?? this.duration,
      features: features ?? this.features,
      isPopular: isPopular ?? this.isPopular,
      isActive: isActive ?? this.isActive,
      metadata: metadata ?? this.metadata,
    );
  }
}