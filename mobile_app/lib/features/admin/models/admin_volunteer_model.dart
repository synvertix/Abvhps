class AdminVolunteer {
  final int id;
  final String volunteerId;
  final String membershipId;
  final String name;
  final String phone;
  final String? email;
  final String cadre;
  final String qualification;
  final String status;
  final bool isActive;
  final String? district;
  final String? mandal;
  final String? gramaPanchayat;
  final String? photoUrl;
  final String? createdAt;

  const AdminVolunteer({
    required this.id,
    required this.volunteerId,
    required this.membershipId,
    required this.name,
    required this.phone,
    this.email,
    required this.cadre,
    required this.qualification,
    required this.status,
    required this.isActive,
    this.district,
    this.mandal,
    this.gramaPanchayat,
    this.photoUrl,
    this.createdAt,
  });

  factory AdminVolunteer.fromJson(Map<String, dynamic> json) {
    return AdminVolunteer(
      id: json['id'] as int? ?? 0,
      volunteerId: json['volunteer_id'] as String? ?? 'PENDING',
      membershipId: json['membership_id'] as String? ?? '',
      name: json['name'] as String? ?? 'Volunteer',
      phone: json['phone'] as String? ?? '',
      email: json['email'] as String?,
      cadre: json['cadre'] as String? ?? 'Volunteer',
      qualification: json['qualification'] as String? ?? 'N/A',
      status: json['status'] as String? ?? 'pending',
      isActive: json['is_active'] as bool? ?? false,
      district: json['district'] as String?,
      mandal: json['mandal'] as String?,
      gramaPanchayat: json['grama_panchayat'] as String?,
      photoUrl: json['photo_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
