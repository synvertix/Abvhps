class AdminLocalGatewayGroup {
  final int id;
  final String registrationId;
  final String name;
  final String type;
  final String wingKey;
  final String wingName;
  final String location;
  final int membersCount;
  final String status;
  final String? leaderName;
  final String? leaderMobile;
  final String? createdAt;

  const AdminLocalGatewayGroup({
    required this.id,
    required this.registrationId,
    required this.name,
    required this.type,
    required this.wingKey,
    required this.wingName,
    required this.location,
    required this.membersCount,
    required this.status,
    this.leaderName,
    this.leaderMobile,
    this.createdAt,
  });

  factory AdminLocalGatewayGroup.fromJson(Map<String, dynamic> json) {
    return AdminLocalGatewayGroup(
      id: json['id'] as int? ?? 0,
      registrationId: json['registration_id'] as String? ?? 'N/A',
      name: json['name'] as String? ?? 'Local Group',
      type: json['type'] as String? ?? 'Wing',
      wingKey: json['wing_key'] as String? ?? 'unknown',
      wingName: json['wing_name'] as String? ?? 'Local Gateway',
      location: json['location'] as String? ?? 'Gram Panchayat',
      membersCount: json['members_count'] as int? ?? 0,
      status: json['status'] as String? ?? 'pending',
      leaderName: json['leader_name'] as String?,
      leaderMobile: json['leader_mobile'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
