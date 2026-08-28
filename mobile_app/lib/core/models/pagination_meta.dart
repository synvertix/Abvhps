class PaginationMeta {
  final int currentPage;
  final int lastPage;
  final int perPage;
  final int total;

  const PaginationMeta({
    required this.currentPage,
    required this.lastPage,
    required this.perPage,
    required this.total,
  });

  factory PaginationMeta.fromJson(Map<String, dynamic>? json) {
    return PaginationMeta(
      currentPage: (json?['current_page'] as num?)?.toInt() ?? 1,
      lastPage: (json?['last_page'] as num?)?.toInt() ?? 1,
      perPage: (json?['per_page'] as num?)?.toInt() ?? 15,
      total: (json?['total'] as num?)?.toInt() ?? 0,
    );
  }

  bool get hasNextPage => currentPage < lastPage;
}
