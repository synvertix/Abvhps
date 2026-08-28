import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../api/api_client.dart';
import '../api/api_exception.dart';
import '../storage/token_storage.dart';
import 'auth_state.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) {
  return SecureTokenStorage();
});

final apiClientProvider = Provider<ApiClient>((ref) {
  final storage = ref.watch(tokenStorageProvider);
  return ApiClient(tokenStorage: storage);
});

final authNotifierProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  final tokenStorage = ref.watch(tokenStorageProvider);
  return AuthNotifier(apiClient: apiClient, tokenStorage: tokenStorage);
});

class AuthNotifier extends StateNotifier<AuthState> {
  final ApiClient apiClient;
  final TokenStorage tokenStorage;

  AuthNotifier({
    required this.apiClient,
    required this.tokenStorage,
  }) : super(const AuthState(isLoading: true)) {
    checkInitialAuth();
  }

  Future<void> checkInitialAuth() async {
    state = state.copyWith(isLoading: true);
    try {
      final token = await tokenStorage.getToken();
      if (token == null || token.isEmpty) {
        state = const AuthState(isAuthenticated: false, isLoading: false);
        return;
      }

      final response = await apiClient.get('/me');
      if (response.statusCode == 200 && response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        state = AuthState(
          isAuthenticated: true,
          isLoading: false,
          accountType: data['account_type'] as String?,
          mustChangePassword: data['must_change_password'] == true,
          profile: data['profile'] as Map<String, dynamic>?,
          capabilities: data['capabilities'] as Map<String, dynamic>?,
        );
      } else {
        await tokenStorage.clearToken();
        state = const AuthState(isAuthenticated: false, isLoading: false);
      }
    } catch (_) {
      await tokenStorage.clearToken();
      state = const AuthState(isAuthenticated: false, isLoading: false);
    }
  }

  Future<bool> loginVolunteer({
    required String loginId,
    required String password,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await apiClient.post(
        '/auth/volunteer/login',
        data: {
          'login_id': loginId,
          'password': password,
          'device_name': deviceName,
        },
      );

      if (response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        final token = data['token'] as String;
        final mustChangePassword = data['must_change_password'] == true;

        await tokenStorage.saveToken(token);
        await tokenStorage.saveAccountType('volunteer');

        state = AuthState(
          isAuthenticated: true,
          isLoading: false,
          accountType: 'volunteer',
          mustChangePassword: mustChangePassword,
          profile: data['profile'] as Map<String, dynamic>?,
        );
        return true;
      }
      state = state.copyWith(
        isLoading: false,
        errorMessage: response.data['message']?.toString() ?? 'Login failed',
      );
      return false;
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
      return false;
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
      return false;
    }
  }

  Future<String?> sendMemberOtp({required String phone}) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await apiClient.post(
        '/auth/member/send-otp',
        data: {'phone': phone},
      );

      state = state.copyWith(isLoading: false);
      if (response.data['success'] == true) {
        return response.data['challenge_id'] as String?;
      }
      state = state.copyWith(
        errorMessage: response.data['message']?.toString() ?? 'Failed to send OTP',
      );
      return null;
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
      return null;
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
      return null;
    }
  }

  Future<bool> verifyMemberOtp({
    required String phone,
    required String challengeId,
    required String otp,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await apiClient.post(
        '/auth/member/verify-otp',
        data: {
          'phone': phone,
          'challenge_id': challengeId,
          'otp': otp,
          'device_name': deviceName,
        },
      );

      if (response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        final token = data['token'] as String;

        await tokenStorage.saveToken(token);
        await tokenStorage.saveAccountType('member');

        state = AuthState(
          isAuthenticated: true,
          isLoading: false,
          accountType: 'member',
          mustChangePassword: false,
          profile: data['profile'] as Map<String, dynamic>?,
        );
        return true;
      }
      state = state.copyWith(
        isLoading: false,
        errorMessage: response.data['message']?.toString() ?? 'Verification failed',
      );
      return false;
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
      return false;
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
      return false;
    }
  }

  Future<bool> changeVolunteerPassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
    String deviceName = 'ABVHPS Mobile App',
  }) async {
    state = state.copyWith(isLoading: true, errorMessage: null);
    try {
      final response = await apiClient.post(
        '/volunteer/change-password',
        data: {
          'current_password': currentPassword,
          'new_password': newPassword,
          'new_password_confirmation': newPasswordConfirmation,
          'device_name': deviceName,
        },
      );

      if (response.data['success'] == true) {
        final data = response.data['data'] as Map<String, dynamic>;
        final token = data['token'] as String;

        await tokenStorage.saveToken(token);

        state = state.copyWith(
          isLoading: false,
          mustChangePassword: false,
          profile: data['profile'] as Map<String, dynamic>?,
        );
        return true;
      }
      state = state.copyWith(
        isLoading: false,
        errorMessage: response.data['message']?.toString() ?? 'Password change failed',
      );
      return false;
    } on ApiException catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.message);
      return false;
    } catch (e) {
      state = state.copyWith(isLoading: false, errorMessage: e.toString());
      return false;
    }
  }

  Future<void> logout() async {
    state = state.copyWith(isLoading: true);
    try {
      await apiClient.post('/auth/logout');
    } catch (_) {
      // Ignore network failures on logout
    } finally {
      await tokenStorage.clearToken();
      state = const AuthState(isAuthenticated: false, isLoading: false);
    }
  }
}
