import 'package:url_launcher/url_launcher.dart';

class UrlHelper {
  /// Safely launches a public URL if the scheme is permitted.
  static Future<bool> launchSafeUrl(String? urlString) async {
    if (urlString == null || urlString.trim().isEmpty) {
      return false;
    }

    final trimmed = urlString.trim();
    final uri = Uri.tryParse(trimmed);
    if (uri == null) return false;

    // Disallow dangerous or unexpected schemes
    final allowedSchemes = {'http', 'https', 'tel', 'mailto', 'whatsapp'};
    if (!allowedSchemes.contains(uri.scheme.toLowerCase())) {
      return false;
    }

    try {
      return await launchUrl(
        uri,
        mode: LaunchMode.externalApplication,
      );
    } catch (_) {
      return false;
    }
  }

  /// Initiates a phone call to a given number.
  static Future<bool> callPhone(String? phone) async {
    if (phone == null || phone.trim().isEmpty) return false;
    final digits = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    return launchSafeUrl('tel:$digits');
  }

  /// Opens email client with recipient.
  static Future<bool> sendEmail(String? email, {String? subject}) async {
    if (email == null || email.trim().isEmpty) return false;
    final cleanEmail = email.trim();
    final query = subject != null ? '?subject=${Uri.encodeComponent(subject)}' : '';
    return launchSafeUrl('mailto:$cleanEmail$query');
  }

  /// Opens WhatsApp conversation with a given phone number or link.
  static Future<bool> openWhatsApp(String? urlOrPhone) async {
    if (urlOrPhone == null || urlOrPhone.trim().isEmpty) return false;
    final trimmed = urlOrPhone.trim();
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return launchSafeUrl(trimmed);
    }
    final digits = trimmed.replaceAll(RegExp(r'[^0-9]'), '');
    return launchSafeUrl('https://wa.me/$digits');
  }
}
