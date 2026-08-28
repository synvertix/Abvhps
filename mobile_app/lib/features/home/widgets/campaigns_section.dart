import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_network_image.dart';

class CampaignsSection extends StatelessWidget {
  final List<dynamic>? campaigns;

  const CampaignsSection({
    super.key,
    this.campaigns,
  });

  @override
  Widget build(BuildContext context) {
    if (campaigns == null || campaigns!.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'DHARMA SEVA INITIATIVES',
            style: TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 11,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Fundraising Campaigns',
            style: TextStyle(
              color: AppTheme.neutralGray,
              fontSize: 22,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.2,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Support meaningful initiatives and help us serve communities across India.',
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

          // Campaigns List
          ...campaigns!.map((campaign) {
            final id = campaign['id'];
            final title = campaign['title'] ?? 'Dharma Campaign';
            final desc = campaign['description'] ?? '';
            final imageUrl = campaign['image_url'];
            final raised = campaign['raised_formatted'] ?? '₹0';
            final target = campaign['target_formatted'] ?? '₹0';
            final percent = (campaign['percent'] is num) ? (campaign['percent'] as num).toDouble() : 0.0;
            final progressVal = (percent / 100.0).clamp(0.0, 1.0);

            return Container(
              margin: const EdgeInsets.only(bottom: 18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.black.withValues(alpha: 0.08)),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 8,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              clipBehavior: Clip.antiAlias,
              child: Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: id != null ? () => context.push('/campaigns/$id') : null,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Image
                      SizedBox(
                        height: 160,
                        width: double.infinity,
                        child: AppNetworkImage(
                          imageUrl: imageUrl,
                          fallbackAsset: 'assets/branding/fundraise_bg.png',
                          fit: BoxFit.cover,
                        ),
                      ),

                      // Content
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              title.toString().toUpperCase(),
                              style: const TextStyle(
                                color: AppTheme.neutralGray,
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                height: 1.3,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 6),
                            Text(
                              desc,
                              style: const TextStyle(
                                color: AppTheme.textSecondary,
                                fontSize: 12,
                                height: 1.4,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 14),

                            // Progress Bar
                            ClipRRect(
                              borderRadius: BorderRadius.circular(6),
                              child: LinearProgressIndicator(
                                value: progressVal,
                                backgroundColor: Colors.grey.shade200,
                                valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryOrange),
                                minHeight: 8,
                              ),
                            ),
                            const SizedBox(height: 8),

                            // Raised / Target
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  'Raised: $raised ($percent%)',
                                  style: const TextStyle(
                                    color: AppTheme.primaryOrange,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                Text(
                                  'Target: $target',
                                  style: const TextStyle(
                                    color: AppTheme.neutralGray,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
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
          }),
        ],
      ),
    );
  }
}
