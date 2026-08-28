class AuthState {
  final bool isAuthenticated;
  final bool isLoading;
  final String? accountType; // 'volunteer' or 'member'
  final bool mustChangePassword;
  final Map<String, dynamic>? profile;
  final Map<String, dynamic>? capabilities;
  final String? errorMessage;

  const AuthState({
    this.isAuthenticated = false,
    this.isLoading = false,
    this.accountType,
    this.mustChangePassword = false,
    this.profile,
    this.capabilities,
    this.errorMessage,
  });

  AuthState copyWith({
    bool? isAuthenticated,
    bool? isLoading,
    String? accountType,
    bool? mustChangePassword,
    Map<String, dynamic>? profile,
    Map<String, dynamic>? capabilities,
    String? errorMessage,
  }) {
    return AuthState(
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      isLoading: isLoading ?? this.isLoading,
      accountType: accountType ?? this.accountType,
      mustChangePassword: mustChangePassword ?? this.mustChangePassword,
      profile: profile ?? this.profile,
      capabilities: capabilities ?? this.capabilities,
      errorMessage: errorMessage,
    );
  }
}
