import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class MainHeader extends StatelessWidget {
  final VoidCallback onOpenDrawer;

  const MainHeader({
    super.key,
    required this.onOpenDrawer,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Left: Emblem + Wordmark
          Expanded(
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Circular Emblem (matches Blade 56px circular border)
                Container(
                  width: 48,
                  height: 48,
                  padding: const EdgeInsets.all(2),
                  decoration: BoxDecoration(
                    color: AppTheme.lightOrange,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: AppTheme.primaryOrange,
                      width: 2,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withValues(alpha: 0.05),
                        blurRadius: 4,
                      ),
                    ],
                  ),
                  child: ClipOval(
                    child: Image.asset(
                      'assets/branding/logo_abvhps.png',
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) => const Icon(
                        Icons.shield,
                        color: AppTheme.primaryOrange,
                        size: 28,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),

                // Stylized Wordmark (image fallback or native typography)
                Flexible(
                  child: Image.asset(
                    'assets/branding/logo.png',
                    height: 40,
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) => const Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'ABVHPS',
                          style: TextStyle(
                            color: AppTheme.primaryOrange,
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.8,
                          ),
                        ),
                        Text(
                          'Akhanda Bharata Viswa Hindu Parirakshana Samiti',
                          style: TextStyle(
                            color: AppTheme.neutralGray,
                            fontSize: 8,
                            fontWeight: FontWeight.w700,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: 8),

          // Right: Hamburger Button
          IconButton(
            key: const Key('drawer_hamburger_button'),
            onPressed: onOpenDrawer,
            icon: Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                color: const Color(0xFFFFF7ED), // orange-50
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: const Color(0xFFFED7AA), // orange-200
                  width: 1,
                ),
              ),
              child: const Icon(
                Icons.menu,
                color: AppTheme.primaryOrange,
                size: 26,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
