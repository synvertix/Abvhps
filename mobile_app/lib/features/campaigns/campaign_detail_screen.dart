import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/url_helper.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/campaign_model.dart';

final campaignDetailProvider = FutureProvider.autoDispose.family<CampaignModel, int>((ref, id) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getCampaign(id);
});

class CampaignDetailScreen extends ConsumerWidget {
  final int campaignId;

  const CampaignDetailScreen({
    super.key,
    required this.campaignId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final campaignAsync = ref.watch(campaignDetailProvider(campaignId));

    return PublicScaffold(
      title: 'Campaign Details',
      body: campaignAsync.when(
        data: (campaign) => _buildDetail(context, campaign),
        loading: () => const AppLoadingState(message: 'Loading campaign details...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(campaignDetailProvider(campaignId)),
        ),
      ),
    );
  }

  Widget _buildDetail(BuildContext context, CampaignModel c) {
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
                context.go('/campaigns');
              }
            },
            icon: const Icon(Icons.arrow_back, size: 18, color: AppTheme.primaryOrange),
            label: const Text(
              'Back to Campaigns',
              style: TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),

          // Main Image
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: AspectRatio(
              aspectRatio: 16 / 9,
              child: AppNetworkImage(
                imageUrl: c.imageUrl,
                fallbackAsset: 'assets/branding/logo_abvhps.png',
                fit: BoxFit.cover,
              ),
            ),
          ),
          const SizedBox(height: 18),

          // Title
          Text(
            c.title,
            style: const TextStyle(
              color: AppTheme.darkNavy,
              fontSize: 18,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.4,
            ),
          ),
          const SizedBox(height: 14),

          // Progress Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: LinearProgressIndicator(
                    value: (c.percent / 100).clamp(0.0, 1.0),
                    backgroundColor: Colors.grey.shade200,
                    valueColor: const AlwaysStoppedAnimation<Color>(AppTheme.primaryOrange),
                    minHeight: 10,
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('RAISED', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                        Text(
                          c.raisedFormatted,
                          style: const TextStyle(color: AppTheme.primaryOrange, fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.center,
                      children: [
                        const Text('ACHIEVED', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                        Text(
                          '${c.percent.toStringAsFixed(1)}%',
                          style: const TextStyle(color: AppTheme.darkNavy, fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        const Text('TARGET GOAL', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                        Text(
                          c.targetFormatted,
                          style: const TextStyle(color: AppTheme.darkNavy, fontSize: 15, fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                  ],
                ),
                if (c.endDate != null) ...[
                  const Divider(color: Colors.black12, height: 20),
                  Row(
                    children: [
                      const Icon(Icons.event_outlined, size: 16, color: Colors.grey),
                      const SizedBox(width: 6),
                      Text(
                        'Target Completion Date: ${c.endDate}',
                        style: const TextStyle(color: Colors.black54, fontSize: 12, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),

          const SizedBox(height: 18),

          // Description Card
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
                  'ABOUT THIS SEVA CAUSE',
                  style: TextStyle(
                    color: AppTheme.primaryOrange,
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  c.description,
                  style: const TextStyle(
                    color: Colors.black87,
                    fontSize: 14,
                    height: 1.6,
                  ),
                ),
              ],
            ),
          ),

          if (c.galleryImages.isNotEmpty) ...[
            const SizedBox(height: 18),
            const Text(
              'SEVA PHOTO GALLERY',
              style: TextStyle(
                color: AppTheme.darkNavy,
                fontSize: 12,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.8,
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              height: 100,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: c.galleryImages.length,
                itemBuilder: (context, index) {
                  return Container(
                    margin: const EdgeInsets.only(right: 10),
                    width: 130,
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: AppNetworkImage(
                        imageUrl: c.galleryImages[index],
                        fit: BoxFit.cover,
                      ),
                    ),
                  );
                },
              ),
            ),
          ],

          const SizedBox(height: 24),

          // WhatsApp Share Button
          if (c.whatsappShareUrl != null && c.whatsappShareUrl!.isNotEmpty)
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () => UrlHelper.launchSafeUrl(c.whatsappShareUrl),
                icon: const Icon(Icons.share, size: 18, color: Color(0xFF25D366)),
                label: const Text('SHARE ON WHATSAPP'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF25D366),
                  side: const BorderSide(color: Color(0xFF25D366), width: 1.5),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
              ),
            ),

          const SizedBox(height: 12),

          // Public Web Donation Action
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () {
                if (c.publicUrl != null && c.publicUrl!.isNotEmpty) {
                  UrlHelper.launchSafeUrl(c.publicUrl);
                } else {
                  context.push('/contact');
                }
              },
              icon: const Icon(Icons.favorite, size: 18),
              label: const Text('DONATE / SUPPORT VIA CENTRAL PORTAL'),
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
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
