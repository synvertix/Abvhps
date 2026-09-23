class AdminSupportCore {
  final int id;
  final String name;
  final int sortOrder;
  final String shortInfo;
  final String status;
  final String? imageUrl;
  final String? createdAt;

  const AdminSupportCore({
    required this.id,
    required this.name,
    required this.sortOrder,
    required this.shortInfo,
    required this.status,
    this.imageUrl,
    this.createdAt,
  });

  factory AdminSupportCore.fromJson(Map<String, dynamic> json) {
    return AdminSupportCore(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      sortOrder: json['sort_order'] as int? ?? 1,
      shortInfo: json['short_info'] as String? ?? '',
      status: json['status'] as String? ?? 'show',
      imageUrl: json['image_url'] as String?,
      createdAt: json['created_at'] as String?,
    );
  }
}
