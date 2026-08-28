class PublicVerificationResult {
  final bool success;
  final bool isValid;
  final String? entityType;
  final String? officialIdLabel;
  final String? officialId;
  final String? name;
  final String? photoUrl;
  final String? status;
  final bool isApproved;
  final String? cadre;
  final String? location;
  final String? bloodGroup;
  final String? verifiedSince;
  final String? extraDetail;
  final String? message;

  const PublicVerificationResult({
    required this.success,
    required this.isValid,
    this.entityType,
    this.officialIdLabel,
    this.officialId,
    this.name,
    this.photoUrl,
    this.status,
    this.isApproved = false,
    this.cadre,
    this.location,
    this.bloodGroup,
    this.verifiedSince,
    this.extraDetail,
    this.message,
  });

  factory PublicVerificationResult.fromJson(Map<String, dynamic> json) {
    return PublicVerificationResult(
      success: json['success'] == true,
      isValid: json['is_valid'] == true,
      entityType: json['entity_type']?.toString(),
      officialIdLabel: json['official_id_label']?.toString() ?? 'ID No.',
      officialId: json['official_id']?.toString(),
      name: json['name']?.toString(),
      photoUrl: json['photo_url']?.toString(),
      status: json['status']?.toString(),
      isApproved: json['is_approved'] == true,
      cadre: json['cadre']?.toString(),
      location: json['location']?.toString(),
      bloodGroup: json['blood_group']?.toString(),
      verifiedSince: json['verified_since']?.toString(),
      extraDetail: json['extra_detail']?.toString(),
      message: json['message']?.toString(),
    );
  }
}
