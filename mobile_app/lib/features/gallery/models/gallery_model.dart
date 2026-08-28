import '../../../core/models/pagination_meta.dart';

class GalleryPaginatedResponse {
  final List<GalleryModel> items;
  final PaginationMeta meta;

  const GalleryPaginatedResponse({
    required this.items,
    required this.meta,
  });

  factory GalleryPaginatedResponse.fromJson(Map<String, dynamic> json) {
    final list = (json['data'] as List<dynamic>? ?? [])
        .map((e) => GalleryModel.fromJson(e as Map<String, dynamic>))
        .toList();
    final meta = PaginationMeta.fromJson(json['meta'] as Map<String, dynamic>?);

    return GalleryPaginatedResponse(
      items: list,
      meta: meta,
    );
  }
}

class GalleryModel {
  final int id;
  final String mediaType;
  final String? imageUrl;
  final String? videoUrl;

  const GalleryModel({
    required this.id,
    required this.mediaType,
    this.imageUrl,
    this.videoUrl,
  });

  factory GalleryModel.fromJson(Map<String, dynamic> json) {
    return GalleryModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      mediaType: json['media_type']?.toString() ?? 'image',
      imageUrl: json['image_url']?.toString(),
      videoUrl: json['video_url']?.toString(),
    );
  }

  bool get isVideo => mediaType == 'video';
}
