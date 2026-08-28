import 'package:dio/dio.dart';
import '../config/app_config.dart';
import '../storage/token_storage.dart';
import 'api_exception.dart';

class ApiClient {
  late final Dio _dio;
  final TokenStorage _tokenStorage;

  ApiClient({
    String? baseUrl,
    TokenStorage? tokenStorage,
    Dio? customDio,
  }) : _tokenStorage = tokenStorage ?? SecureTokenStorage() {
    _dio = customDio ??
        Dio(
          BaseOptions(
            baseUrl: baseUrl ?? AppConfig.apiBaseUrl,
            connectTimeout: const Duration(seconds: 15),
            receiveTimeout: const Duration(seconds: 15),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          ),
        );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await _tokenStorage.getToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) {
          final response = e.response;
          final statusCode = response?.statusCode;
          String errorMessage = 'A network error occurred. Please try again.';
          bool mustChangePassword = false;

          if (response?.data is Map<String, dynamic>) {
            final data = response!.data as Map<String, dynamic>;
            if (data.containsKey('message') && data['message'] != null) {
              errorMessage = data['message'].toString();
            }
            if (data['must_change_password'] == true) {
              mustChangePassword = true;
            }
          }

          final apiException = ApiException(
            message: errorMessage,
            statusCode: statusCode,
            data: response?.data,
            mustChangePassword: mustChangePassword,
          );

          return handler.reject(
            DioException(
              requestOptions: e.requestOptions,
              response: e.response,
              type: e.type,
              error: apiException,
            ),
          );
        },
      ),
    );
  }

  Dio get dio => _dio;

  Future<Response> get(String path, {Map<String, dynamic>? queryParameters}) async {
    try {
      return await _dio.get(path, queryParameters: queryParameters);
    } on DioException catch (e) {
      if (e.error is ApiException) {
        throw e.error as ApiException;
      }
      throw ApiException(message: e.message ?? 'Unknown request error');
    }
  }

  Future<Response> post(String path, {dynamic data}) async {
    try {
      return await _dio.post(path, data: data);
    } on DioException catch (e) {
      if (e.error is ApiException) {
        throw e.error as ApiException;
      }
      throw ApiException(message: e.message ?? 'Unknown request error');
    }
  }
}
