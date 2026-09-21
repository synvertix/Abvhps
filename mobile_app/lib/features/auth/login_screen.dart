import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';
import '../../core/theme/app_theme.dart';

class LoginScreen extends ConsumerStatefulWidget {
  final String initialType;

  const LoginScreen({super.key, this.initialType = 'admin'});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // Admin form controllers
  final _adminEmailController = TextEditingController();
  final _adminPasswordController = TextEditingController();
  bool _obscureAdminPassword = true;

  // Volunteer form controllers
  final _volIdController = TextEditingController();
  final _volPasswordController = TextEditingController();
  bool _obscureVolPassword = true;

  // Member form controllers
  final _memberPhoneController = TextEditingController();
  final _memberOtpController = TextEditingController();

  String? _memberChallengeId;
  bool _otpSent = false;

  // Resend OTP countdown
  Timer? _resendTimer;
  int _resendCountdown = 60;
  bool _canResendOtp = false;

  @override
  void initState() {
    super.initState();
    int initialIndex = 0;
    if (widget.initialType == 'volunteer') {
      initialIndex = 1;
    } else if (widget.initialType == 'member') {
      initialIndex = 2;
    }

    _tabController = TabController(
      length: 3,
      vsync: this,
      initialIndex: initialIndex,
    );
  }

  @override
  void dispose() {
    _resendTimer?.cancel();
    _tabController.dispose();
    _adminEmailController.dispose();
    _adminPasswordController.dispose();
    _volIdController.dispose();
    _volPasswordController.dispose();
    _memberPhoneController.dispose();
    _memberOtpController.dispose();
    super.dispose();
  }

  void _startResendTimer() {
    _resendTimer?.cancel();
    setState(() {
      _resendCountdown = 60;
      _canResendOtp = false;
    });
    _resendTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_resendCountdown > 1) {
        setState(() {
          _resendCountdown--;
        });
      } else {
        timer.cancel();
        setState(() {
          _canResendOtp = true;
          _resendCountdown = 0;
        });
      }
    });
  }

  Future<void> _handleAdminLogin() async {
    FocusScope.of(context).unfocus();
    final email = _adminEmailController.text.trim();
    final password = _adminPasswordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter Administrative Email and Password.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final success = await ref.read(authNotifierProvider.notifier).loginAdmin(
          email: email,
          password: password,
          deviceName: 'ABVHPS Mobile App',
        );

    if (!success && mounted) {
      final error = ref.read(authNotifierProvider).errorMessage ?? 'Login failed';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleVolunteerLogin() async {
    FocusScope.of(context).unfocus();
    final loginId = _volIdController.text.trim();
    final password = _volPasswordController.text.trim();

    if (loginId.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter Volunteer ID and Password.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final success = await ref.read(authNotifierProvider.notifier).loginVolunteer(
          loginId: loginId,
          password: password,
          deviceName: 'ABVHPS Mobile App',
        );

    if (!success && mounted) {
      final error = ref.read(authNotifierProvider).errorMessage ?? 'Login failed';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleMemberSendOtp() async {
    FocusScope.of(context).unfocus();
    final phone = _memberPhoneController.text.trim();

    if (phone.length != 10 || !RegExp(r'^[0-9]+$').hasMatch(phone)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter a valid 10-digit mobile number.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final challengeId = await ref.read(authNotifierProvider.notifier).sendMemberOtp(
          phone: phone,
        );

    if (challengeId != null && mounted) {
      setState(() {
        _memberChallengeId = challengeId;
        _otpSent = true;
      });
      _startResendTimer();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('OTP sent. Please enter the 6-digit code.'),
          backgroundColor: Colors.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } else if (mounted) {
      final error = ref.read(authNotifierProvider).errorMessage ?? 'Failed to send OTP';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _handleMemberVerifyOtp() async {
    FocusScope.of(context).unfocus();
    final phone = _memberPhoneController.text.trim();
    final otp = _memberOtpController.text.trim();

    if (_memberChallengeId == null || otp.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter the 6-digit OTP code.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final success = await ref.read(authNotifierProvider.notifier).verifyMemberOtp(
          phone: phone,
          challengeId: _memberChallengeId!,
          otp: otp,
          deviceName: 'ABVHPS Mobile App',
        );

    if (!success && mounted) {
      final error = ref.read(authNotifierProvider).errorMessage ?? 'Verification failed';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'Sign in to ABVHPS',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.5,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelStyle: const TextStyle(fontWeight: FontWeight.w800, fontSize: 12, letterSpacing: 0.4),
          tabs: const [
            Tab(
              icon: Icon(Icons.admin_panel_settings_outlined, size: 18),
              text: 'ADMIN',
            ),
            Tab(
              icon: Icon(Icons.badge_outlined, size: 18),
              text: 'VOLUNTEER',
            ),
            Tab(
              icon: Icon(Icons.phone_android_outlined, size: 18),
              text: 'MEMBER (OTP)',
            ),
          ],
        ),
      ),
      body: SafeArea(
        child: TabBarView(
          controller: _tabController,
          children: [
            // Tab 0: Admin Login
            _buildAdminTab(authState),

            // Tab 1: Volunteer Login
            _buildVolunteerTab(authState),

            // Tab 2: Member Login
            _buildMemberTab(authState),
          ],
        ),
      ),
    );
  }

  Widget _buildBrandingHeader() {
    return Column(
      children: [
        Container(
          width: 56,
          height: 56,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Colors.white,
            border: Border.all(color: AppTheme.primaryOrange, width: 2),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.06),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          padding: const EdgeInsets.all(4),
          child: ClipOval(
            child: Image.asset(
              'assets/branding/logo_abvhps.png',
              fit: BoxFit.contain,
              errorBuilder: (context, error, stackTrace) => const Icon(
                Icons.account_balance,
                color: AppTheme.primaryOrange,
                size: 26,
              ),
            ),
          ),
        ),
        const SizedBox(height: 10),
        const Text(
          'AKHANDA BHARATA',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AppTheme.primaryOrange,
            fontSize: 16,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.6,
          ),
        ),
        const Text(
          'VISWA HINDU PARIRAKSHANA SAMITI',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Color(0xFF64748B),
            fontSize: 10,
            fontWeight: FontWeight.w700,
            letterSpacing: 0.8,
          ),
        ),
      ],
    );
  }

  Widget _buildAdminTab(dynamic authState) {
    return SingleChildScrollView(
      physics: const ClampingScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 8),
              _buildBrandingHeader(),
              const SizedBox(height: 20),

              // Admin Form Card
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Card Banner
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(15),
                          topRight: Radius.circular(15),
                        ),
                      ),
                      child: const Column(
                        children: [
                          Text(
                            'ADMIN PORTAL',
                            style: TextStyle(
                              color: Color(0xFF94A3B8),
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 1.2,
                            ),
                          ),
                          SizedBox(height: 2),
                          Text(
                            'Admin Sign In',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Card Content
                    Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const Text(
                            'Authorized administrators can log in with their official email and password.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: AppTheme.textSecondary,
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Email Field
                          Semantics(
                            label: 'Administrative Email input field',
                            child: TextField(
                              controller: _adminEmailController,
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                              autofillHints: const [AutofillHints.email],
                              decoration: InputDecoration(
                                labelText: 'Administrative Email',
                                hintText: 'admin@abvhps.org',
                                prefixIcon: const Icon(Icons.email_outlined, color: Color(0xFF0F172A)),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Password Field
                          Semantics(
                            label: 'Security Password input field',
                            child: TextField(
                              controller: _adminPasswordController,
                              obscureText: _obscureAdminPassword,
                              textInputAction: TextInputAction.done,
                              autofillHints: const [AutofillHints.password],
                              onSubmitted: (_) => _handleAdminLogin(),
                              decoration: InputDecoration(
                                labelText: 'Security Password',
                                hintText: '••••••••',
                                prefixIcon: const Icon(Icons.lock_outline, color: Color(0xFF0F172A)),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscureAdminPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                    color: const Color(0xFF64748B),
                                  ),
                                  tooltip: _obscureAdminPassword ? 'Show password' : 'Hide password',
                                  onPressed: () {
                                    setState(() {
                                      _obscureAdminPassword = !_obscureAdminPassword;
                                    });
                                  },
                                ),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 22),

                          // Submit Button
                          Semantics(
                            label: 'Submit administrator login',
                            button: true,
                            child: SizedBox(
                              height: 48,
                              child: ElevatedButton(
                                onPressed: authState.isLoading ? null : _handleAdminLogin,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF0F172A),
                                  foregroundColor: Colors.white,
                                  elevation: 1,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                                child: authState.isLoading
                                    ? const SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(
                                          color: Colors.white,
                                          strokeWidth: 2.5,
                                        ),
                                      )
                                    : const Text(
                                        'LOGIN AS ADMIN',
                                        style: TextStyle(
                                          fontWeight: FontWeight.w800,
                                          fontSize: 13,
                                          letterSpacing: 0.8,
                                        ),
                                      ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),
              const Text(
                'Akhanda Bharata Viswa Hindu Parirakshana Samiti',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildVolunteerTab(dynamic authState) {
    return SingleChildScrollView(
      physics: const ClampingScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 8),
              _buildBrandingHeader(),
              const SizedBox(height: 20),

              // Volunteer Form Card
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Card Banner
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFFC2410C), Color(0xFFEA580C)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(15),
                          topRight: Radius.circular(15),
                        ),
                      ),
                      child: const Column(
                        children: [
                          Text(
                            'VOLUNTEER PORTAL',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 1.2,
                            ),
                          ),
                          SizedBox(height: 2),
                          Text(
                            'Volunteer Sign In',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Card Content
                    Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          const Text(
                            'Approved volunteers can log in with their 6-digit ID and password.',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: AppTheme.textSecondary,
                              fontSize: 12,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Volunteer ID Field
                          Semantics(
                            label: '6-digit Volunteer ID input field',
                            child: TextField(
                              controller: _volIdController,
                              keyboardType: TextInputType.text,
                              textInputAction: TextInputAction.next,
                              autofillHints: const [AutofillHints.username],
                              decoration: InputDecoration(
                                labelText: '6-digit Volunteer ID',
                                hintText: 'e.g. 100001',
                                prefixIcon: const Icon(Icons.badge_outlined, color: AppTheme.primaryOrange),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Password Field
                          Semantics(
                            label: 'Volunteer Password input field',
                            child: TextField(
                              controller: _volPasswordController,
                              obscureText: _obscureVolPassword,
                              textInputAction: TextInputAction.done,
                              autofillHints: const [AutofillHints.password],
                              onSubmitted: (_) => _handleVolunteerLogin(),
                              decoration: InputDecoration(
                                labelText: 'Password',
                                hintText: '••••••••',
                                prefixIcon: const Icon(Icons.lock_outline, color: AppTheme.primaryOrange),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _obscureVolPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined,
                                    color: const Color(0xFF64748B),
                                  ),
                                  tooltip: _obscureVolPassword ? 'Show password' : 'Hide password',
                                  onPressed: () {
                                    setState(() {
                                      _obscureVolPassword = !_obscureVolPassword;
                                    });
                                  },
                                ),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 22),

                          // Submit Button
                          Semantics(
                            label: 'Submit volunteer login',
                            button: true,
                            child: SizedBox(
                              height: 48,
                              child: ElevatedButton(
                                onPressed: authState.isLoading ? null : _handleVolunteerLogin,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.primaryOrange,
                                  foregroundColor: Colors.white,
                                  elevation: 1,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                                child: authState.isLoading
                                    ? const SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(
                                          color: Colors.white,
                                          strokeWidth: 2.5,
                                        ),
                                      )
                                    : const Text(
                                        'LOGIN AS VOLUNTEER',
                                        style: TextStyle(
                                          fontWeight: FontWeight.w800,
                                          fontSize: 13,
                                          letterSpacing: 0.8,
                                        ),
                                      ),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),
              const Text(
                'Akhanda Bharata Viswa Hindu Parirakshana Samiti',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMemberTab(dynamic authState) {
    return SingleChildScrollView(
      physics: const ClampingScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 16.0),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 8),
              _buildBrandingHeader(),
              const SizedBox(height: 20),

              // Member Form Card
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Card Banner
                    Container(
                      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 16),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.only(
                          topLeft: Radius.circular(15),
                          topRight: Radius.circular(15),
                        ),
                      ),
                      child: const Column(
                        children: [
                          Text(
                            'MEMBER PORTAL',
                            style: TextStyle(
                              color: Color(0xFF94A3B8),
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                              letterSpacing: 1.2,
                            ),
                          ),
                          SizedBox(height: 2),
                          Text(
                            'Member OTP Login',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.4,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Card Content
                    Padding(
                      padding: const EdgeInsets.all(20.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          if (!_otpSent) ...[
                            const Text(
                              'Enter your 10-digit registered mobile number to receive a verification OTP.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                color: AppTheme.textSecondary,
                                fontSize: 12,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Mobile Number Field
                            Semantics(
                              label: '10-digit registered mobile number input field',
                              child: TextField(
                                controller: _memberPhoneController,
                                keyboardType: TextInputType.phone,
                                textInputAction: TextInputAction.done,
                                maxLength: 10,
                                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                                autofillHints: const [AutofillHints.telephoneNumber],
                                onSubmitted: (_) => _handleMemberSendOtp(),
                                decoration: InputDecoration(
                                  labelText: '10-digit Mobile Number',
                                  hintText: '9876543210',
                                  counterText: '',
                                  prefixIcon: const Icon(Icons.phone_android_outlined, color: Color(0xFF0F172A)),
                                  prefixText: '+91 ',
                                  prefixStyle: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF0F172A),
                                  ),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Send OTP Button
                            Semantics(
                              label: 'Send OTP verification code',
                              button: true,
                              child: SizedBox(
                                height: 48,
                                child: ElevatedButton(
                                  onPressed: authState.isLoading ? null : _handleMemberSendOtp,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF0F172A),
                                    foregroundColor: Colors.white,
                                    elevation: 1,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                  ),
                                  child: authState.isLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2.5,
                                          ),
                                        )
                                      : const Text(
                                          'SEND OTP',
                                          style: TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 13,
                                            letterSpacing: 0.8,
                                          ),
                                        ),
                                ),
                              ),
                            ),
                          ] else ...[
                            // Masked Destination Notification
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                              decoration: BoxDecoration(
                                color: const Color(0xFFF0FDF4),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(color: const Color(0xFFBBF7D0)),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.check_circle_outline, color: Color(0xFF16A34A), size: 20),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'OTP sent to +91 ******${_memberPhoneController.text.length >= 4 ? _memberPhoneController.text.substring(_memberPhoneController.text.length - 4) : ""}',
                                      style: const TextStyle(
                                        color: Color(0xFF166534),
                                        fontSize: 12,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 18),

                            // OTP Input Field
                            Semantics(
                              label: '6-digit OTP code input field',
                              child: TextField(
                                controller: _memberOtpController,
                                keyboardType: TextInputType.number,
                                textInputAction: TextInputAction.done,
                                maxLength: 6,
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 8,
                                  color: Color(0xFF0F172A),
                                ),
                                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                                autofillHints: const [AutofillHints.oneTimeCode],
                                onSubmitted: (_) => _handleMemberVerifyOtp(),
                                decoration: InputDecoration(
                                  labelText: '6-digit OTP Code',
                                  hintText: '••••••',
                                  counterText: '',
                                  prefixIcon: const Icon(Icons.security_outlined, color: Color(0xFF0F172A)),
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 20),

                            // Verify & Login Button
                            Semantics(
                              label: 'Verify OTP and login',
                              button: true,
                              child: SizedBox(
                                height: 48,
                                child: ElevatedButton(
                                  onPressed: authState.isLoading ? null : _handleMemberVerifyOtp,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF0F172A),
                                    foregroundColor: Colors.white,
                                    elevation: 1,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10),
                                    ),
                                  ),
                                  child: authState.isLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2.5,
                                          ),
                                        )
                                      : const Text(
                                          'VERIFY & LOGIN',
                                          style: TextStyle(
                                            fontWeight: FontWeight.w800,
                                            fontSize: 13,
                                            letterSpacing: 0.8,
                                          ),
                                        ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),

                            // Resend OTP Action
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                if (!_canResendOtp)
                                  Text(
                                    'Resend OTP in ${_resendCountdown}s',
                                    style: const TextStyle(
                                      color: Color(0xFF64748B),
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  )
                                else
                                  TextButton.icon(
                                    onPressed: authState.isLoading ? null : _handleMemberSendOtp,
                                    icon: const Icon(Icons.refresh, size: 16),
                                    label: const Text(
                                      'Resend OTP',
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.primaryOrange,
                                      ),
                                    ),
                                  ),
                              ],
                            ),

                            // Change Number Action
                            Center(
                              child: TextButton(
                                onPressed: () {
                                  _resendTimer?.cancel();
                                  setState(() {
                                    _otpSent = false;
                                    _memberChallengeId = null;
                                    _memberOtpController.clear();
                                  });
                                },
                                child: const Text(
                                  'Use a different mobile number',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF475569),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),
              const Text(
                'Akhanda Bharata Viswa Hindu Parirakshana Samiti',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Color(0xFF94A3B8),
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        ),
      ),
    );
  }
}
