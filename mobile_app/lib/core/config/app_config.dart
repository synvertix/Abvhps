class AppConfig {
  /// Base API URL configured at compile time.
  /// Android Emulator Default: `http://10.0.2.2:8000/api/v1`
  /// Local Dev / LAN: `http://192.168.1.x:8000/api/v1`
  /// Production: `https://abvhps.org/api/v1`
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  /// Website origin (the API base URL without `/api/v1`), used for the public legal pages.
  static String get siteUrl {
    final base = apiBaseUrl.replaceFirst(RegExp(r'/api/v\d+/?$'), '');
    return base.endsWith('/') ? base.substring(0, base.length - 1) : base;
  }

  /// Public legal documents (also required by app stores).
  static String get privacyPolicyUrl => '$siteUrl/privacy-policy';
  static String get termsUrl => '$siteUrl/terms-and-conditions';
  static String get refundPolicyUrl => '$siteUrl/refund-cancellation-policy';
  static String get donationPolicyUrl => '$siteUrl/donation-payment-policy';
  static String get accountDeletionUrl => '$siteUrl/account-deletion';

  static const String appName = 'ABVHPS';
  static const String organizationName = 'Akhanda Bharata Viswa Hindu Parirakshana Samiti';
  static const String defaultPhone = '+91 9989980055';
  static const String defaultEmail = 'info@abvhps.org';
  static const String defaultAddress = 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193';

  /// WebSocket / Reverb Configuration
  static const String wsHost = String.fromEnvironment(
    'WS_HOST',
    defaultValue: '10.0.2.2',
  );

  static const int wsPort = int.fromEnvironment(
    'WS_PORT',
    defaultValue: 8080,
  );

  static const String wsScheme = String.fromEnvironment(
    'WS_SCHEME',
    defaultValue: 'ws',
  );

  static const String reverbAppKey = String.fromEnvironment(
    'REVERB_APP_KEY',
    defaultValue: 'l99cn5jjtikyyrn7pvx3',
  );
}
