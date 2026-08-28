class AboutData {
  final Map<String, dynamic>? banner;
  final OrganizationInfo organization;
  final MissionInfo mission;
  final List<CoreValueItem> coreValues;
  final List<PillarItem> pillars;

  const AboutData({
    this.banner,
    required this.organization,
    required this.mission,
    required this.coreValues,
    required this.pillars,
  });

  factory AboutData.fromJson(Map<String, dynamic> json) {
    return AboutData(
      banner: json['banner'] as Map<String, dynamic>?,
      organization: OrganizationInfo.fromJson(json['organization'] as Map<String, dynamic>? ?? {}),
      mission: MissionInfo.fromJson(json['mission'] as Map<String, dynamic>? ?? {}),
      coreValues: (json['core_values'] as List<dynamic>? ?? [])
          .map((e) => CoreValueItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      pillars: (json['pillars'] as List<dynamic>? ?? [])
          .map((e) => PillarItem.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class OrganizationInfo {
  final String name;
  final String shortName;
  final String tagline;
  final int foundedYear;
  final String registrationNo;
  final String founderGuru;
  final String? logoUrl;

  const OrganizationInfo({
    required this.name,
    required this.shortName,
    required this.tagline,
    required this.foundedYear,
    required this.registrationNo,
    required this.founderGuru,
    this.logoUrl,
  });

  factory OrganizationInfo.fromJson(Map<String, dynamic> json) {
    return OrganizationInfo(
      name: json['name']?.toString() ?? 'ABVHPS',
      shortName: json['short_name']?.toString() ?? 'ABVHPS',
      tagline: json['tagline']?.toString() ?? '',
      foundedYear: (json['founded_year'] as num?)?.toInt() ?? 2023,
      registrationNo: json['registration_no']?.toString() ?? '20/2023',
      founderGuru: json['founder_guru']?.toString() ?? 'Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu',
      logoUrl: json['logo_url']?.toString(),
    );
  }
}

class MissionInfo {
  final String title;
  final List<String> paragraphs;

  const MissionInfo({
    required this.title,
    required this.paragraphs,
  });

  factory MissionInfo.fromJson(Map<String, dynamic> json) {
    return MissionInfo(
      title: json['title']?.toString() ?? 'Our Mission',
      paragraphs: (json['paragraphs'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
    );
  }
}

class CoreValueItem {
  final String id;
  final String title;
  final String description;
  final String? icon;

  const CoreValueItem({
    required this.id,
    required this.title,
    required this.description,
    this.icon,
  });

  factory CoreValueItem.fromJson(Map<String, dynamic> json) {
    return CoreValueItem(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      icon: json['icon']?.toString(),
    );
  }
}

class PillarItem {
  final String title;
  final String description;

  const PillarItem({
    required this.title,
    required this.description,
  });

  factory PillarItem.fromJson(Map<String, dynamic> json) {
    return PillarItem(
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
    );
  }
}
