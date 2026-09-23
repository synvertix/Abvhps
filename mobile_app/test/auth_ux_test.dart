import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/core/api/api_client.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';
import 'package:abvhpsapp/core/auth/auth_state.dart';
import 'package:abvhpsapp/core/storage/token_storage.dart';
import 'package:abvhpsapp/features/auth/login_screen.dart';
import 'package:abvhpsapp/features/auth/volunteer_change_password_screen.dart';

class MockTokenStorage implements TokenStorage {
  String? token;
  String? accountType;

  MockTokenStorage({this.token, this.accountType});

  @override
  Future<String?> getToken() async => token;

  @override
  Future<void> saveToken(String t) async {
    token = t;
  }

  @override
  Future<void> clearToken() async {
    token = null;
    accountType = null;
  }

  @override
  Future<String?> getAccountType() async => accountType;

  @override
  Future<void> saveAccountType(String a) async {
    accountType = a;
  }
}

class MockAuthNotifier extends AuthNotifier {
  MockAuthNotifier(AuthState initial, {TokenStorage? storage})
      : super(
          apiClient: ApiClient(tokenStorage: storage ?? MockTokenStorage()),
          tokenStorage: storage ?? MockTokenStorage(),
        ) {
    state = initial;
  }

  @override
  Future<void> checkInitialAuth() async {}

  @override
  Future<bool> loginAdmin({
    required String email,
    required String password,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    return email == 'admin@abvhps.org' && password == 'AdminSecret123!';
  }

  @override
  Future<bool> loginVolunteer({
    required String loginId,
    required String password,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    return loginId == '100001' && password == 'Secret123';
  }

  @override
  Future<String?> sendMemberOtp({required String phone}) async {
    if (phone == '9876543210') {
      return 'mock-challenge-uuid-1234';
    }
    return null;
  }

  @override
  Future<bool> verifyMemberOtp({
    required String phone,
    required String challengeId,
    required String otp,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    return otp == '123456';
  }
}

void main() {
  testWidgets('Auth UX: LoginScreen renders branding, official title, and tabs', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final mockStorage = MockTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(mockStorage),
          authNotifierProvider.overrideWith(
            (ref) => MockAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: mockStorage,
            ),
          ),
        ],
        child: const MaterialApp(
          home: LoginScreen(initialType: 'admin'),
        ),
      ),
    );
    await tester.pumpAndSettle();

    // 1. Verify Official Branding
    expect(find.text('AKHANDA BHARATA'), findsOneWidget);
    expect(find.text('VISWA HINDU PARIRAKSHANA SAMITI'), findsOneWidget);
    expect(find.text('Sign in to ABVHPS'), findsOneWidget);

    // 2. Verify Tabs
    expect(find.text('ADMIN'), findsOneWidget);
    expect(find.text('VOLUNTEER'), findsOneWidget);
    expect(find.text('MEMBER (OTP)'), findsOneWidget);
  });

  testWidgets('Auth UX: Admin Login password visibility toggle functions correctly', (WidgetTester tester) async {
    final mockStorage = MockTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(mockStorage),
          authNotifierProvider.overrideWith(
            (ref) => MockAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: mockStorage,
            ),
          ),
        ],
        child: const MaterialApp(
          home: LoginScreen(initialType: 'admin'),
        ),
      ),
    );
    await tester.pumpAndSettle();

    // Find password field and verify obscureText
    final passwordFieldFinder = find.widgetWithText(TextField, 'Security Password');
    expect(passwordFieldFinder, findsOneWidget);
    TextField passwordField = tester.widget<TextField>(passwordFieldFinder);
    expect(passwordField.obscureText, isTrue);

    // Tap visibility toggle
    final toggleFinder = find.byTooltip('Show password');
    expect(toggleFinder, findsOneWidget);
    await tester.tap(toggleFinder);
    await tester.pumpAndSettle();

    // Verify obscureText is now false
    passwordField = tester.widget<TextField>(passwordFieldFinder);
    expect(passwordField.obscureText, isFalse);
    expect(find.byTooltip('Hide password'), findsOneWidget);
  });

  testWidgets('Auth UX: Volunteer Login password visibility toggle functions correctly', (WidgetTester tester) async {
    final mockStorage = MockTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(mockStorage),
          authNotifierProvider.overrideWith(
            (ref) => MockAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: mockStorage,
            ),
          ),
        ],
        child: const MaterialApp(
          home: LoginScreen(initialType: 'volunteer'),
        ),
      ),
    );
    await tester.pumpAndSettle();

    // Find password field and verify obscureText
    final passwordFieldFinder = find.widgetWithText(TextField, 'Password');
    expect(passwordFieldFinder, findsOneWidget);
    TextField passwordField = tester.widget<TextField>(passwordFieldFinder);
    expect(passwordField.obscureText, isTrue);

    // Tap visibility toggle
    final toggleFinder = find.byTooltip('Show password');
    expect(toggleFinder, findsOneWidget);
    await tester.tap(toggleFinder);
    await tester.pumpAndSettle();

    // Verify obscureText is now false
    passwordField = tester.widget<TextField>(passwordFieldFinder);
    expect(passwordField.obscureText, isFalse);
    expect(find.byTooltip('Hide password'), findsOneWidget);
  });

  testWidgets('Auth UX: Member OTP 2-step flow renders masked phone number and OTP input', (WidgetTester tester) async {
    final mockStorage = MockTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(mockStorage),
          authNotifierProvider.overrideWith(
            (ref) => MockAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: mockStorage,
            ),
          ),
        ],
        child: const MaterialApp(
          home: LoginScreen(initialType: 'member'),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Member OTP Login'), findsOneWidget);
    expect(find.text('SEND OTP'), findsOneWidget);

    // Enter valid 10-digit mobile number
    await tester.enterText(find.byType(TextField).first, '9876543210');
    await tester.pumpAndSettle();

    // Tap SEND OTP
    await tester.tap(find.text('SEND OTP'));
    await tester.pumpAndSettle();

    // Verify OTP input view appears with masked phone
    expect(find.text('OTP sent to +91 ******3210'), findsOneWidget);
    expect(find.text('6-digit OTP Code'), findsOneWidget);
    expect(find.text('VERIFY & LOGIN'), findsOneWidget);
    expect(find.text('Use a different mobile number'), findsOneWidget);
  });

  testWidgets('Auth UX: Volunteer Change Password screen renders 3 password fields with visibility toggles', (WidgetTester tester) async {
    final mockStorage = MockTokenStorage(token: 'restricted-token', accountType: 'volunteer');

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(mockStorage),
          authNotifierProvider.overrideWith(
            (ref) => MockAuthNotifier(
              const AuthState(
                isAuthenticated: true,
                isLoading: false,
                accountType: 'volunteer',
                mustChangePassword: true,
              ),
              storage: mockStorage,
            ),
          ),
        ],
        child: const MaterialApp(
          home: VolunteerChangePasswordScreen(),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Change Temporary Password'), findsOneWidget);
    expect(find.text('Security Requirement'), findsOneWidget);
    expect(find.widgetWithText(TextField, 'Current Temporary Password'), findsOneWidget);
    expect(find.widgetWithText(TextField, 'New Password (min 8 characters)'), findsOneWidget);
    expect(find.widgetWithText(TextField, 'Confirm New Password'), findsOneWidget);
    expect(find.text('UPDATE PASSWORD & CONTINUE'), findsOneWidget);

    // Verify 3 show password toggles exist
    expect(find.byTooltip('Show password'), findsNWidgets(3));
  });

  testWidgets('Auth UX: Responsive verification across 360x800 and 390x844 without overflow', (WidgetTester tester) async {
    final viewports = [
      const Size(360, 800),
      const Size(390, 844),
      const Size(412, 915),
    ];

    for (final vp in viewports) {
      tester.view.physicalSize = vp;
      tester.view.devicePixelRatio = 1.0;

      final mockStorage = MockTokenStorage();

      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            tokenStorageProvider.overrideWithValue(mockStorage),
            authNotifierProvider.overrideWith(
              (ref) => MockAuthNotifier(
                const AuthState(isAuthenticated: false, isLoading: false),
                storage: mockStorage,
              ),
            ),
          ],
          child: const MaterialApp(
            home: LoginScreen(initialType: 'admin'),
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(tester.takeException(), isNull);
    }
  });
}
