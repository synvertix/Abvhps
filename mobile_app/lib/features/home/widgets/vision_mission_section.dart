import 'package:flutter/material.dart';
import '../../../core/i18n/i18n.dart';
import '../../../core/theme/app_theme.dart';

class VisionMissionSection extends StatelessWidget {
  const VisionMissionSection({super.key});

  // English source sentences — also the translation keys (see assets/i18n/*.json).
  static const String _vision =
      'To see Sanatana Dharma flourish in every village — with temples restored and newly built as living centres of prayer, learning and seva, and with every family, whatever their means, treated with dignity, equality and love.';
  static const String _mission =
      'To gather willing hearts as members and volunteers and turn devotion into service — offering Annapurna meals to the hungry, education to children, relief to the poor and medical aid to the sick, with humility and without expectation.';
  static const String _goal =
      'To protect our sacred traditions, rituals and festivals and hand them down, unbroken, to the next generation — building a united family of devotees, strong in brotherhood and working together, from every village to every corner of the world.';

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [AppTheme.creamBg, Color(0xFFFFFDF8)],
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: Column(
        children: [
          _buildHeading(context),
          const SizedBox(height: 18),
          _buildPillarCard(
            icon: Icons.wb_sunny_outlined,
            title: context.tr('Our Vision'),
            description: context.tr(_vision),
          ),
          const SizedBox(height: 14),
          _buildPillarCard(
            icon: Icons.favorite_border,
            title: context.tr('Our Mission'),
            description: context.tr(_mission),
          ),
          const SizedBox(height: 14),
          _buildPillarCard(
            icon: Icons.local_fire_department_outlined,
            title: context.tr('The Goal'),
            description: context.tr(_goal),
          ),
        ],
      ),
    );
  }

  Widget _buildHeading(BuildContext context) {
    return Column(
      children: [
        Text(
          context.tr('Vision · Mission · Goal').toUpperCase(),
          style: const TextStyle(
            color: Color(0xFFB8860B),
            fontSize: 10,
            fontWeight: FontWeight.w900,
            letterSpacing: 2.6,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          context.tr('The Sacred Purpose Behind Our Seva'),
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: AppTheme.neutralGray,
            fontSize: 20,
            fontWeight: FontWeight.w800,
          ),
        ),
        const SizedBox(height: 8),
        const _GoldDivider(width: 44),
        const SizedBox(height: 8),
        Text(
          context.tr(
              'Rooted in Dharma, driven by Seva — three promises we hold to, together with every member, volunteer and well-wisher.'),
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: AppTheme.textSecondary,
            fontSize: 12,
            height: 1.5,
          ),
        ),
      ],
    );
  }

  Widget _buildPillarCard({
    required IconData icon,
    required String title,
    required String description,
  }) {
    return Container(
      width: double.infinity,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFCE7B0)),
        boxShadow: [
          BoxShadow(
            color: AppTheme.templeGold.withValues(alpha: 0.10),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Gold crest along the top edge
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              height: 3,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    AppTheme.templeGold.withValues(alpha: 0.0),
                    AppTheme.templeGold,
                    AppTheme.templeGold.withValues(alpha: 0.0),
                  ],
                ),
              ),
            ),
          ),
          // Soft lotus watermark
          Positioned(
            right: -22,
            bottom: -22,
            child: IgnorePointer(
              child: Icon(
                Icons.spa,
                size: 110,
                color: AppTheme.templeGold.withValues(alpha: 0.10),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 22, 20, 20),
            child: SizedBox(
              width: double.infinity,
              child: Column(
                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Color(0xFFFFF3D1), Color(0xFFFBE3A1)],
                      ),
                      border: Border.all(
                        color: AppTheme.templeGold.withValues(alpha: 0.55),
                        width: 2,
                      ),
                    ),
                    child: Icon(icon, size: 26, color: const Color(0xFFB8860B)),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    title,
                    style: const TextStyle(
                      color: AppTheme.neutralGray,
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const _GoldDivider(width: 30),
                  const SizedBox(height: 10),
                  Text(
                    description,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: AppTheme.textSecondary,
                      fontSize: 13,
                      height: 1.6,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Thin gold rule with a small diamond in the centre — the devotional ornament.
class _GoldDivider extends StatelessWidget {
  final double width;

  const _GoldDivider({required this.width});

  @override
  Widget build(BuildContext context) {
    Widget line() => Container(
          width: width,
          height: 1,
          color: AppTheme.templeGold.withValues(alpha: 0.6),
        );

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        line(),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 6),
          child: Icon(Icons.diamond, size: 8, color: AppTheme.templeGold),
        ),
        line(),
      ],
    );
  }
}
