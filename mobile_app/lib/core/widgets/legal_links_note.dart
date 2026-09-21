import 'package:flutter/material.dart';

import '../config/app_config.dart';
import '../i18n/i18n.dart';
import '../utils/url_helper.dart';

/// Small "Privacy Policy · Terms & Conditions" strip shown on sign-in screens.
/// The links open the official web pages (also the URLs given to the app store).
class LegalLinksNote extends StatelessWidget {
  const LegalLinksNote({super.key});

  @override
  Widget build(BuildContext context) {
    const linkStyle = TextStyle(
      color: Color(0xFFC2410C),
      fontSize: 11.5,
      fontWeight: FontWeight.w700,
      decoration: TextDecoration.underline,
    );

    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
        child: Wrap(
          alignment: WrapAlignment.center,
          crossAxisAlignment: WrapCrossAlignment.center,
          spacing: 14,
          children: [
            InkWell(
              key: const Key('legal_privacy_link'),
              onTap: () => UrlHelper.launchSafeUrl(AppConfig.privacyPolicyUrl),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Text(context.tr('Privacy Policy'), style: linkStyle),
              ),
            ),
            InkWell(
              key: const Key('legal_terms_link'),
              onTap: () => UrlHelper.launchSafeUrl(AppConfig.termsUrl),
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Text(context.tr('Terms & Conditions'), style: linkStyle),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
