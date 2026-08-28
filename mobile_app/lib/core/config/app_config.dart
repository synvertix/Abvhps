class AppConfig {
  /// Base API URL configured at compile time.
  /// Android Emulator Default: `http://10.0.2.2:8000/api/v1`
  /// Local Dev / LAN: `http://192.168.1.x:8000/api/v1`
  /// Production: `https://abvhps.org/api/v1`
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  static const String appName = 'ABVHPS';
  static const String organizationName = 'Akhanda Bharata Viswa Hindu Parirakshana Samiti';
  static const String defaultPhone = '+91 8884933379';
  static const String defaultEmail = 'info@abvhps.org';
  static const String defaultAddress = 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193';
}
