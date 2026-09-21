class AdminVolunteerEvent {
  final int id;
  final String title;
  final String eventType;
  final String eventDate;
  final String venue;
  final String status;
  final String? organizerName;
  final String? organizerPhone;
  final String? district;
  final String? mandal;
  final int beneficiariesCount;
  final String? proofImageUrl;
  final String? createdAt;

  const AdminVolunteerEvent({
    required this.id,
    required this.title,
    required this.eventType,
    required this.eventDate,
    required this.venue,
    required this.status,
    this.organizerName,
    this.organizerPhone,
    this.district,
    this.mandal,
    required this.beneficiariesCount,
    this.proofImageUrl,
    this.createdAt,
  });

  factory AdminVolunteerEvent.fromJson(Map<String, dynamic> json) {
    return AdminVolunteerEvent(
      id: json['id'] as int? ?? 0,
      title: json['title'] as String? ?? 'Volunteer Event',
      eventType: json['event_type'] as String? ?? 'General',
      eventDate: json['event_date'] as String? ?? '',
      venue: json['venue'] as String? ?? '',
      status: json['status'] as String? ?? 'pending',
      organizerName: json['organizer_name'] as String?,
      organizerPhone: json['organizer_phone'] as String?,
      district: json['district'] as String?,
      mandal: json['mandal'] as String?,
      beneficiariesCount: json['beneficiaries_count'] as int? ?? 0,
      proofImageUrl: json['proof_image_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
