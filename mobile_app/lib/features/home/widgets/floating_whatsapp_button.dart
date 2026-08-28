import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class FloatingWhatsAppButton extends StatelessWidget {
  final String? whatsappUrl;
  final String? whatsappNumber;

  const FloatingWhatsAppButton({
    super.key,
    this.whatsappUrl,
    this.whatsappNumber,
  });

  @override
  Widget build(BuildContext context) {
    if (whatsappNumber == null && whatsappUrl == null) {
      return const SizedBox.shrink();
    }

    return FloatingActionButton(
      onPressed: () {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Connect on WhatsApp: ${whatsappNumber ?? "+91 9989980055"}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            backgroundColor: AppTheme.whatsappGreen,
            duration: const Duration(seconds: 3),
          ),
        );
      },
      backgroundColor: AppTheme.whatsappGreen,
      foregroundColor: Colors.white,
      elevation: 6,
      shape: const CircleBorder(),
      child: const Icon(
        Icons.chat,
        size: 28,
      ),
    );
  }
}
