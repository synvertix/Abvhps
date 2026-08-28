import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/project_model.dart';

final projectsProvider = FutureProvider.autoDispose<List<ProjectModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getProjects();
});

class ProjectsListScreen extends ConsumerWidget {
  const ProjectsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final projectsAsync = ref.watch(projectsProvider);

    return PublicScaffold(
      title: 'Our Core Projects & Seva',
      body: projectsAsync.when(
        data: (projects) => _buildList(context, projects, ref),
        loading: () => const AppLoadingState(message: 'Loading core service initiatives...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(projectsProvider),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<ProjectModel> projects, WidgetRef ref) {
    if (projects.isEmpty) {
      return AppEmptyState(
        title: 'No Projects Found',
        subtitle: 'Core initiatives will appear here shortly.',
        icon: Icons.volunteer_activism_outlined,
        onAction: () => ref.refresh(projectsProvider),
        actionLabel: 'Refresh',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: projects.length,
      itemBuilder: (context, index) {
        final p = projects[index];
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
              onTap: () => context.push('/projects/${p.id}'),
              borderRadius: BorderRadius.circular(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Image
                  ClipRRect(
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                    child: AspectRatio(
                      aspectRatio: 16 / 9,
                      child: AppNetworkImage(
                        imageUrl: p.imageUrl,
                        fallbackAsset: 'assets/branding/logo_abvhps.png',
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),

                  // Info
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          p.name,
                          style: const TextStyle(
                            color: AppTheme.darkNavy,
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          p.shortInfo,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 12.5,
                            height: 1.4,
                          ),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 12),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'View Project Details',
                              style: TextStyle(
                                color: AppTheme.primaryOrange,
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color: AppTheme.lightOrange,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.arrow_forward_ios,
                                size: 12,
                                color: AppTheme.primaryOrange,
                              ),
                            ),
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
