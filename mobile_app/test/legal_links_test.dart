import 'package:abvhpsapp/core/config/app_config.dart';
import 'package:abvhpsapp/core/widgets/legal_links_note.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('legal URLs are built on the website origin (API base without /api/v1)', () {
    expect(AppConfig.siteUrl, isNot(contains('/api/')));
    expect(AppConfig.privacyPolicyUrl, '${AppConfig.siteUrl}/privacy-policy');
    expect(AppConfig.termsUrl, '${AppConfig.siteUrl}/terms-and-conditions');
    expect(AppConfig.refundPolicyUrl, '${AppConfig.siteUrl}/refund-cancellation-policy');
    expect(AppConfig.donationPolicyUrl, '${AppConfig.siteUrl}/donation-payment-policy');
    expect(AppConfig.accountDeletionUrl, '${AppConfig.siteUrl}/account-deletion');
  });

  testWidgets('sign-in legal strip shows both links', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: Scaffold(bottomNavigationBar: LegalLinksNote())));

    expect(find.text('Privacy Policy'), findsOneWidget);
    expect(find.text('Terms & Conditions'), findsOneWidget);
    expect(find.byKey(const Key('legal_privacy_link')), findsOneWidget);
    expect(find.byKey(const Key('legal_terms_link')), findsOneWidget);
  });
}
