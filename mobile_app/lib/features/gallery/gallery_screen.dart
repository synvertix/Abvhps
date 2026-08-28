import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/url_helper.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/gallery_model.dart';

class GalleryQueryState {
  final int page;
  final String activeType;
  final List<GalleryModel> items;
  final bool hasNextPage;
  final bool isLoadingMore;

  const GalleryQueryState({
    this.page = 1,
    this.activeType = 'all',
    this.items = const [],
    this.hasNextPage = false,
    this.isLoadingMore = false,
  });

  GalleryQueryState copyWith({
    int? page,
    String? activeType,
    List<GalleryModel>? items,
    bool? hasNextPage,
    bool? isLoadingMore,
  }) {
    return GalleryQueryState(
      page: page ?? this.page,
      activeType: activeType ?? this.activeType,
      items: items ?? this.items,
      hasNextPage: hasNextPage ?? this.hasNextPage,
      isLoadingMore: isLoadingMore ?? this.isLoadingMore,
    );
  }
}

final galleryNotifierProvider = StateNotifierProvider.autoDispose<GalleryNotifier, AsyncValue<GalleryQueryState>>((ref) {
  final repo = ref.watch(publicApiRepositoryProvider);
  return GalleryNotifier(repo);
});

class GalleryNotifier extends StateNotifier<AsyncValue<GalleryQueryState>> {
  final PublicApiRepository _repo;

  GalleryNotifier(this._repo) : super(const AsyncValue.loading()) {
    loadType('all');
  }

  Future<void> loadType(String type) async {
    state = const AsyncValue.loading();
    try {
      final res = await _repo.getGallery(page: 1, perPage: 24, type: type);
      state = AsyncValue.data(GalleryQueryState(
        page: 1,
        activeType: type,
        items: res.items,
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
      final res = await _repo.getGallery(
        page: nextPage,
        perPage: 24,
        type: current.activeType,
      );
      state = AsyncValue.data(current.copyWith(
        page: nextPage,
        items: [...current.items, ...res.items],
        hasNextPage: res.meta.hasNextPage,
        isLoadingMore: false,
      ));
    } catch (_) {
      state = AsyncValue.data(current.copyWith(isLoadingMore: false));
    }
  }
}

class GalleryScreen extends ConsumerWidget {
  const GalleryScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final galleryState = ref.watch(galleryNotifierProvider);

    return PublicScaffold(
      title: 'Media Gallery & Events',
      body: Column(
        children: [
          // Filter Tabs Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            color: Colors.white,
            child: Row(
              children: [
                _buildFilterChip(context, ref, 'all', 'All Media', galleryState.valueOrNull?.activeType == 'all'),
                const SizedBox(width: 8),
                _buildFilterChip(context, ref, 'image', 'Photos', galleryState.valueOrNull?.activeType == 'image'),
                const SizedBox(width: 8),
                _buildFilterChip(context, ref, 'video', 'Videos', galleryState.valueOrNull?.activeType == 'video'),
              ],
            ),
          ),
          const Divider(height: 1, color: Colors.black12),

          Expanded(
            child: galleryState.when(
              data: (data) => _buildGrid(context, data, ref),
              loading: () => const AppLoadingState(message: 'Loading media gallery...'),
              error: (err, _) => AppErrorState(
                message: err.toString(),
                onRetry: () => ref.read(galleryNotifierProvider.notifier).loadType('all'),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(BuildContext context, WidgetRef ref, String type, String label, bool isActive) {
    return ChoiceChip(
      label: Text(
        label,
        style: TextStyle(
          color: isActive ? Colors.white : AppTheme.darkNavy,
          fontWeight: FontWeight.bold,
          fontSize: 12,
        ),
      ),
      selected: isActive,
      selectedColor: AppTheme.primaryOrange,
      backgroundColor: Colors.grey.shade100,
      showCheckmark: false,
      onSelected: (selected) {
        if (selected) {
          ref.read(galleryNotifierProvider.notifier).loadType(type);
        }
      },
    );
  }

  Widget _buildGrid(BuildContext context, GalleryQueryState data, WidgetRef ref) {
    if (data.items.isEmpty) {
      return AppEmptyState(
        title: 'No Media Found',
        subtitle: 'Photos and videos for this category will appear here.',
        icon: Icons.photo_library_outlined,
        onAction: () => ref.read(galleryNotifierProvider.notifier).loadType('all'),
        actionLabel: 'Show All Media',
      );
    }

    return CustomScrollView(
      slivers: [
        SliverPadding(
          padding: const EdgeInsets.all(12),
          sliver: SliverGrid(
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 10,
              mainAxisSpacing: 10,
              childAspectRatio: 1.0,
            ),
            delegate: SliverChildBuilderDelegate(
              (context, index) {
                final item = data.items[index];
                return _buildMediaTile(context, item);
              },
              childCount: data.items.length,
            ),
          ),
        ),
        if (data.hasNextPage)
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 20),
              child: Center(
                child: data.isLoadingMore
                    ? const CircularProgressIndicator(color: AppTheme.primaryOrange, strokeWidth: 2)
                    : OutlinedButton(
                        onPressed: () => ref.read(galleryNotifierProvider.notifier).loadNextPage(),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.primaryOrange,
                          side: const BorderSide(color: AppTheme.primaryOrange),
                        ),
                        child: const Text('Load More Media'),
                      ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildMediaTile(BuildContext context, GalleryModel item) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: () {
            if (item.isVideo) {
              if (item.videoUrl != null) {
                UrlHelper.launchSafeUrl(item.videoUrl);
              }
            } else {
              _openImageViewer(context, item.imageUrl);
            }
          },
          child: Stack(
            fit: StackFit.expand,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: AppNetworkImage(
                  imageUrl: item.imageUrl,
                  fallbackAsset: 'assets/branding/logo_abvhps.png',
                  fit: BoxFit.cover,
                ),
              ),
              if (item.isVideo)
                Container(
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.35),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Center(
                    child: Container(
                      width: 44,
                      height: 44,
                      decoration: const BoxDecoration(
                        color: AppTheme.primaryOrange,
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.play_arrow, color: Colors.white, size: 28),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }

  void _openImageViewer(BuildContext context, String? imageUrl) {
    if (imageUrl == null || imageUrl.isEmpty) return;

    showDialog(
      context: context,
      barrierColor: Colors.black87,
      builder: (ctx) => Dialog(
        backgroundColor: Colors.transparent,
        insetPadding: const EdgeInsets.all(12),
        child: Stack(
          alignment: Alignment.center,
          children: [
            InteractiveViewer(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: AppNetworkImage(
                  imageUrl: imageUrl,
                  fit: BoxFit.contain,
                ),
              ),
            ),
            Positioned(
              top: 10,
              right: 10,
              child: IconButton(
                style: IconButton.styleFrom(
                  backgroundColor: Colors.black54,
                  foregroundColor: Colors.white,
                ),
                icon: const Icon(Icons.close),
                onPressed: () => Navigator.of(ctx).pop(),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
