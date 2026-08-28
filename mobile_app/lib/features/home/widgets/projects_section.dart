import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_network_image.dart';

class ProjectsSection extends StatelessWidget {
  final List<dynamic>? projects;

  const ProjectsSection({
    super.key,
    this.projects,
  });

  @override
  Widget build(BuildContext context) {
    if (projects == null || projects!.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      color: AppTheme.lightGray,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'COMPREHENSIVE SEVA MODULES',
            style: TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 11,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Our Core Service Projects',
            style: TextStyle(
              color: AppTheme.neutralGray,
              fontSize: 22,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.2,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Dedicated community initiatives for heritage preservation and social welfare.',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12,
            ),
          ),
          const SizedBox(height: 8),
          Container(
            width: 48,
            height: 3,
            color: AppTheme.primaryOrange,
          ),
          const SizedBox(height: 18),

          // Projects List
          ...projects!.map((project) {
            final id = project['id'];
            final name = project['name'] ?? 'Core Seva Project';
            final info = project['short_info'] ?? '';
            final imageUrl = project['image_url'];

            return Container(
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.03),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              clipBehavior: Clip.antiAlias,
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: id != null ? () => context.push('/projects/$id') : null,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (imageUrl != null && imageUrl.toString().isNotEmpty)
                        SizedBox(
                          height: 140,
                          width: double.infinity,
                          child: AppNetworkImage(
                            imageUrl: imageUrl,
                            fit: BoxFit.cover,
                          ),
                        ),
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              name.toString().toUpperCase(),
                              style: const TextStyle(
                                color: AppTheme.neutralGray,
                                fontSize: 15,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              info,
                              style: const TextStyle(
                                color: AppTheme.textSecondary,
                                fontSize: 12,
                                height: 1.45,
                              ),
                              maxLines: 3,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),
        ],
      ),
    );
  }
}
