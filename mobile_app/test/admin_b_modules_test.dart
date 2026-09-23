import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/models/admin_membership_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_volunteer_model.dart';
import 'package:abvhpsapp/features/admin/models/admin_rudrasena_model.dart';
import 'package:abvhpsapp/features/admin/memberships/admin_approved_memberships_screen.dart';
import 'package:abvhpsapp/features/admin/rudrasena/admin_rudrasena_screen.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_b_repositories.dart';

void main() {
  group('ADMIN-B Native Models Unit Tests', () {
    test('AdminMembership.fromJson parses complete payload correctly', () {
      final json = {
        'id': 1,
        'membership_id': '922493121520',
        'full_name': 'RAMA RAO',
        'phone': '9989980055',
        'email': 'ramarao@abvhps.org',
        'payment_status': 'success',
        'is_completed': true,
        'district': 'HYDERABAD',
        'mandal': 'HYDERABAD',
        'grama_panchayat': 'CENTRAL GP',
        'state': 'TELANGANA',
        'photo_url': null,
        'created_at': '2026-08-28 10:00:00',
      };

      final item = AdminMembership.fromJson(json);
      expect(item.id, 1);
      expect(item.membershipId, '922493121520');
      expect(item.fullName, 'RAMA RAO');
      expect(item.isCompleted, true);
      expect(item.district, 'HYDERABAD');
    });

    test('AdminVolunteer.fromJson parses volunteer payload correctly', () {
      final json = {
        'id': 10,
        'volunteer_id': '100001',
        'membership_id': '922493121520',
        'name': 'BHARAT KUMAR',
        'phone': '9888777666',
        'email': 'vol1@abvhps.org',
        'cadre': 'Mandal President',
        'qualification': 'Graduate',
        'status': 'approved',
        'is_active': true,
        'district': 'HYDERABAD',
      };

      final item = AdminVolunteer.fromJson(json);
      expect(item.id, 10);
      expect(item.volunteerId, '100001');
      expect(item.cadre, 'Mandal President');
      expect(item.isActive, true);
    });

    test('AdminRudrasenaMember.fromJson parses age and eligibility correctly', () {
      final json = {
        'id': 5,
        'rudrasena_id': 'RS0001',
        'membership_id': '999900001111',
        'name': 'RUDRA COMMANDER',
        'mobile': '9988776655',
        'volunteer_type': 'Field Commander',
        'assigned_cadder': 'Dal Leader',
        'assigned_locality': 'State HQ',
        'status': 'verified',
        'dob': '1995-05-15',
        'age': 30,
        'is_age_eligible': true,
      };

      final item = AdminRudrasenaMember.fromJson(json);
      expect(item.id, 5);
      expect(item.rudrasenaId, 'RS0001');
      expect(item.age, 30);
      expect(item.isAgeEligible, true);
    });
  });

  group('ADMIN-B Native Screens Widget Tests', () {
    testWidgets('AdminApprovedMembershipsScreen renders title and search bar', (tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            adminApprovedMembershipsProvider('').overrideWith((ref) async => [
                  const AdminMembership(
                    id: 1,
                    membershipId: '922493121520',
                    fullName: 'RAMA RAO',
                    phone: '9989980055',
                    isCompleted: true,
                  ),
                ]),
          ],
          child: const MaterialApp(
            home: AdminApprovedMembershipsScreen(),
          ),
        ),
      );

      await tester.pumpAndSettle();

      expect(find.text('APPROVED MEMBERSHIPS'), findsOneWidget);
      expect(find.byType(TextField), findsOneWidget);
      expect(find.text('RAMA RAO'), findsOneWidget);
      expect(find.text('ID: 922493121520'), findsOneWidget);
    });

    testWidgets('AdminRudrasenaScreen renders Rudrasena cadets and age badge', (tester) async {
      await tester.pumpWidget(
        ProviderScope(
          overrides: [
            adminRudrasenaProvider('').overrideWith((ref) async => [
                  const AdminRudrasenaMember(
                    id: 5,
                    rudrasenaId: 'RS0001',
                    membershipId: '999900001111',
                    name: 'RUDRA COMMANDER',
                    mobile: '9988776655',
                    volunteerType: 'Field Commander',
                    assignedCadder: 'Dal Leader',
                    assignedLocality: 'State HQ',
                    status: 'verified',
                    age: 30,
                    isAgeEligible: true,
                  ),
                ]),
          ],
          child: const MaterialApp(
            home: AdminRudrasenaScreen(),
          ),
        ),
      );

      await tester.pumpAndSettle();

      expect(find.text('RUDRASENA DESK'), findsOneWidget);
      expect(find.text('RUDRA COMMANDER'), findsOneWidget);
      expect(find.text('RS ID: RS0001'), findsOneWidget);
      expect(find.text('Age: 30 (24-44 OK)'), findsOneWidget);
    });
  });
}
