import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/blog_model.dart';

class BlogsQueryState {
  final int page;
  final List<BlogModel> blogs;
  final bool hasNextPage;
  final bool isLoadingMore;

  const BlogsQueryState({
    this.page = 1,
    this.blogs = const [],
    this.hasNextPage = false,
    this.isLoadingMore = false,
  });

  BlogsQueryState copyWith({
    int? page,
    List<BlogModel>? blogs,
    bool? hasNextPage,
    bool? isLoadingMore,
  }) {
    return BlogsQueryState(
      page: page ?? this.page,
      blogs: blogs ?? this.blogs,
      hasNextPage: hasNextPage ?? this.hasNextPage,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
    );
  }
}

final blogsNotifierProvider = StateNotifierProvider.autoDispose<BlogsNotifier, AsyncValue<BlogsQueryState>>((ref) {
  final repo = ref.watch(publicApiRepositoryProvider);
  return BlogsNotifier(repo);
});

class BlogsNotifier extends StateNotifier<AsyncValue<BlogsQueryState>> {
  final PublicApiRepository _repo;

  BlogsNotifier(this._repo) : super(const AsyncValue.loading()) {
    loadInitial();
  }

  Future<void> loadInitial() async {
    state = const AsyncValue.loading();
    try {
      final res = await _repo.getBlogs(page: 1, perPage: 12);
      state = AsyncValue.data(BlogsQueryState(
        page: 1,
        blogs: res.items,
        hasNextPage: res.meta.hasNextPage,
      ));
    } catch (e, st) {
      state = AsyncValue.error(e, st);
    }
  }

  Future<void> loadNextPage() async {
    final current = state.valueOrNull;
    if (current == null || !current.hasNextPage || current.isLoadingMore) return;

    state = AsyncValue.data(current.copyWith(isLoadingMore: true));
    try {
      final nextPage = current.page + 1;
      final res = await _repo.getBlogs(page: nextPage, perPage: 12);
      state = AsyncValue.data(current.copyWith(
        page: nextPage,
        blogs: [...current.blogs, ...res.items],
        hasNextPage: res.meta.hasNextPage,
        isLoadingMore: false,
      ));
    } catch (_) {
      state = AsyncValue.data(current.copyWith(isLoadingMore: false));
    }
  }
}

class BlogsScreen extends ConsumerWidget {
  const BlogsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final blogsState = ref.watch(blogsNotifierProvider);

    return PublicScaffold(
      title: 'Blogs & Samiti News',
      body: blogsState.when(
        data: (data) => _buildList(context, data, ref),
        loading: () => const AppLoadingState(message: 'Loading articles and updates...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.read(blogsNotifierProvider.notifier).loadInitial(),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, BlogsQueryState data, WidgetRef ref) {
    if (data.blogs.isEmpty) {
      return AppEmptyState(
        title: 'No Articles Published',
        subtitle: 'New organizational updates and news will be published here.',
        icon: Icons.newspaper_outlined,
        onAction: () => ref.read(blogsNotifierProvider.notifier).loadInitial(),
        actionLabel: 'Refresh',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: data.blogs.length + (data.hasNextPage ? 1 : 0),
      itemBuilder: (context, index) {
        if (index == data.blogs.length) {
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 16),
            child: Center(
              child: data.isLoadingMore
                  ? const CircularProgressIndicator(color: AppTheme.primaryOrange, strokeWidth: 2)
                  : OutlinedButton(
                      onPressed: () => ref.read(blogsNotifierProvider.notifier).loadNextPage(),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: AppTheme.primaryOrange,
                        side: const BorderSide(color: AppTheme.primaryOrange),
                      ),
                      child: const Text('Load More Articles'),
                    ),
            ),
          );
        }

        final b = data.blogs[index];
        return Container(
          margin: const EdgeInsets.only(bottom: 16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.03),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () => context.push('/blogs/${b.id}'),
              borderRadius: BorderRadius.circular(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                    child: AspectRatio(
                      aspectRatio: 16 / 9,
                      child: AppNetworkImage(
                        imageUrl: b.thumbnailUrl ?? b.imageUrl,
                        fallbackAsset: 'assets/branding/logo_abvhps.png',
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (b.publishedAt != null)
                          Row(
                            children: [
                              const Icon(Icons.calendar_today_outlined, size: 12, color: Colors.grey),
                              const SizedBox(width: 4),
                              Text(
                                b.publishedAt!,
                                style: const TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                        const SizedBox(height: 6),
                        Text(
                          b.title,
                          style: const TextStyle(
                            color: AppTheme.darkNavy,
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          b.excerpt,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 12.5,
                            height: 1.4,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 12),
                        const Row(
                          children: [
                            Text(
                              'Read Full Article',
                              style: TextStyle(
                                color: AppTheme.primaryOrange,
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            SizedBox(width: 4),
                            Icon(Icons.arrow_forward_ios, size: 11, color: AppTheme.primaryOrange),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
