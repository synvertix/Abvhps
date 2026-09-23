import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// One selectable app language. The translation file is `assets/i18n/<code>.json`
/// (the very same files the website uses, keyed by the English sentence).
class AppLanguage {
  final String code;
  final String native;

  const AppLanguage(this.code, this.native);
}

const List<AppLanguage> kAppLanguages = [
  AppLanguage('en', 'English'),
  AppLanguage('hi', 'हिन्दी'),
  AppLanguage('te', 'తెలుగు'),
  AppLanguage('ta', 'தமிழ்'),
  AppLanguage('kn', 'ಕನ್ನಡ'),
  AppLanguage('ml', 'മലയാളം'),
  AppLanguage('mr', 'मराठी'),
  AppLanguage('bn', 'বাংলা'),
  AppLanguage('gu', 'ગુજરાતી'),
  AppLanguage('or', 'ଓଡ଼ିଆ'),
  AppLanguage('pa', 'ਪੰਜਾਬੀ'),
];

/// Holds the chosen language + its dictionary. English needs no dictionary: the English
/// sentence itself is the key, so an untranslated string always falls back to English.
class I18n extends ChangeNotifier {
  static const String _storageKey = 'app_language';

  final FlutterSecureStorage _storage;
  final AssetBundle _bundle;

  String _code = 'en';
  Map<String, String> _dictionary = const {};

  I18n({FlutterSecureStorage? storage, AssetBundle? bundle})
      : _storage = storage ?? const FlutterSecureStorage(),
        _bundle = bundle ?? rootBundle;

  String get code => _code;

  AppLanguage get language =>
      kAppLanguages.firstWhere((l) => l.code == _code, orElse: () => kAppLanguages.first);

  /// Restores the saved language (silently stays on English if nothing is saved / storage is unavailable).
  Future<void> restore() async {
    try {
      final saved = await _storage.read(key: _storageKey);
      if (saved != null && saved != _code && kAppLanguages.any((l) => l.code == saved)) {
        await _apply(saved);
      }
    } catch (_) {
      // No saved preference available — English stays.
    }
  }

  Future<void> setLanguage(String code) async {
    if (!kAppLanguages.any((l) => l.code == code)) return;
    await _apply(code);
    try {
      await _storage.write(key: _storageKey, value: code);
    } catch (_) {
      // The choice still applies for this session.
    }
  }

  Future<void> _apply(String code) async {
    Map<String, String> dictionary = const {};
    if (code != 'en') {
      try {
        final raw = await _bundle.loadString('assets/i18n/$code.json');
        dictionary = (json.decode(raw) as Map<String, dynamic>).map((k, v) => MapEntry(k, v.toString()));
      } catch (_) {
        code = 'en'; // asset missing/corrupt → stay in English rather than show nothing
      }
    }
    _code = code;
    _dictionary = dictionary;
    notifyListeners();
  }

  /// Translates [english]; `:name`-style placeholders are filled from [args].
  String t(String english, {Map<String, String>? args}) {
    var text = _dictionary[english] ?? english;
    if (args != null) {
      args.forEach((name, value) => text = text.replaceAll(':$name', value));
    }
    return text;
  }
}

final i18nProvider = ChangeNotifierProvider<I18n>((ref) {
  final i18n = I18n();
  i18n.restore();
  return i18n;
});

/// Makes the current [I18n] available to the whole widget tree so `context.tr(...)` rebuilds on change.
class I18nScope extends InheritedNotifier<I18n> {
  const I18nScope({super.key, required I18n i18n, required super.child}) : super(notifier: i18n);

  static I18n? maybeOf(BuildContext context) =>
      context.dependOnInheritedWidgetOfExactType<I18nScope>()?.notifier;
}

extension TranslateContext on BuildContext {
  /// `context.tr('Our Vision')` → the sentence in the app's current language.
  String tr(String english, {Map<String, String>? args}) {
    final i18n = I18nScope.maybeOf(this);
    if (i18n == null) {
      var text = english;
      args?.forEach((name, value) => text = text.replaceAll(':$name', value));
      return text;
    }
    return i18n.t(english, args: args);
  }
}
