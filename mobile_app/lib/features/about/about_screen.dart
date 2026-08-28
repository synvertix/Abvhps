import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/about_model.dart';

final aboutDataProvider = FutureProvider.autoDispose<AboutData>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getAbout();
});

class AboutScreen extends ConsumerWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final aboutAsync = ref.watch(aboutDataProvider);

    return PublicScaffold(
      title: 'About Us — ABVHPS',
      body: aboutAsync.when(
        data: (data) => _buildContent(context, data),
        loading: () => const AppLoadingState(message: 'Loading organization profile...'),
        error: (err, stack) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(aboutDataProvider),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, AboutData data) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Banner / Emblem Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.primaryOrange.withValues(alpha: 0.3)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.1),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              children: [
                Container(
                  width: 80,
                  height: 80,
                  padding: const EdgeInsets.all(3),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppTheme.primaryOrange, width: 2.5),
                  ),
                  child: ClipOval(
                    child: AppNetworkImage(
                      imageUrl: data.organization.logoUrl,
                      fallbackAsset: 'assets/branding/logo_abvhps.png',
                      fit: BoxFit.contain,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  data.organization.name,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryOrange.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: AppTheme.primaryOrange, width: 1),
                  ),
                  child: Text(
                    'Reg. No: ${data.organization.registrationNo} | Estd. ${data.organization.foundedYear}',
                    style: const TextStyle(
                      color: AppTheme.primaryOrange,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  data.organization.tagline,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 12,
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 18),

          // Founder & Guru Recognition Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF7ED), // orange-50
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFFDBA74)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.workspace_premium, color: AppTheme.primaryOrange, size: 28),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'FOUNDER & SPIRITUAL GUIDE',
                        style: TextStyle(
                          color: AppTheme.primaryOrange,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        data.organization.founderGuru,
                        style: const TextStyle(
                          color: AppTheme.darkNavy,
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Mission Section
          _buildSectionCard(
            title: data.mission.title,
            icon: Icons.flag_rounded,
            content: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: data.mission.paragraphs
                  .map((p) => Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: Text(
                          p,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 13,
                            height: 1.5,
                          ),
                        ),
                      ))
                  .toList(),
            ),
          ),

          const SizedBox(height: 20),

          // 4 Core Values Section
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 4),
            child: Text(
              'OUR CORE VALUES',
              style: TextStyle(
                color: AppTheme.darkNavy,
                fontSize: 14,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.8,
              ),
            ),
          ),
          const SizedBox(height: 10),
          ...data.coreValues.map((v) => _buildValueCard(v)),

          const SizedBox(height: 20),

          // Pillars / Vision & Goals
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 4),
            child: Text(
              'ORGANIZATIONAL PILLARS',
              style: TextStyle(
                color: AppTheme.darkNavy,
                fontSize: 14,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.8,
              ),
            ),
          ),
          const SizedBox(height: 10),
          ...data.pillars.map((p) => _buildPillarCard(p)),

          const SizedBox(height: 30),
        ],
      ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required Widget content,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: AppTheme.primaryOrange, size: 22),
              const SizedBox(width: 10),
              Text(
                title.toUpperCase(),
                style: const TextStyle(
                  color: AppTheme.darkNavy,
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0.6,
                ),
              ),
            ],
          ),
          const Divider(color: Colors.black12, height: 20),
          content,
        ],
      ),
    );
  }

  Widget _buildValueCard(CoreValueItem v) {
    IconData iconData = Icons.star;
    if (v.icon == 'temple') iconData = Icons.account_balance;
    if (v.icon == 'handshake') iconData = Icons.volunteer_activism;
    if (v.icon == 'sprout') iconData = Icons.eco;
    if (v.icon == 'shield') iconData = Icons.shield;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppTheme.lightOrange,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(iconData, color: AppTheme.primaryOrange, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  v.title,
                  style: const TextStyle(
                    color: AppTheme.darkNavy,
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  v.description,
                  style: const TextStyle(
                    color: Colors.black54,
                    fontSize: 12,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPillarCard(PillarItem p) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            p.title,
            style: const TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 13,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            p.description,
            style: const TextStyle(
              color: Colors.black87,
              fontSize: 12,
              height: 1.4,
            ),
          ),
        ],
      ),
    );
  }
}
