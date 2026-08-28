class ProjectModel {
  final int id;
  final String name;
  final String shortInfo;
  final String? imageUrl;
  final int sortOrder;

  const ProjectModel({
    required this.id,
    required this.name,
    required this.shortInfo,
    this.imageUrl,
    this.sortOrder = 0,
  });

  factory ProjectModel.fromJson(Map<String, dynamic> json) {
    return ProjectModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? 'Project',
      shortInfo: json['short_info']?.toString() ?? '',
      imageUrl: json['image_url']?.toString(),
      sortOrder: (json['sort_order'] as num?)?.toInt() ?? 0,
    );
  }
}
