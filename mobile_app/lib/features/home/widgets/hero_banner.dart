import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_network_image.dart';

class HeroBanner extends StatelessWidget {
  final Map<String, dynamic>? banner;
  final List<dynamic>? sliders;

  const HeroBanner({
    super.key,
    this.banner,
    this.sliders,
  });

  @override
  Widget build(BuildContext context) {
    final double screenWidth = MediaQuery.of(context).size.width;
    // Responsive portrait/tall banner height matching website 380-420px
    final double heroHeight = (screenWidth * 1.05).clamp(360.0, 430.0);

    final String? bannerUrl = banner?['mobile_banner'] ?? banner?['desktop_banner'];
    final String title = banner?['title'] ??
        (sliders != null && sliders!.isNotEmpty ? sliders![0]['title'] : 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti');
    final String subtitle = banner?['subtitle'] ??
        (sliders != null && sliders!.isNotEmpty ? sliders![0]['subtitle'] : 'Preserving Sanathana Dharma and Empowering Communities');

    return Container(
      width: double.infinity,
      height: heroHeight,
      decoration: const BoxDecoration(
        color: Color(0xFF0F172A),
        border: Border(
          bottom: BorderSide(color: AppTheme.primaryOrange, width: 4),
        ),
      ),
      child: Stack(
        fit: StackFit.expand,
        children: [
          // Background Image (Dynamic or permanent fallback)
          AppNetworkImage(
            imageUrl: bannerUrl,
            fallbackAsset: 'assets/branding/ourteam_bg.png',
            fit: BoxFit.cover,
            errorWidget: Container(
              color: const Color(0xFF0F172A),
            ),
          ),

          // Dark overlay matching website rgba(5, 15, 30, 0.45)
          Container(
            color: const Color(0xFF050F1E).withValues(alpha: 0.48),
          ),

          // Content Text
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Text(
                  title.toUpperCase(),
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                    height: 1.25,
                    shadows: [
                      Shadow(
                        color: Colors.black87,
                        blurRadius: 10,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  subtitle,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: AppTheme.lightOrange,
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    height: 1.4,
                    shadows: [
                      Shadow(
                        color: Colors.black87,
                        blurRadius: 8,
                        offset: Offset(0, 1),
                      ),
                    ],
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
