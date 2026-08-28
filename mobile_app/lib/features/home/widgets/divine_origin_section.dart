import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class DivineOriginSection extends StatelessWidget {
  const DivineOriginSection({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section Eyebrow Tag
          const Text(
            'OUR DIVINE ORIGIN',
            style: TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 12,
              fontWeight: FontWeight.w900,
              letterSpacing: 1.2,
            ),
          ),
          const SizedBox(height: 6),

          // Main Heading
          const Text(
            'Why and How ABVHPS Was Founded',
            style: TextStyle(
              color: AppTheme.neutralGray,
              fontSize: 22,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.2,
              height: 1.2,
            ),
          ),
          const SizedBox(height: 14),

          // Origin Paragraph 1
          const Text(
            'The Akhanda Bharata Viswa Hindu Parirakshana Samithi was set up in the year of 2023 and having the Registration Number 20/2023 for the social process. It recognizes activities preserving Sanatana Dharma under the behest of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.6,
              fontWeight: FontWeight.w400,
            ),
          ),
          const SizedBox(height: 12),

          // Origin Paragraph 2
          const Text(
            'This charitable trust is dedicated to uplift mankind mentally, morally, or physically. Trust is to beautify designated villages and focus on spiritual awareness, temple wellbeing, and deep patriotism.',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 13,
              height: 1.6,
              fontWeight: FontWeight.w400,
            ),
          ),
          const SizedBox(height: 20),

          // Divine Blessings Callout Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppTheme.lightOrange,
              borderRadius: BorderRadius.circular(8),
              border: const Border(
                left: BorderSide(color: AppTheme.primaryOrange, width: 4),
              ),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Divine Blessings',
                  style: TextStyle(
                    color: AppTheme.primaryOrange,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                SizedBox(height: 8),
                Text(
                  '"Our main objective is to protect Hindu Sanathana Dharma, construct new temples, expand Goushalas, distribute daily meals under Annapurna, and support children\'s literacy across every Grama Panchayat."',
                  style: TextStyle(
                    color: AppTheme.neutralGray,
                    fontSize: 12,
                    fontStyle: FontStyle.italic,
                    height: 1.5,
                  ),
                ),
                SizedBox(height: 10),
                Align(
                  alignment: Alignment.centerRight,
                  child: Text(
                    '- Sri Sri Sri Subrahmanneswara Swamy Garu',
                    style: TextStyle(
                      color: AppTheme.neutralGray,
                      fontSize: 11,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
