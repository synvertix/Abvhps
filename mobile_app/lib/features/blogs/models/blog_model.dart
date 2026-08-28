import '../../../core/models/pagination_meta.dart';

class BlogPaginatedResponse {
  final List<BlogModel> items;
  final PaginationMeta meta;

  const BlogPaginatedResponse({
    required this.items,
    required this.meta,
  });

  factory BlogPaginatedResponse.fromJson(Map<String, dynamic> json) {
    final list = (json['data'] as List<dynamic>? ?? [])
        .map((e) => BlogModel.fromJson(e as Map<String, dynamic>))
        .toList();
    final meta = PaginationMeta.fromJson(json['meta'] as Map<String, dynamic>?);

    return BlogPaginatedResponse(
      items: list,
      meta: meta,
    );
  }
}

class BlogModel {
  final int id;
  final String title;
  final String excerpt;
  final String? content;
  final String? thumbnailUrl;
  final String? imageUrl;
  final String? publishedAt;

  const BlogModel({
    required this.id,
    required this.title,
    required this.excerpt,
    this.content,
    this.thumbnailUrl,
    this.imageUrl,
    this.publishedAt,
  });

  factory BlogModel.fromJson(Map<String, dynamic> json) {
    return BlogModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: json['title']?.toString() ?? 'News Article',
      excerpt: json['excerpt']?.toString() ?? '',
      content: json['content']?.toString(),
      thumbnailUrl: json['thumbnail_url']?.toString(),
      imageUrl: json['image_url']?.toString(),
      publishedAt: json['published_at']?.toString(),
    );
  }
}
