import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/project_model.dart';

final projectDetailProvider = FutureProvider.autoDispose.family<ProjectModel, int>((ref, id) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getProject(id);
});

class ProjectDetailScreen extends ConsumerWidget {
  final int projectId;

  const ProjectDetailScreen({
    super.key,
    required this.projectId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final projectAsync = ref.watch(projectDetailProvider(projectId));

    return PublicScaffold(
      title: 'Project Overview',
      body: projectAsync.when(
        data: (project) => _buildDetail(context, project),
        loading: () => const AppLoadingState(message: 'Loading project information...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(projectDetailProvider(projectId)),
        ),
      ),
    );
  }

  Widget _buildDetail(BuildContext context, ProjectModel p) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Back button row
          TextButton.icon(
            onPressed: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/projects');
              }
            },
            icon: const Icon(Icons.arrow_back, size: 18, color: AppTheme.primaryOrange),
            label: const Text(
              'Back to Projects',
              style: TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),

          // Project Image
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: AppNetworkImage(
                imageUrl: p.imageUrl,
                fallbackAsset: 'assets/branding/logo_abvhps.png',
                fit: BoxFit.cover,
              ),
            ),
          ),
          const SizedBox(height: 18),

          // Project Name
          Text(
            p.name,
            style: const TextStyle(
              color: AppTheme.darkNavy,
              fontSize: 18,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.4,
            ),
          ),
          const SizedBox(height: 12),

          // Project Body
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'ABOUT THIS INITIATIVE',
                  style: TextStyle(
                    color: AppTheme.primaryOrange,
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  p.shortInfo,
                  style: const TextStyle(
                    color: Colors.black87,
                    fontSize: 14,
                    height: 1.6,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Action button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () => context.push('/contact'),
              icon: const Icon(Icons.volunteer_activism, size: 18),
              label: const Text('JOIN / SUPPORT THIS PROJECT'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryOrange,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
