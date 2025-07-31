class User {
  final String id;
  final String username;
  final String email;
  final String? phoneNumber;
  final int? age;
  final String? city;
  final String? nationality;
  final String? specialization;
  final String? educationLevel;
  final String? profileImage;
  final DateTime createdAt;
  final DateTime updatedAt;
  final SubscriptionStatus? subscription;
  final bool isActive;
  final bool isVerified;

  User({
    required this.id,
    required this.username,
    required this.email,
    this.phoneNumber,
    this.age,
    this.city,
    this.nationality,
    this.specialization,
    this.educationLevel,
    this.profileImage,
    required this.createdAt,
    required this.updatedAt,
    this.subscription,
    this.isActive = true,
    this.isVerified = false,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'].toString(),
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      phoneNumber: json['phone_number'],
      age: json['age'] != null ? int.tryParse(json['age'].toString()) : null,
      city: json['city'],
      nationality: json['nationality'],
      specialization: json['specialization'],
      educationLevel: json['education_level'],
      profileImage: json['profile_image'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      subscription: json['subscription'] != null 
          ? SubscriptionStatus.fromJson(json['subscription'])
          : null,
      isActive: json['is_active'] ?? true,
      isVerified: json['is_verified'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'username': username,
      'email': email,
      'phone_number': phoneNumber,
      'age': age,
      'city': city,
      'nationality': nationality,
      'specialization': specialization,
      'education_level': educationLevel,
      'profile_image': profileImage,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
      'subscription': subscription?.toJson(),
      'is_active': isActive,
      'is_verified': isVerified,
    };
  }

  User copyWith({
    String? id,
    String? username,
    String? email,
    String? phoneNumber,
    int? age,
    String? city,
    String? nationality,
    String? specialization,
    String? educationLevel,
    String? profileImage,
    DateTime? createdAt,
    DateTime? updatedAt,
    SubscriptionStatus? subscription,
    bool? isActive,
    bool? isVerified,
  }) {
    return User(
      id: id ?? this.id,
      username: username ?? this.username,
      email: email ?? this.email,
      phoneNumber: phoneNumber ?? this.phoneNumber,
      age: age ?? this.age,
      city: city ?? this.city,
      nationality: nationality ?? this.nationality,
      specialization: specialization ?? this.specialization,
      educationLevel: educationLevel ?? this.educationLevel,
      profileImage: profileImage ?? this.profileImage,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      subscription: subscription ?? this.subscription,
      isActive: isActive ?? this.isActive,
      isVerified: isVerified ?? this.isVerified,
    );
  }

  // Helper methods
  bool get hasActiveSubscription => subscription?.isActive == true && subscription?.isExpired == false;
  bool get isSubscriptionExpiringSoon => subscription?.isExpiringSoon == true;
  String get displayName => username.isNotEmpty ? username : email.split('@').first;
  String get initials => username.isNotEmpty 
      ? username.split(' ').map((e) => e.isNotEmpty ? e[0] : '').take(2).join().toUpperCase()
      : email.isNotEmpty ? email[0].toUpperCase() : '?';
}

class SubscriptionStatus {
  final String id;
  final String planName;
  final String planType;
  final DateTime expiresAt;
  final DateTime? startedAt;
  final bool isActive;
  final int daysRemaining;
  final double? price;
  final String? currency;
  final bool autoRenew;

  SubscriptionStatus({
    required this.id,
    required this.planName,
    required this.planType,
    required this.expiresAt,
    this.startedAt,
    required this.isActive,
    required this.daysRemaining,
    this.price,
    this.currency,
    this.autoRenew = false,
  });

  factory SubscriptionStatus.fromJson(Map<String, dynamic> json) {
    final expiresAt = DateTime.parse(json['expires_at']);
    final now = DateTime.now();
    final daysRemaining = expiresAt.difference(now).inDays;

    return SubscriptionStatus(
      id: json['id'].toString(),
      planName: json['plan_name'] ?? '',
      planType: json['plan_type'] ?? 'basic',
      expiresAt: expiresAt,
      startedAt: json['started_at'] != null ? DateTime.parse(json['started_at']) : null,
      isActive: json['is_active'] ?? false,
      daysRemaining: daysRemaining > 0 ? daysRemaining : 0,
      price: json['price'] != null ? double.tryParse(json['price'].toString()) : null,
      currency: json['currency'] ?? 'USD',
      autoRenew: json['auto_renew'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'plan_name': planName,
      'plan_type': planType,
      'expires_at': expiresAt.toIso8601String(),
      'started_at': startedAt?.toIso8601String(),
      'is_active': isActive,
      'days_remaining': daysRemaining,
      'price': price,
      'currency': currency,
      'auto_renew': autoRenew,
    };
  }

  bool get isExpired => DateTime.now().isAfter(expiresAt);
  bool get isExpiringSoon => daysRemaining <= 7 && daysRemaining > 0;
  bool get isPremium => planType.toLowerCase() != 'basic' && planType.toLowerCase() != 'free';
  
  String get statusText {
    if (isExpired) return 'Expired';
    if (isExpiringSoon) return 'Expiring Soon';
    if (isActive) return 'Active';
    return 'Inactive';
  }
}