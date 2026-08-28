import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/blog_model.dart';

final blogDetailProvider = FutureProvider.autoDispose.family<BlogModel, int>((ref, id) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getBlog(id);
});

class BlogDetailScreen extends ConsumerWidget {
  final int blogId;

  const BlogDetailScreen({
    super.key,
    required this.blogId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final blogAsync = ref.watch(blogDetailProvider(blogId));

    return PublicScaffold(
      title: 'Article Details',
      body: blogAsync.when(
        data: (blog) => _buildDetail(context, blog),
        loading: () => const AppLoadingState(message: 'Loading article content...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(blogDetailProvider(blogId)),
        ),
      ),
    );
  }

  Widget _buildDetail(BuildContext context, BlogModel b) {
    // Basic safe HTML to plain paragraphs parser (strips scripts/tags)
    final cleanParagraphs = (b.content ?? b.excerpt)
        .replaceAll(RegExp(r'<script.*?</script>', multiLine: true, caseSensitive: false), '')
        .replaceAll(RegExp(r'</?(p|div|h[1-6]|li|br\s*/?)[^>]*>', caseSensitive: false), '\n\n')
        .replaceAll(RegExp(r'<[^>]*>'), '')
        .replaceAll('&nbsp;', ' ')
        .replaceAll('&amp;', '&')
        .replaceAll('&quot;', '"')
        .replaceAll('&#039;', "'")
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .split('\n\n')
        .map((p) => p.trim())
        .where((p) => p.isNotEmpty)
        .toList();

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Back button
          TextButton.icon(
            onPressed: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/blogs');
              }
            },
            icon: const Icon(Icons.arrow_back, size: 18, color: AppTheme.primaryOrange),
            label: const Text(
              'Back to Blogs',
              style: TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),

          // Title
          Text(
            b.title,
            style: const TextStyle(
              color: AppTheme.darkNavy,
              fontSize: 18,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.3,
            ),
          ),
          const SizedBox(height: 8),

          if (b.publishedAt != null)
            Row(
              children: [
                const Icon(Icons.calendar_today_outlined, size: 13, color: Colors.grey),
                const SizedBox(width: 6),
                Text(
                  b.publishedAt!,
                  style: const TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.w600),
                ),
              ],
            ),
          const SizedBox(height: 14),

          // Cover Image
          if (b.imageUrl != null || b.thumbnailUrl != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: AspectRatio(
                aspectRatio: 16 / 9,
                child: AppNetworkImage(
                  imageUrl: b.imageUrl ?? b.thumbnailUrl,
                  fallbackAsset: 'assets/branding/logo_abvhps.png',
                  fit: BoxFit.cover,
                ),
              ),
            ),
          const SizedBox(height: 18),

          // Content Paragraphs
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: cleanParagraphs.map((p) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Text(
                    p,
                    style: const TextStyle(
                      color: Colors.black87,
                      fontSize: 14,
                      height: 1.6,
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
