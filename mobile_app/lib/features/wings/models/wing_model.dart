class WingModel {
  final String slug;
  final String name;
  final String slogan;
  final String tagline;
  final String description;
  final String? eligibilityCriteria;
  final int? minAge;
  final int? maxAge;
  final bool requiresAgeCheck;
  final List<String> keyInitiatives;
  final String? badgeIcon;
  final String? imageUrl;

  const WingModel({
    required this.slug,
    required this.name,
    required this.slogan,
    required this.tagline,
    required this.description,
    this.eligibilityCriteria,
    this.minAge,
    this.maxAge,
    this.requiresAgeCheck = false,
    this.keyInitiatives = const [],
    this.badgeIcon,
    this.imageUrl,
  });

  factory WingModel.fromJson(Map<String, dynamic> json) {
    return WingModel(
      slug: json['slug']?.toString() ?? '',
      name: json['name']?.toString() ?? 'Wing',
      slogan: json['slogan']?.toString() ?? '',
      tagline: json['tagline']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      eligibilityCriteria: json['eligibility_criteria']?.toString(),
      minAge: (json['min_age'] as num?)?.toInt(),
      maxAge: (json['max_age'] as num?)?.toInt(),
      requiresAgeCheck: json['requires_age_check'] == true,
      keyInitiatives: (json['key_initiatives'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
      badgeIcon: json['badge_icon']?.toString(),
      imageUrl: json['image_url']?.toString(),
    );
  }
}

class RudrasenaEligibilityResult {
  final bool success;
  final bool eligible;
  final int? age;
  final String message;
  final Map<String, dynamic>? memberData;

  const RudrasenaEligibilityResult({
    required this.success,
    required this.eligible,
    this.age,
    required this.message,
    this.memberData,
  });

  factory RudrasenaEligibilityResult.fromJson(Map<String, dynamic> json) {
    return RudrasenaEligibilityResult(
      success: json['success'] == true,
      eligible: json['eligible'] == true,
      age: (json['age'] as num?)?.toInt(),
      message: json['message']?.toString() ?? '',
      memberData: json['data'] as Map<String, dynamic>?,
    );
  }
}
