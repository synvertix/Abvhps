import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/wing_model.dart';

final wingsProvider = FutureProvider.autoDispose<List<WingModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getWings();
});

class WingsListScreen extends ConsumerWidget {
  const WingsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final wingsAsync = ref.watch(wingsProvider);

    return PublicScaffold(
      title: 'Our Specialized Wings & Subsystems',
      body: wingsAsync.when(
        data: (wings) => _buildList(context, wings, ref),
        loading: () => const AppLoadingState(message: 'Loading specialized wings...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(wingsProvider),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<WingModel> wings, WidgetRef ref) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: wings.length,
      itemBuilder: (context, index) {
        final w = wings[index];
        IconData iconData = Icons.shield;
        if (w.badgeIcon == 'music') iconData = Icons.music_note;
        if (w.badgeIcon == 'home') iconData = Icons.home_work;
        if (w.badgeIcon == 'leaf') iconData = Icons.eco;

        return Container(
          margin: const EdgeInsets.only(bottom: 16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.04),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () => context.push('/wings/${w.slug}'),
              borderRadius: BorderRadius.circular(16),
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: AppTheme.lightOrange,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: AppTheme.primaryOrange.withValues(alpha: 0.3)),
                          ),
                          child: Icon(iconData, color: AppTheme.primaryOrange, size: 24),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                w.name,
                                style: const TextStyle(
                                  color: AppTheme.darkNavy,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                              Text(
                                w.slogan,
                                style: const TextStyle(
                                  color: AppTheme.primaryOrange,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      w.tagline,
                      style: const TextStyle(
                        color: Colors.black87,
                        fontSize: 12.5,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 14),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        if (w.requiresAgeCheck)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFF0F172A),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'AGE 24-44 CHECK REQUIRED',
                              style: TextStyle(color: AppTheme.primaryOrange, fontSize: 9.5, fontWeight: FontWeight.w800),
                            ),
                          )
                        else
                          const SizedBox.shrink(),
                        const Row(
                          children: [
                            Text(
                              'Explore Wing',
                              style: TextStyle(color: AppTheme.primaryOrange, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                            SizedBox(width: 4),
                            Icon(Icons.arrow_forward_ios, size: 11, color: AppTheme.primaryOrange),
                          ],
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
