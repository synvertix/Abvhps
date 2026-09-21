class AdminGalleryItem {
  final int id;
  final String mediaType;
  final String? imageUrl;
  final String? videoUrl;
  final String? createdAt;

  const AdminGalleryItem({
    required this.id,
    required this.mediaType,
    this.imageUrl,
    this.videoUrl,
    this.createdAt,
  });

  factory AdminGalleryItem.fromJson(Map<String, dynamic> json) {
    return AdminGalleryItem(
      id: json['id'] as int,
      mediaType: json['media_type'] as String? ?? 'image',
      imageUrl: json['image_url'] as String?,
      videoUrl: json['video_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
