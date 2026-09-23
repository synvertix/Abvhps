class AdminMembership {
  final int id;
  final String membershipId;
  final String fullName;
  final String phone;
  final String? email;
  final String? paymentStatus;
  final bool isCompleted;
  final String? district;
  final String? mandal;
  final String? gramaPanchayat;
  final String? state;
  final String? photoUrl;
  final String? createdAt;

  const AdminMembership({
    required this.id,
    required this.membershipId,
    required this.fullName,
    required this.phone,
    this.email,
    this.paymentStatus,
    required this.isCompleted,
    this.district,
    this.mandal,
    this.gramaPanchayat,
    this.state,
    this.photoUrl,
    this.createdAt,
  });

  factory AdminMembership.fromJson(Map<String, dynamic> json) {
    return AdminMembership(
      id: json['id'] as int? ?? 0,
      membershipId: json['membership_id'] as String? ?? 'N/A',
      fullName: json['full_name'] as String? ?? 'Anonymous Member',
      phone: json['phone'] as String? ?? '',
      email: json['email'] as String?,
      paymentStatus: json['payment_status'] as String?,
      isCompleted: json['is_completed'] as bool? ?? false,
      district: json['district'] as String?,
      mandal: json['mandal'] as String?,
      gramaPanchayat: json['grama_panchayat'] as String?,
      state: json['state'] as String?,
      photoUrl: json['photo_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
