import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/app/app.dart';
import 'package:abvhpsapp/core/api/api_client.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';
import 'package:abvhpsapp/core/auth/auth_state.dart';
import 'package:abvhpsapp/core/storage/token_storage.dart';
import 'package:abvhpsapp/features/about/about_screen.dart';
import 'package:abvhpsapp/features/about/models/about_model.dart';
import 'package:abvhpsapp/features/auth/login_screen.dart';
import 'package:abvhpsapp/features/auth/volunteer_change_password_screen.dart';
import 'package:abvhpsapp/features/blogs/blogs_screen.dart';
import 'package:abvhpsapp/features/campaigns/campaigns_list_screen.dart';
import 'package:abvhpsapp/features/contact/contact_screen.dart';
import 'package:abvhpsapp/features/exams/exams_notice_board_screen.dart';
import 'package:abvhpsapp/features/exams/exam_results_screen.dart';
import 'package:abvhpsapp/features/gallery/gallery_screen.dart';
import 'package:abvhpsapp/features/home/home_screen.dart';
import 'package:abvhpsapp/features/home/home_provider.dart';
import 'package:abvhpsapp/features/member/member_dashboard_screen.dart';
import 'package:abvhpsapp/features/team/our_team_screen.dart';
import 'package:abvhpsapp/features/volunteer/volunteer_dashboard_screen.dart';
import 'package:abvhpsapp/features/wings/wing_detail_screen.dart';

class FakeTokenStorage implements TokenStorage {
  String? token;
  String? accountType;

  FakeTokenStorage({this.token, this.accountType});

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

class TestAuthNotifier extends AuthNotifier {
  TestAuthNotifier(AuthState initial, {TokenStorage? storage})
      : super(
          apiClient: ApiClient(tokenStorage: storage ?? FakeTokenStorage()),
          tokenStorage: storage ?? FakeTokenStorage(),
        ) {
    state = initial;
  }

  @override
  Future<void> checkInitialAuth() async {
    // No-op for tests
  }
}

void main() {
  final testHomeData = {
    'contact': {
      'phone': '+91 8884933379',
      'email': 'info@abvhps.org',
      'whatsapp_number': '+91 9989980055',
    },
    'social_strip': {
      'platforms': [
        {'id': 'facebook', 'name': 'Facebook'},
        {'id': 'instagram', 'name': 'Instagram'},
      ],
    },
    'banner': {
      'title': 'AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI',
      'subtitle': 'Preserving Sanathana Dharma and Empowering Communities',
    },
    'stats': {
      'donors': 120,
      'members': 450,
      'volunteers': 85,
      'years': 3,
    },
  };

  testWidgets('1. Unauthenticated app renders mobile website parity Home Screen with top bar, header, hero, origin, and drawer', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) => Future.value(testHomeData)),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byType(HomeScreen), findsOneWidget);
    expect(find.byKey(const Key('drawer_hamburger_button')), findsOneWidget);
    expect(find.text('+91 8884933379'), findsOneWidget);
    expect(find.text('info@abvhps.org'), findsOneWidget);

    expect(find.text('OUR DIVINE ORIGIN'), findsOneWidget);
    expect(find.text('Why and How ABVHPS Was Founded'), findsOneWidget);
    expect(find.text('Our Vision'), findsOneWidget);
    expect(find.text('Our Mission'), findsOneWidget);
    expect(find.text('The Goal'), findsOneWidget);

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    expect(find.text('ABVHPS CENTRAL'), findsOneWidget);
    expect(find.text('EXPLORE SAMITI'), findsOneWidget);
    expect(find.text('ACADEMICS & SERVICES'), findsOneWidget);
    expect(find.text('OUR WINGS SUBSYSTEMS'), findsOneWidget);
    expect(find.text('COMMUNITY & SUPPORT'), findsOneWidget);
    expect(find.byKey(const Key('drawer_login_portals_button')), findsOneWidget);
    expect(find.byKey(const Key('drawer_make_donation_button')), findsOneWidget);
  });

  testWidgets('2. Volunteer login route from drawer Login Portals displays validation when submitted with empty fields', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    // Open Login Portals Modal
    await tester.tap(find.byKey(const Key('drawer_login_portals_button')));
    await tester.pumpAndSettle();

    expect(find.text('SELECT LOGIN PORTAL'), findsOneWidget);
    expect(find.byKey(const Key('drawer_volunteer_login_button')), findsOneWidget);

    await tester.tap(find.byKey(const Key('drawer_volunteer_login_button')));
    await tester.pumpAndSettle();

    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.text('Volunteer Sign In'), findsOneWidget);

    await tester.tap(find.text('LOGIN AS VOLUNTEER'));
    await tester.pumpAndSettle();

    expect(find.text('Please enter Volunteer ID and Password.'), findsOneWidget);
  });

  testWidgets('3. Member login OTP route from drawer Login Portals displays validation for invalid mobile number', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    // Open Login Portals Modal
    await tester.tap(find.byKey(const Key('drawer_login_portals_button')));
    await tester.pumpAndSettle();

    expect(find.text('SELECT LOGIN PORTAL'), findsOneWidget);
    expect(find.byKey(const Key('drawer_member_login_button')), findsOneWidget);

    await tester.tap(find.byKey(const Key('drawer_member_login_button')));
    await tester.pumpAndSettle();

    expect(find.byType(LoginScreen), findsOneWidget);
    expect(find.text('Member OTP Login'), findsOneWidget);

    await tester.enterText(find.byType(TextField).first, '12345');
    await tester.pumpAndSettle();

    await tester.tap(find.text('SEND OTP'));
    await tester.pumpAndSettle();

    expect(find.text('Please enter a valid 10-digit mobile number.'), findsOneWidget);
  });

  testWidgets('4. mustChangePassword auth state automatically routes to change-password screen', (WidgetTester tester) async {
    final fakeStorage = FakeTokenStorage(token: 'restricted-token', accountType: 'volunteer');

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(
                isAuthenticated: true,
                isLoading: false,
                accountType: 'volunteer',
                mustChangePassword: true,
                profile: {'full_name': 'New Volunteer', 'volunteer_id': '100001'},
              ),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byType(VolunteerChangePasswordScreen), findsOneWidget);
    expect(find.text('Change Temporary Password'), findsOneWidget);
  });

  testWidgets('5. Authenticated volunteer auth state routes to Volunteer Dashboard', (WidgetTester tester) async {
    final fakeStorage = FakeTokenStorage(token: 'valid-volunteer-token', accountType: 'volunteer');

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(
                isAuthenticated: true,
                isLoading: false,
                accountType: 'volunteer',
                mustChangePassword: false,
                profile: {'full_name': 'Active Volunteer', 'volunteer_id': '100002'},
              ),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byType(VolunteerDashboardScreen), findsOneWidget);
  });

  testWidgets('6. Authenticated member auth state routes to Member Dashboard', (WidgetTester tester) async {
    final fakeStorage = FakeTokenStorage(token: 'valid-member-token', accountType: 'member');

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(
                isAuthenticated: true,
                isLoading: false,
                accountType: 'member',
                profile: {'full_name': 'Registered Member', 'member_id': 'MEM001'},
              ),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.byType(MemberDashboardScreen), findsOneWidget);
  });

  testWidgets('7. Drawer navigates to ABOUT US and renders AboutScreen without crash', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
          aboutDataProvider.overrideWith(
            (ref) async => const AboutData(
              organization: OrganizationInfo(
                name: 'AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI',
                shortName: 'ABVHPS',
                tagline: 'Preserving Sanathana Dharma',
                registrationNo: '72/2020',
                foundedYear: 2020,
                founderGuru: 'Brahmasri Dr. Swamireddy',
              ),
              mission: MissionInfo(
                title: 'Our Mission & Calling',
                paragraphs: ['Preserve cultural roots and Vedic wisdom.'],
              ),
              coreValues: [
                CoreValueItem(
                  id: '1',
                  icon: 'temple',
                  title: 'Dharma Rakshana',
                  description: 'Upholding and protecting righteousness.',
                ),
              ],
              pillars: [
                PillarItem(
                  title: 'Sanathana Dharma Awareness',
                  description: 'Spreading ancient wisdom through regular workshops.',
                ),
              ],
            ),
          ),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    // Tap ABOUT US in drawer
    await tester.tap(find.text('ABOUT US'));
    await tester.pumpAndSettle();

    expect(find.byType(AboutScreen), findsOneWidget);
    expect(find.text('OUR CORE VALUES'), findsOneWidget);
    expect(find.text('Dharma Rakshana'), findsOneWidget);
    expect(find.text('ORGANIZATIONAL PILLARS'), findsOneWidget);
  });

  testWidgets('8. Drawer navigates to OUR TEAM and renders OurTeamScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('OUR TEAM'));
    await tester.pumpAndSettle();

    expect(find.byType(OurTeamScreen), findsOneWidget);
    expect(find.text('Search by name, ID, cadre or district...'), findsOneWidget);
  });

  testWidgets('9. Drawer navigates to MEDIA GALLERY and renders GalleryScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('MEDIA GALLERY'));
    await tester.pumpAndSettle();

    expect(find.byType(GalleryScreen), findsOneWidget);
  });

  testWidgets('10. Drawer expands EXAMS INFO & RESULTS accordion and navigates to notice board', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    // Tap accordion header to expand
    await tester.tap(find.text('EXAMS INFO & RESULTS'));
    await tester.pumpAndSettle();

    expect(find.text('EXAMS NOTICE BOARD'), findsOneWidget);
    expect(find.text('APPLY ONLINE'), findsOneWidget);
    expect(find.text('VIEW RESULTS'), findsOneWidget);

    await tester.tap(find.text('EXAMS NOTICE BOARD'));
    await tester.pumpAndSettle();

    expect(find.byType(ExamsNoticeBoardScreen), findsOneWidget);
  });

  testWidgets('11. Drawer expands EXAMS INFO & RESULTS and navigates to results portal', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('EXAMS INFO & RESULTS'));
    await tester.pumpAndSettle();

    await tester.tap(find.text('VIEW RESULTS'));
    await tester.pumpAndSettle();

    expect(find.byType(ExamResultsScreen), findsOneWidget);
  });

  testWidgets('12. Drawer expands OUR WINGS accordion and navigates to wing detail', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    // Tap accordion header to expand
    await tester.tap(find.text('OUR WINGS'));
    await tester.pumpAndSettle();

    expect(find.text('RUDRASENA DAL'), findsOneWidget);
    expect(find.text('KALA BRUNDAM'), findsOneWidget);
    expect(find.text('GRAMA SEVA DAL'), findsOneWidget);
    expect(find.text('ORGANIC FARMERS'), findsOneWidget);

    await tester.tap(find.text('RUDRASENA DAL'));
    await tester.pumpAndSettle();

    expect(find.byType(WingDetailScreen), findsOneWidget);
  });

  testWidgets('13. Drawer navigates to FUNDRAISE CAMPAIGNS and renders CampaignsListScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('FUNDRAISE CAMPAIGNS'));
    await tester.pumpAndSettle();

    expect(find.byType(CampaignsListScreen), findsOneWidget);
  });

  testWidgets('14. Drawer navigates to BLOGS & UPDATES and renders BlogsScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('BLOGS & UPDATES'));
    await tester.pumpAndSettle();

    expect(find.byType(BlogsScreen), findsOneWidget);
  });

  testWidgets('15. Drawer navigates to CONTACT US and renders ContactScreen', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.text('CONTACT US'));
    await tester.pumpAndSettle();

    expect(find.byType(ContactScreen), findsOneWidget);
  });

  testWidgets('16. Drawer MAKE A DONATION action navigates to campaigns/donations screen safely', (WidgetTester tester) async {
    tester.view.physicalSize = const Size(1080, 2400);
    tester.view.devicePixelRatio = 2.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final fakeStorage = FakeTokenStorage();

    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(fakeStorage),
          authNotifierProvider.overrideWith(
            (ref) => TestAuthNotifier(
              const AuthState(isAuthenticated: false, isLoading: false),
              storage: fakeStorage,
            ),
          ),
          homeDataProvider.overrideWith((ref) async => testHomeData),
        ],
        child: const AbvhpsApp(),
      ),
    );
    await tester.pumpAndSettle();

    final scaffoldFinder = find.byType(Scaffold);
    final scaffoldState = tester.state<ScaffoldState>(scaffoldFinder.first);
    scaffoldState.openDrawer();
    await tester.pumpAndSettle();

    await tester.tap(find.byKey(const Key('drawer_make_donation_button')));
    await tester.pumpAndSettle();

    expect(find.byType(CampaignsListScreen), findsOneWidget);
  });
}
