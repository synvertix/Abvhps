class CampaignModel {
  final int id;
  final String title;
  final String description;
  final String? imageUrl;
  final List<String> galleryImages;
  final String? videoUrl;
  final double targetAmount;
  final double raisedAmount;
  final String targetFormatted;
  final String raisedFormatted;
  final double percent;
  final String? endDate;
  final String? whatsappShareUrl;
  final String? publicUrl;

  const CampaignModel({
    required this.id,
    required this.title,
    required this.description,
    this.imageUrl,
    this.galleryImages = const [],
    this.videoUrl,
    required this.targetAmount,
    required this.raisedAmount,
    required this.targetFormatted,
    required this.raisedFormatted,
    required this.percent,
    this.endDate,
    this.whatsappShareUrl,
    this.publicUrl,
  });

  factory CampaignModel.fromJson(Map<String, dynamic> json) {
    return CampaignModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title']?.toString() ?? 'Campaign',
      description: json['description']?.toString() ?? '',
      imageUrl: json['image_url']?.toString(),
      galleryImages: (json['gallery_images'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
      videoUrl: json['video_url']?.toString(),
      targetAmount: (json['target_amount'] as num?)?.toDouble() ?? 0.0,
      raisedAmount: (json['raised_amount'] as num?)?.toDouble() ?? 0.0,
      targetFormatted: json['target_formatted']?.toString() ?? '₹0',
      raisedFormatted: json['raised_formatted']?.toString() ?? '₹0',
      percent: (json['percent'] as num?)?.toDouble() ?? 0.0,
      endDate: json['end_date']?.toString(),
      whatsappShareUrl: json['whatsapp_share_url']?.toString(),
      publicUrl: json['public_url']?.toString(),
    );
  }
}
