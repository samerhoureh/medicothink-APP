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
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'].toString(),
      username: json['username'] ?? '',
      email: json['email'] ?? '',
      phoneNumber: json['phone_number'],
      age: json['age'],
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
    );
  }
}

class SubscriptionStatus {
  final String id;
  final String planName;
  final DateTime expiresAt;
  final bool isActive;
  final int daysRemaining;

  SubscriptionStatus({
    required this.id,
    required this.planName,
    required this.expiresAt,
    required this.isActive,
    required this.daysRemaining,
  });

  factory SubscriptionStatus.fromJson(Map<String, dynamic> json) {
    final expiresAt = DateTime.parse(json['expires_at']);
    final now = DateTime.now();
    final daysRemaining = expiresAt.difference(now).inDays;

    return SubscriptionStatus(
      id: json['id'].toString(),
      planName: json['plan_name'] ?? '',
      expiresAt: expiresAt,
      isActive: json['is_active'] ?? false,
      daysRemaining: daysRemaining > 0 ? daysRemaining : 0,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'plan_name': planName,
      'expires_at': expiresAt.toIso8601String(),
      'is_active': isActive,
      'days_remaining': daysRemaining,
    };
  }

  bool get isExpired => DateTime.now().isAfter(expiresAt);
  bool get isExpiringSoon => daysRemaining <= 7 && daysRemaining > 0;
}