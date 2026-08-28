import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class LiveStatsSection extends StatelessWidget {
  final Map<String, dynamic>? stats;

  const LiveStatsSection({
    super.key,
    this.stats,
  });

  @override
  Widget build(BuildContext context) {
    final donors = stats?['donors'] ?? 0;
    final members = stats?['members'] ?? 0;
    final volunteers = stats?['volunteers'] ?? 0;
    final years = stats?['years'] ?? 2;

    return Container(
      width: double.infinity,
      color: AppTheme.primaryOrange,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: _buildStatItem(
                  count: donors.toString(),
                  label: 'VERIFIED DONORS',
                ),
              ),
              Expanded(
                child: _buildStatItem(
                  count: members.toString(),
                  label: 'REGISTERED MEMBERS',
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _buildStatItem(
                  count: volunteers.toString(),
                  label: 'TOTAL VOLUNTEERS',
                ),
              ),
              Expanded(
                child: _buildStatItem(
                  count: years.toString(),
                  label: 'YEARS OF SERVICE',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatItem({required String count, required String label}) {
    return Column(
      children: [
        Text(
          count,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 28,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.5,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.9),
            fontSize: 10,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.8,
          ),
        ),
      ],
    );
  }
}
