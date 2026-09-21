class AdminTeamMember {
  final int id;
  final String? membershipId;
  final String name;
  final String cadreLevel;
  final String designation;
  final String locality;
  final String? imageUrl;
  final String? createdAt;

  const AdminTeamMember({
    required this.id,
    this.membershipId,
    required this.name,
    required this.cadreLevel,
    required this.designation,
    required this.locality,
    this.imageUrl,
    this.createdAt,
  });

  factory AdminTeamMember.fromJson(Map<String, dynamic> json) {
    return AdminTeamMember(
      id: json['id'] as int,
      membershipId: json['membership_id'] as String?,
      name: json['name'] as String? ?? '',
      cadreLevel: json['cadre_level'] as String? ?? 'grama_panchayat',
      designation: json['designation'] as String? ?? '',
      locality: json['locality'] as String? ?? '',
      imageUrl: json['image_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
