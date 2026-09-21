import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/admin_dashboard_provider.dart';
import 'package:abvhpsapp/features/admin/admin_dashboard_screen.dart';
import 'package:abvhpsapp/features/admin/team/admin_team_screen.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/models/admin_dashboard_model.dart';

void main() {
  final sampleDashboardData = AdminDashboardData(
    administrator: const AdminUser(name: 'Commander Test', email: 'admin@abvhps.org'),
    summary: const AdminSummary(
      totalProfiles: 125,
      volunteers: 42,
      pendingActions: 7,
      fundsRaised: 250000.0,
    ),
    wings: const AdminWings(
      centralBase: 125,
      rudraSena: 38,
      kalaBrundham: 14,
      gramaSevaDal: 22,
      organicFarmers: 19,
      dharmaSeva: 3,
    ),
    pending: const AdminPending(
      volunteers: 4,
      memberships: 3,
      examApplications: 8,
      resultsPublished: 2,
      activeCampaigns: 3,
    ),
    system: const AdminSystem(
      application: 'Running',
      database: 'Connected',
      storage: 'Writable',
      totalRecords: 167,
      totalExams: 5,
      activeCampaigns: 3,
      databaseStatus: 'ok',
      storageStatus: 'ok',
    ),
    exams: const AdminExams(
      total: 5,
      active: 2,
      applications: 8,
      resultsPublished: 2,
    ),
    fundraising: const AdminFundraising(
      totalCampaigns: 4,
      activeCampaigns: 3,
      totalDonors: 58,
      amountRaised: 250000.0,
    ),
    content: const AdminContent(
      blogs: 12,
      publishedBlogs: 10,
      galleryMedia: 35,
      supportCores: 6,
    ),
    recentActivity: const [
      AdminActivity(
        action: 'ADMIN LOGIN SUCCESS',
        actorType: 'User',
        actorIdentifier: 'admin@abvhps.org',
        targetType: 'Auth',
        formattedTime: '28-Aug 13:30',
      ),
    ],
  );

  Widget createTestWidget({AdminDashboardData? data, Object? error, Completer<AdminDashboardData>? completer}) {
    return ProviderScope(
      overrides: [
        if (completer != null)
          adminDashboardDataProvider.overrideWith((ref) => completer.future)
        else if (error != null)
          adminDashboardDataProvider.overrideWith((ref) => Future<AdminDashboardData>.error(error))
        else
          adminDashboardDataProvider.overrideWith((ref) => Future.value(data ?? sampleDashboardData)),
      ],
      child: const MaterialApp(
        home: AdminDashboardScreen(),
      ),
    );
  }

  group('Admin Dashboard & Control Desk Drawer Tests', () {
    testWidgets('1. Admin Dashboard renders complete web parity layout and metrics', (tester) async {
      tester.view.physicalSize = const Size(800, 3000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(createTestWidget());
      await tester.pumpAndSettle();

      // Top Bar
      expect(find.text('ABVHPS Admin Portal'), findsOneWidget);
      expect(find.byIcon(Icons.menu), findsOneWidget);
      expect(find.byIcon(Icons.refresh), findsOneWidget);
      expect(find.byIcon(Icons.logout), findsOneWidget);

      // Administrator Card
      expect(find.text('Commander Test'), findsOneWidget);
      expect(find.text('admin@abvhps.org'), findsOneWidget);
      expect(find.text('AUTHENTICATED SYSTEM ADMINISTRATOR'), findsOneWidget);

      // Executive Summary
      expect(find.text('EXECUTIVE SUMMARY'), findsOneWidget);
      expect(find.text('TOTAL PROFILES'), findsOneWidget);
      expect(find.text('125'), findsWidgets);
      expect(find.text('VOLUNTEERS'), findsOneWidget);
      expect(find.text('42'), findsOneWidget);
      expect(find.text('PENDING ACTIONS'), findsOneWidget);
      expect(find.text('7'), findsOneWidget);
      expect(find.text('FUNDS RAISED'), findsOneWidget);
      expect(find.text('₹2,50,000'), findsWidgets);

      // Organizational Wings
      expect(find.text('ORGANIZATIONAL WING OVERVIEW'), findsOneWidget);
      expect(find.text('Central Base'), findsOneWidget);
      expect(find.text('Rudra Sena'), findsOneWidget);
      expect(find.text('Kala Brundham'), findsOneWidget);
      expect(find.text('Grama Seva Dal'), findsOneWidget);
      expect(find.text('Organic Farmers'), findsOneWidget);
      expect(find.text('Dharma Seva'), findsOneWidget);

      // Admin Attention — Pending Actions
      expect(find.text('ADMIN ATTENTION — PENDING ACTIONS'), findsOneWidget);
      expect(find.text('Volunteers awaiting approval'), findsOneWidget);
      expect(find.text('Memberships awaiting review'), findsOneWidget);
      expect(find.text('Exam applications received'), findsOneWidget);

      // System Status
      expect(find.text('SYSTEM STATUS'), findsOneWidget);
      expect(find.text('Application'), findsOneWidget);
      expect(find.text('Running'), findsOneWidget);
      expect(find.text('Database'), findsOneWidget);
      expect(find.text('Connected'), findsOneWidget);
      expect(find.text('Storage'), findsOneWidget);
      expect(find.text('Writable'), findsOneWidget);

      // Mini Panels
      expect(find.text('EXAMINATION OVERVIEW'), findsOneWidget);
      expect(find.text('DHARMA SEVA FUNDRAISING'), findsOneWidget);
      expect(find.text('CONTENT OVERVIEW'), findsOneWidget);

      // Recent Activity
      expect(find.text('RECENT SYSTEM ACTIVITY'), findsOneWidget);
      expect(find.text('ADMIN LOGIN SUCCESS'), findsOneWidget);

      // Quick Actions
      expect(find.text('QUICK ACTIONS'), findsOneWidget);
      expect(find.text('Review Memberships'), findsOneWidget);
      expect(find.text('Review Volunteers'), findsOneWidget);
      expect(find.text('Site Settings'), findsOneWidget);
    });

    testWidgets('2. Admin Drawer opens and renders all 5 sections and 20 module items', (tester) async {
      tester.view.physicalSize = const Size(800, 2000);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);

      await tester.pumpWidget(createTestWidget());
      await tester.pumpAndSettle();

      // Tap hamburger menu to open drawer
      await tester.tap(find.byIcon(Icons.menu));
      await tester.pumpAndSettle();

      // Header
      expect(find.text('ABVHPS CENTRAL'), findsOneWidget);
      expect(find.text('ADMIN CONTROL DESK'), findsOneWidget);

      // Section 1: MAIN
      expect(find.text('MAIN'), findsOneWidget);
      expect(find.text('Dashboard Home'), findsOneWidget);

      // Section 2: WINGS & CONTENT
      expect(find.text('WINGS & CONTENT'), findsOneWidget);
      expect(find.text('Our Team'), findsOneWidget);
      expect(find.text('Donations Ledger'), findsOneWidget);
      expect(find.text('Blogs Manager'), findsOneWidget);
      expect(find.text('Media Gallery'), findsOneWidget);
      expect(find.text('Our Support Cores'), findsOneWidget);

      // Section 3: MEMBERSHIP & CADRES
      expect(find.text('MEMBERSHIP & CADRES'), findsOneWidget);
      expect(find.text('Approved Membership'), findsOneWidget);
      expect(find.text('Pending Membership List'), findsOneWidget);
      expect(find.text('Volunteer Desk'), findsOneWidget);
      expect(find.text('Volunteer Events'), findsOneWidget);
      expect(find.text('Rudrasena'), findsOneWidget);
      expect(find.text('Local GP Gateways'), findsOneWidget);

      // Section 4: SERVICES & CORES
      expect(find.text('SERVICES & CORES'), findsOneWidget);
      expect(find.text('Exams Info Board'), findsOneWidget);
      expect(find.text('Fundraising Matrices'), findsOneWidget);
      expect(find.text('Contact Forms Audit'), findsOneWidget);
      expect(find.text('Tax Certificates'), findsOneWidget);
      expect(find.text('Site Global Settings'), findsOneWidget);
      expect(find.text('Banner Management'), findsOneWidget);

      // Section 5: SYSTEM
      expect(find.text('SYSTEM'), findsOneWidget);
      expect(find.text('WhatsApp Support'), findsOneWidget);
      expect(find.text('Sign Out'), findsWidgets);
    });

    testWidgets('3. Native Admin Team Screen renders cleanly', (tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            adminTeamProvider('').overrideWith((ref) => Future.value([])),
          ],
          child: const MaterialApp(
            home: AdminTeamScreen(),
          ),
        ),
      );
      await tester.pumpAndSettle();

      expect(find.text('OUR TEAM ROSTER'), findsOneWidget);
    });

    testWidgets('4a. Admin Dashboard renders loading state', (tester) async {
      final completer = Completer<AdminDashboardData>();
      await tester.pumpWidget(createTestWidget(completer: completer));
      await tester.pump();

      expect(find.byType(CircularProgressIndicator), findsOneWidget);
      expect(find.text('Loading administrative dashboard metrics...'), findsOneWidget);

      completer.complete(sampleDashboardData);
      await tester.pumpAndSettle();
    });

    testWidgets('4b. Admin Dashboard renders error state with retry button', (tester) async {
      await tester.pumpWidget(createTestWidget(error: Exception('Network connection timed out')));
      await tester.pumpAndSettle();

      expect(find.text('Unable to Load Admin Metrics'), findsOneWidget);
      expect(find.text('Network connection timed out'), findsOneWidget);
      expect(find.text('Retry Fetch'), findsOneWidget);
    });

    testWidgets('5. Responsive layout test across viewports without overflow', (tester) async {
      final viewports = [
        const Size(360, 800),
        const Size(390, 844),
        const Size(412, 915),
      ];

      for (final size in viewports) {
        tester.view.physicalSize = size;
        tester.view.devicePixelRatio = 1.0;
        addTearDown(tester.view.resetPhysicalSize);

        await tester.pumpWidget(createTestWidget());
        await tester.pumpAndSettle();

        expect(tester.takeException(), isNull);
      }
    });
  });
}
