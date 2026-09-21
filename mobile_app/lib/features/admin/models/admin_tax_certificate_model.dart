class AdminTaxCertificate {
  final int id;
  final String title;
  final String certificateType;
  final String? documentNumber;
  final String? validFrom;
  final String? validTo;
  final String? description;
  final bool isActive;
  final String? pdfUrl;
  final String? createdAt;

  const AdminTaxCertificate({
    required this.id,
    required this.title,
    required this.certificateType,
    this.documentNumber,
    this.validFrom,
    this.validTo,
    this.description,
    required this.isActive,
    this.pdfUrl,
    this.createdAt,
  });

  factory AdminTaxCertificate.fromJson(Map<String, dynamic> json) {
    return AdminTaxCertificate(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      certificateType: json['certificate_type'] as String? ?? 'Statutory',
      documentNumber: json['document_number'] as String?,
      validFrom: json['valid_from'] as String?,
      validTo: json['valid_to'] as String?,
      description: json['description'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      pdfUrl: json['pdf_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
