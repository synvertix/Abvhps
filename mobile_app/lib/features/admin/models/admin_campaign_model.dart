class AdminCampaign {
  final int id;
  final String title;
  final String description;
  final double targetAmount;
  final double raisedAmount;
  final double progressPct;
  final String status;
  final String? endDate;
  final String? imageUrl;
  final String? createdAt;

  const AdminCampaign({
    required this.id,
    required this.title,
    required this.description,
    required this.targetAmount,
    required this.raisedAmount,
    required this.progressPct,
    required this.status,
    this.endDate,
    this.imageUrl,
    this.createdAt,
  });

  factory AdminCampaign.fromJson(Map<String, dynamic> json) {
    return AdminCampaign(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      targetAmount: (json['target_amount'] as num?)?.toDouble() ?? 0.0,
      raisedAmount: (json['raised_amount'] as num?)?.toDouble() ?? 0.0,
      progressPct: (json['progress_pct'] as num?)?.toDouble() ?? 0.0,
      status: json['status'] as String? ?? 'active',
      endDate: json['end_date'] as String?,
      imageUrl: json['image_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
