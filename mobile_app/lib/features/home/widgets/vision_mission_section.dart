import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class VisionMissionSection extends StatelessWidget {
  const VisionMissionSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: AppTheme.lightGray,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: Column(
        children: [
          _buildPillarCard(
            icon: '👁️',
            title: 'Our Vision',
            description:
                'To develop Temples as a part of Sanathana Dharma, construct new worship spaces, and bring social equality to the underprivileged sections of society.',
          ),
          const SizedBox(height: 14),
          _buildPillarCard(
            icon: '🚀',
            title: 'Our Mission',
            description:
                'Giving voluntary memberships to those ready to deliver services covering poor relief, education, food distribution, and medical aid maps.',
          ),
          const SizedBox(height: 14),
          _buildPillarCard(
            icon: '🎯',
            title: 'The Goal',
            description:
                'Protect and promote Hindu traditions, rituals, and festivals for future generations, fostering brotherhood and collaboration globally.',
          ),
        ],
      ),
    );
  }

  Widget _buildPillarCard({
    required String icon,
    required String title,
    required String description,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(icon, style: const TextStyle(fontSize: 26)),
          const SizedBox(height: 8),
          Text(
            title,
            style: const TextStyle(
              color: AppTheme.neutralGray,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            description,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 12,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }
}
