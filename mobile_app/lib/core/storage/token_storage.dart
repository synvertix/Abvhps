import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract class TokenStorage {
  Future<String?> getToken();
  Future<void> saveToken(String token);
  Future<void> clearToken();
  Future<String?> getAccountType();
  Future<void> saveAccountType(String accountType);
}

class SecureTokenStorage implements TokenStorage {
  static const _tokenKey = 'abvhps_auth_token';
  static const _accountTypeKey = 'abvhps_account_type';

  final FlutterSecureStorage _storage;

  SecureTokenStorage({FlutterSecureStorage? storage})
      : _storage = storage ?? const FlutterSecureStorage();

  @override
  Future<String?> getToken() async {
    return await _storage.read(key: _tokenKey);
  }

  @override
  Future<void> saveToken(String token) async {
    await _storage.write(key: _tokenKey, value: token);
  }

  @override
  Future<void> clearToken() async {
    await _storage.delete(key: _tokenKey);
    await _storage.delete(key: _accountTypeKey);
  }

  @override
  Future<String?> getAccountType() async {
    return await _storage.read(key: _accountTypeKey);
  }

  @override
  Future<void> saveAccountType(String accountType) async {
    await _storage.write(key: _accountTypeKey, value: accountType);
  }
}
