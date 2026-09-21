class AdminBanner {
  final int id;
  final String pageKey;
  final String? title;
  final String? subtitle;
  final String? desktopImageUrl;
  final String? mobileImageUrl;
  final String status;
  final int sortOrder;
  final String? createdAt;

  const AdminBanner({
    required this.id,
    required this.pageKey,
    this.title,
    this.subtitle,
    this.desktopImageUrl,
    this.mobileImageUrl,
    required this.status,
    required this.sortOrder,
    this.createdAt,
  });

  factory AdminBanner.fromJson(Map<String, dynamic> json) {
    return AdminBanner(
      id: json['id'] as int,
      pageKey: json['page_key'] as String? ?? 'home',
      title: json['title'] as String?,
      subtitle: json['subtitle'] as String?,
      desktopImageUrl: json['desktop_image_url'] as String?,
      mobileImageUrl: json['mobile_image_url'] as String?,
      status: json['status'] as String? ?? 'show',
      sortOrder: json['sort_order'] as int? ?? 0,
      createdAt: json['created_at'] as String?,
    );
  }
}
