class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic data;
  final bool mustChangePassword;

  ApiException({
    required this.message,
    this.statusCode,
    this.data,
    this.mustChangePassword = false,
  });

  @override
  String toString() => 'ApiException: $message (Status: $statusCode)';
}
