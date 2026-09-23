class AdminBlog {
  final int id;
  final String title;
  final String content;
  final String status;
  final String? imageUrl;
  final String? thumbnailUrl;
  final String? createdAt;

  const AdminBlog({
    required this.id,
    required this.title,
    required this.content,
    required this.status,
    this.imageUrl,
    this.thumbnailUrl,
    this.createdAt,
  });

  factory AdminBlog.fromJson(Map<String, dynamic> json) {
    return AdminBlog(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      content: json['content'] as String? ?? '',
      status: json['status'] as String? ?? 'active',
      imageUrl: json['image_url'] as String?,
      thumbnailUrl: json['thumbnail_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
