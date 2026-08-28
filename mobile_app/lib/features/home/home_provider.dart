import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';

final homeDataProvider = FutureProvider.autoDispose<Map<String, dynamic>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final response = await apiClient.get('/home');
  if (response.data is Map<String, dynamic> && response.data['data'] != null) {
    return response.data['data'] as Map<String, dynamic>;
  }
  return <String, dynamic>{};
});
