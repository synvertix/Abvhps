class AdminRudrasenaMember {
  final int id;
  final String rudrasenaId;
  final String membershipId;
  final String name;
  final String mobile;
  final String? email;
  final String volunteerType;
  final String assignedCadder;
  final String assignedLocality;
  final String status;
  final String? dob;
  final int? age;
  final bool isAgeEligible;
  final String? photoUrl;
  final String? createdAt;

  const AdminRudrasenaMember({
    required this.id,
    required this.rudrasenaId,
    required this.membershipId,
    required this.name,
    required this.mobile,
    this.email,
    required this.volunteerType,
    required this.assignedCadder,
    required this.assignedLocality,
    required this.status,
    this.dob,
    this.age,
    required this.isAgeEligible,
    this.photoUrl,
    this.createdAt,
  });

  factory AdminRudrasenaMember.fromJson(Map<String, dynamic> json) {
    return AdminRudrasenaMember(
      id: json['id'] as int? ?? 0,
      rudrasenaId: json['rudrasena_id'] as String? ?? 'PENDING',
      membershipId: json['membership_id'] as String? ?? '',
      name: json['name'] as String? ?? 'Rudrasena Cadet',
      mobile: json['mobile'] as String? ?? '',
      email: json['email'] as String?,
      volunteerType: json['volunteer_type'] as String? ?? 'General',
      assignedCadder: json['assigned_cadder'] as String? ?? 'Dal Member',
      assignedLocality: json['assigned_locality'] as String? ?? 'HQ',
      status: json['status'] as String? ?? 'pending',
      dob: json['dob'] as String?,
      age: json['age'] as int?,
      isAgeEligible: json['is_age_eligible'] as bool? ?? false,
      photoUrl: json['photo_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
