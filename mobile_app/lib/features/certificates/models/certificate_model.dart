class CertificateModel {
  final int id;
  final String title;
  final String certificateType;
  final String? documentNumber;
  final String? validFrom;
  final String? validTo;
  final String validitySummary;
  final String? description;
  final String? downloadUrl;

  const CertificateModel({
    required this.id,
    required this.title,
    required this.certificateType,
    this.documentNumber,
    this.validFrom,
    this.validTo,
    required this.validitySummary,
    this.description,
    this.downloadUrl,
  });

  factory CertificateModel.fromJson(Map<String, dynamic> json) {
    return CertificateModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title']?.toString() ?? 'Statutory Certificate',
      certificateType: json['certificate_type']?.toString() ?? '80G / 12A',
      documentNumber: json['document_number']?.toString(),
      validFrom: json['valid_from']?.toString(),
      validTo: json['valid_to']?.toString(),
      validitySummary: json['validity_summary']?.toString() ?? 'Permanent',
      description: json['description']?.toString(),
      downloadUrl: json['download_url']?.toString(),
    );
  }
}
