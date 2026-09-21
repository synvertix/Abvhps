import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/auth/auth_notifier.dart';
import '../models/admin_membership_model.dart';
import '../models/admin_volunteer_model.dart';
import '../models/admin_volunteer_event_model.dart';
import '../models/admin_rudrasena_model.dart';
import '../models/admin_local_gateway_model.dart';

// --- Approved Memberships Provider ---
final adminApprovedMembershipsProvider = FutureProvider.family.autoDispose<List<AdminMembership>, String>((ref, search) async {
  final apiClient = ref.watch(apiClientProvider);
  final uri = search.trim().isEmpty 
      ? '/admin/memberships' 
      : '/admin/memberships?search=${Uri.encodeComponent(search.trim())}';
  
  final response = await apiClient.get(uri);
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminMembership.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load approved memberships.');
});

// --- Pending Memberships Provider ---
final adminPendingMembershipsProvider = FutureProvider.autoDispose<List<AdminMembership>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final response = await apiClient.get('/admin/memberships/pending');
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminMembership.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load pending memberships.');
});

// --- Volunteer Desk Provider ---
final adminVolunteersProvider = FutureProvider.family.autoDispose<List<AdminVolunteer>, String>((ref, search) async {
  final apiClient = ref.watch(apiClientProvider);
  final uri = search.trim().isEmpty 
      ? '/admin/volunteers' 
      : '/admin/volunteers?search=${Uri.encodeComponent(search.trim())}';

  final response = await apiClient.get(uri);
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminVolunteer.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load volunteer roster.');
});

// --- Volunteer Events Provider ---
final adminVolunteerEventsProvider = FutureProvider.autoDispose<List<AdminVolunteerEvent>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final response = await apiClient.get('/admin/volunteer-events');
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminVolunteerEvent.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load volunteer events.');
});

// --- Rudrasena Provider ---
final adminRudrasenaProvider = FutureProvider.family.autoDispose<List<AdminRudrasenaMember>, String>((ref, search) async {
  final apiClient = ref.watch(apiClientProvider);
  final uri = search.trim().isEmpty 
      ? '/admin/rudrasena' 
      : '/admin/rudrasena?search=${Uri.encodeComponent(search.trim())}';

  final response = await apiClient.get(uri);
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminRudrasenaMember.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load Rudrasena roster.');
});

// --- Local GP Gateways Provider ---
final adminLocalGatewaysProvider = FutureProvider.autoDispose<List<AdminLocalGatewayGroup>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final response = await apiClient.get('/admin/local-gateways');
  final responseData = response.data;

  if (responseData is Map<String, dynamic> && responseData['success'] == true) {
    final list = responseData['data'] as List? ?? [];
    return list.map((item) => AdminLocalGatewayGroup.fromJson(item as Map<String, dynamic>)).toList();
  }
  throw Exception(responseData['message'] ?? 'Failed to load Local Gateways roster.');
});
