import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:flutter_test/flutter_test.dart';
import 'package:abvhpsapp/core/i18n/i18n.dart';
import 'package:abvhpsapp/features/home/widgets/live_stats_section.dart';
import 'package:abvhpsapp/features/home/widgets/vision_mission_section.dart';

Widget _host(I18n i18n, Widget child) => MaterialApp(
      builder: (context, c) => I18nScope(i18n: i18n, child: c ?? const SizedBox.shrink()),
      home: Scaffold(body: SingleChildScrollView(child: child)),
    );

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('I18n core', () {
    test('English is the default and returns the key itself', () {
      final i18n = I18n();
      expect(i18n.code, 'en');
      expect(i18n.t('Our Vision'), 'Our Vision');
    });

    test('switching to Telugu translates, unknown keys fall back to English, placeholders are filled', () async {
      final i18n = I18n();
      await i18n.setLanguage('te');

      expect(i18n.code, 'te');
      expect(i18n.t('Our Vision'), 'మా దృక్పథం');
      expect(i18n.t('A sentence nobody translated'), 'A sentence nobody translated');
      expect(
        i18n.t('Why and How :name Was Founded', args: {'name': 'ABVHPS'}),
        allOf(contains('ABVHPS'), isNot(contains(':name'))),
      );

      await i18n.setLanguage('en');
      expect(i18n.t('Our Vision'), 'Our Vision');
    });

    test('an unsupported language code is ignored', () async {
      final i18n = I18n();
      await i18n.setLanguage('xx');
      expect(i18n.code, 'en');
    });

    test('every listed language has a complete dictionary with the same keys', () async {
      Map<String, dynamic> load(String raw) => json.decode(raw) as Map<String, dynamic>;
      final reference = load(await rootBundle.loadString('assets/i18n/hi.json'));
      expect(reference, isNotEmpty);

      for (final lang in kAppLanguages.where((l) => l.code != 'en')) {
        final map = load(await rootBundle.loadString('assets/i18n/${lang.code}.json'));
        expect(map.keys.toList(), reference.keys.toList(), reason: '${lang.code} keys differ from hi');
        for (final entry in map.entries) {
          expect(entry.value.toString().trim(), isNotEmpty, reason: '${lang.code}: ${entry.key}');
        }
      }
      expect(kAppLanguages.length, 11);
    });
  });

  group('Widgets follow the selected language', () {
    testWidgets('Vision / Mission / Goal switch to Telugu and back', (tester) async {
      final i18n = I18n();
      await tester.pumpWidget(_host(i18n, const VisionMissionSection()));
      expect(find.text('Our Vision'), findsOneWidget);

      await tester.runAsync(() => i18n.setLanguage('te'));
      await tester.pumpAndSettle();
      expect(find.text('మా దృక్పథం'), findsOneWidget);
      expect(find.text('Our Vision'), findsNothing);

      await tester.runAsync(() => i18n.setLanguage('en'));
      await tester.pumpAndSettle();
      expect(find.text('Our Vision'), findsOneWidget);
    });

    testWidgets('stats band hides zero counters and never invents numbers', (tester) async {
      final i18n = I18n();
      await tester.pumpWidget(_host(i18n, const LiveStatsSection(stats: {'donors': 0, 'members': 0, 'volunteers': 0, 'years': 3})));

      expect(find.text('3'), findsOneWidget);
      expect(find.text('YEARS OF SERVICE'), findsOneWidget);
      expect(find.text('VERIFIED DONORS'), findsNothing);
      expect(find.text('2023'), findsOneWidget); // true fact filler
      expect(find.text('20/2023'), findsOneWidget);
    });
  });
}
