import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';
import '../../core/theme/app_theme.dart';

class LoginScreen extends ConsumerStatefulWidget {
  final String initialType;

  const LoginScreen({super.key, this.initialType = 'volunteer'});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // Volunteer form controllers
  final _volIdController = TextEditingController();
  final _volPasswordController = TextEditingController();

  // Member form controllers
  final _memberPhoneController = TextEditingController();
  final _memberOtpController = TextEditingController();

  String? _memberChallengeId;
  bool _otpSent = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: 2,
      vsync: this,
      initialIndex: widget.initialType == 'member' ? 1 : 0,
    );
  }

  @override
  void dispose() {
    _tabController.dispose();
    _volIdController.dispose();
    _volPasswordController.dispose();
    _memberPhoneController.dispose();
    _memberOtpController.dispose();
    super.dispose();
  }

  Future<void> _handleVolunteerLogin() async {
    final loginId = _volIdController.text.trim();
    final password = _volPasswordController.text.trim();

    if (loginId.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter Volunteer ID and Password.')),
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
        SnackBar(content: Text(error), backgroundColor: Colors.red),
      );
    }
  }

  Future<void> _handleMemberSendOtp() async {
    final phone = _memberPhoneController.text.trim();

    if (phone.length != 10 || !RegExp(r'^[0-9]+$').hasMatch(phone)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter a valid 10-digit mobile number.')),
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
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('OTP sent. Please enter the 6-digit code.')),
      );
    } else if (mounted) {
      final error = ref.read(authNotifierProvider).errorMessage ?? 'Failed to send OTP';
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error), backgroundColor: Colors.red),
      );
    }
  }

  Future<void> _handleMemberVerifyOtp() async {
    final phone = _memberPhoneController.text.trim();
    final otp = _memberOtpController.text.trim();

    if (_memberChallengeId == null || otp.length != 6) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter the 6-digit OTP code.')),
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
        SnackBar(content: Text(error), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authNotifierProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Login Portal'),
        bottom: TabBar(
          controller: _tabController,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          indicatorColor: Colors.white,
          tabs: const [
            Tab(text: 'VOLUNTEER'),
            Tab(text: 'MEMBER (OTP)'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Volunteer Login Tab
          SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 16),
                const Text(
                  'Volunteer Sign In',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Approved volunteers can log in with their 6-digit ID and password.',
                  style: TextStyle(color: AppTheme.textSecondary),
                ),
                const SizedBox(height: 24),
                TextField(
                  controller: _volIdController,
                  keyboardType: TextInputType.text,
                  decoration: const InputDecoration(
                    labelText: '6-digit Volunteer ID',
                    prefixIcon: Icon(Icons.badge),
                  ),
                ),
                const SizedBox(height: 16),
                TextField(
                  controller: _volPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'Password',
                    prefixIcon: Icon(Icons.lock),
                  ),
                ),
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: authState.isLoading ? null : _handleVolunteerLogin,
                  child: authState.isLoading
                      ? const SizedBox(
                          height: 20,
                          width: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('LOGIN AS VOLUNTEER'),
                ),
              ],
            ),
          ),

          // Member Login Tab
          SingleChildScrollView(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const SizedBox(height: 16),
                const Text(
                  'Member OTP Login',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Enter your 10-digit registered mobile number to receive a verification OTP.',
                  style: TextStyle(color: AppTheme.textSecondary),
                ),
                const SizedBox(height: 24),
                TextField(
                  controller: _memberPhoneController,
                  keyboardType: TextInputType.phone,
                  maxLength: 10,
                  enabled: !_otpSent,
                  decoration: const InputDecoration(
                    labelText: '10-digit Mobile Number',
                    prefixIcon: Icon(Icons.phone),
                    prefixText: '+91 ',
                  ),
                ),
                if (_otpSent) ...[
                  const SizedBox(height: 16),
                  TextField(
                    controller: _memberOtpController,
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    decoration: const InputDecoration(
                      labelText: '6-digit OTP Code',
                      prefixIcon: Icon(Icons.security),
                    ),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: authState.isLoading ? null : _handleMemberVerifyOtp,
                    child: authState.isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('VERIFY & LOGIN'),
                  ),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _otpSent = false;
                        _memberChallengeId = null;
                        _memberOtpController.clear();
                      });
                    },
                    child: const Text('Use a different mobile number'),
                  ),
                ] else ...[
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: authState.isLoading ? null : _handleMemberSendOtp,
                    child: authState.isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                          )
                        : const Text('SEND OTP'),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
