import 'package:flutter/material.dart';
import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';

class PublicFooter extends StatelessWidget {
  final Map<String, dynamic>? contact;

  const PublicFooter({
    super.key,
    this.contact,
  });

  @override
  Widget build(BuildContext context) {
    final phone = contact?['phone'] ?? AppConfig.defaultPhone;
    final email = contact?['email'] ?? AppConfig.defaultEmail;
    final address = contact?['address'] ?? AppConfig.defaultAddress;

    return Container(
      color: AppTheme.darkGray,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // About ABVHPS
          const Text(
            'About ABVHPS',
            style: TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Dedicated to preserving and promoting Hindu culture and values worldwide under the behest of Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 12,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 20),

          // Contact Info
          const Text(
            'Contact Us',
            style: TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 16,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            address,
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 12,
              height: 1.5,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            '📞 $phone',
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '✉️ $email',
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 24),
          const Divider(color: Colors.white12),
          const SizedBox(height: 12),
          Center(
            child: Text(
              '© ${DateTime.now().year} ABVHPS. All Rights Reserved.',
              style: const TextStyle(
                color: Colors.white38,
                fontSize: 11,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
