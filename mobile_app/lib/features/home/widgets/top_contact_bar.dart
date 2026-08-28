import 'package:flutter/material.dart';
import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';

class TopContactBar extends StatelessWidget {
  final Map<String, dynamic>? contact;
  final List<dynamic>? socialLinks;

  const TopContactBar({
    super.key,
    this.contact,
    this.socialLinks,
  });

  @override
  Widget build(BuildContext context) {
    final phone = contact?['phone'] ?? AppConfig.defaultPhone;
    final email = contact?['email'] ?? AppConfig.defaultEmail;

    return Container(
      width: double.infinity,
      color: AppTheme.neutralGray,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          // Contact Info (Phone / Email)
          Expanded(
            child: Wrap(
              spacing: 12,
              runSpacing: 4,
              children: [
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text('📞', style: TextStyle(fontSize: 10)),
                    const SizedBox(width: 4),
                    Text(
                      phone,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text('✉️', style: TextStyle(fontSize: 10)),
                    const SizedBox(width: 4),
                    Text(
                      email,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Social icons if available
          if (socialLinks != null && socialLinks!.isNotEmpty)
            Row(
              mainAxisSize: MainAxisSize.min,
              children: socialLinks!.take(4).map((item) {
                final id = item['id']?.toString().toLowerCase() ?? '';
                IconData iconData = Icons.link;
                if (id.contains('facebook')) iconData = Icons.facebook;
                if (id.contains('instagram')) iconData = Icons.camera_alt;
                if (id.contains('youtube')) iconData = Icons.play_circle_fill;
                if (id.contains('whatsapp')) iconData = Icons.chat;

                return Container(
                  margin: const EdgeInsets.only(left: 6),
                  width: 22,
                  height: 22,
                  decoration: const BoxDecoration(
                    color: Colors.white12,
                    shape: BoxShape.circle,
                  ),
                  child: Center(
                    child: Icon(
                      iconData,
                      size: 13,
                      color: Colors.white,
                    ),
                  ),
                );
              }).toList(),
            ),
        ],
      ),
    );
  }
}
