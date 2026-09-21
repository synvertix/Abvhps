import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';
import 'models/admin_dashboard_model.dart';

final adminDashboardDataProvider = FutureProvider.autoDispose<AdminDashboardData>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final response = await apiClient.get('/admin/dashboard');

  final responseData = response.data;
  if (responseData is Map<String, dynamic> &&
      responseData['success'] == true &&
      responseData['data'] != null) {
    return AdminDashboardData.fromJson(responseData['data'] as Map<String, dynamic>);
  }

  throw Exception((responseData is Map<String, dynamic> ? responseData['message'] : null) ??
      'Failed to load administrative metrics.');
});
