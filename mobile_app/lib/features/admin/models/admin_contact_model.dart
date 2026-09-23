class AdminContactMessage {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String subject;
  final String message;
  final String status;
  final String? adminNotes;
  final String? createdAt;

  const AdminContactMessage({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.subject,
    required this.message,
    required this.status,
    this.adminNotes,
    this.createdAt,
  });

  factory AdminContactMessage.fromJson(Map<String, dynamic> json) {
    return AdminContactMessage(
      id: json['id'] as int,
      name: json['name'] as String? ?? 'Visitor',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      subject: json['subject'] as String? ?? 'General Inquiry',
      message: json['message'] as String? ?? '',
      status: json['status'] as String? ?? 'unread',
      adminNotes: json['admin_notes'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
