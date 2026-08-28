import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/campaign_model.dart';

final campaignsProvider = FutureProvider.autoDispose<List<CampaignModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getCampaigns();
});

class CampaignsListScreen extends ConsumerWidget {
  const CampaignsListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final campaignsAsync = ref.watch(campaignsProvider);

    return PublicScaffold(
      title: 'Active Campaigns & Seva',
      body: campaignsAsync.when(
        data: (campaigns) => _buildList(context, campaigns, ref),
        loading: () => const AppLoadingState(message: 'Loading active fundraising appeals...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(campaignsProvider),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<CampaignModel> campaigns, WidgetRef ref) {
    if (campaigns.isEmpty) {
      return AppEmptyState(
        title: 'No Active Campaigns',
        subtitle: 'All recent seva appeals have been successfully fulfilled.',
        icon: Icons.campaign_outlined,
        onAction: () => ref.refresh(campaignsProvider),
        actionLabel: 'Refresh',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: campaigns.length,
      itemBuilder: (context, index) {
        final c = campaigns[index];
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
              onTap: () => context.push('/campaigns/${c.id}'),
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
                        imageUrl: c.imageUrl,
                        fallbackAsset: 'assets/branding/logo_abvhps.png',
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),

                  // Content
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          c.title,
                          style: const TextStyle(
                            color: AppTheme.darkNavy,
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          c.description,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 12.5,
                            height: 1.4,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 14),

                        // Progress bar
                        ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: LinearProgressIndicator(
                            value: (c.percent / 100).clamp(0.0, 1.0),
                            backgroundColor: Colors.grey.shade200,
                            valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryOrange),
                            minHeight: 8,
                          ),
                        ),
                        const SizedBox(height: 8),

                        // Financial Stats
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'RAISED',
                                  style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  c.raisedFormatted,
                                  style: const TextStyle(
                                    color: AppTheme.primaryOrange,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.center,
                              children: [
                                const Text(
                                  'PROGRESS',
                                  style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  '${c.percent.toStringAsFixed(1)}%',
                                  style: const TextStyle(
                                    color: AppTheme.darkNavy,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                const Text(
                                  'GOAL',
                                  style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold),
                                ),
                                Text(
                                  c.targetFormatted,
                                  style: const TextStyle(
                                    color: AppTheme.darkNavy,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ],
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
