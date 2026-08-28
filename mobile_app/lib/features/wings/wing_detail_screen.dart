import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/wing_model.dart';

final wingDetailProvider = FutureProvider.autoDispose.family<WingModel, String>((ref, slug) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getWing(slug);
});

class WingDetailScreen extends ConsumerWidget {
  final String slug;

  const WingDetailScreen({
    super.key,
    required this.slug,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final wingAsync = ref.watch(wingDetailProvider(slug));

    return PublicScaffold(
      title: 'Wing Details',
      body: wingAsync.when(
        data: (wing) => _buildDetail(context, wing),
        loading: () => const AppLoadingState(message: 'Loading subsystem details...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(wingDetailProvider(slug)),
        ),
      ),
    );
  }

  Widget _buildDetail(BuildContext context, WingModel w) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextButton.icon(
            onPressed: () {
              if (context.canPop()) {
                context.pop();
              } else {
                context.go('/wings');
              }
            },
            icon: const Icon(Icons.arrow_back, size: 18, color: AppTheme.primaryOrange),
            label: const Text(
              'Back to Wings',
              style: TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),

          // Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF0F172A), Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.primaryOrange.withValues(alpha: 0.3)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  w.name.toUpperCase(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.6,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  w.slogan,
                  style: const TextStyle(
                    color: AppTheme.primaryOrange,
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  w.tagline,
                  style: const TextStyle(color: Colors.white70, fontSize: 12.5),
                ),
              ],
            ),
          ),

          const SizedBox(height: 18),

          // Description Card
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'ABOUT THIS SUBSYSTEM',
                  style: TextStyle(color: AppTheme.primaryOrange, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                ),
                const SizedBox(height: 10),
                Text(
                  w.description,
                  style: const TextStyle(color: Colors.black87, fontSize: 13.5, height: 1.5),
                ),
                if (w.eligibilityCriteria != null) ...[
                  const Divider(color: Colors.black12, height: 24),
                  const Text(
                    'ELIGIBILITY CRITERIA',
                    style: TextStyle(color: AppTheme.darkNavy, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    w.eligibilityCriteria!,
                    style: const TextStyle(color: Colors.black54, fontSize: 12.5, height: 1.4),
                  ),
                ],
              ],
            ),
          ),

          // Key Initiatives
          if (w.keyInitiatives.isNotEmpty) ...[
            const SizedBox(height: 18),
            const Text(
              'KEY ACTIVITIES & RESPONSIBILITIES',
              style: TextStyle(color: AppTheme.darkNavy, fontSize: 13, fontWeight: FontWeight.w900, letterSpacing: 0.6),
            ),
            const SizedBox(height: 10),
            ...w.keyInitiatives.map((init) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.grey.shade200),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.check_circle, color: AppTheme.primaryOrange, size: 18),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          init,
                          style: const TextStyle(color: Colors.black87, fontSize: 12.5, height: 1.4),
                        ),
                      ),
                    ],
                  ),
                )),
          ],

          // Rudrasena Eligibility Checker Widget
          if (w.slug == 'rudrasena') ...[
            const SizedBox(height: 20),
            const RudrasenaEligibilityWidget(),
          ],

          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

class RudrasenaEligibilityWidget extends ConsumerStatefulWidget {
  const RudrasenaEligibilityWidget({super.key});

  @override
  ConsumerState<RudrasenaEligibilityWidget> createState() => _RudrasenaEligibilityWidgetState();
}

class _RudrasenaEligibilityWidgetState extends ConsumerState<RudrasenaEligibilityWidget> {
  final TextEditingController _idController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  bool _isChecking = false;
  RudrasenaEligibilityResult? _result;
  String? _errorMessage;

  @override
  void dispose() {
    _idController.dispose();
    super.dispose();
  }

  Future<void> _checkEligibility() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isChecking = true;
      _errorMessage = null;
      _result = null;
    });

    final repo = ref.read(publicApiRepositoryProvider);
    try {
      final res = await repo.verifyRudrasenaEligibility(_idController.text.trim());
      setState(() {
        _result = res;
        _isChecking = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceAll('Exception:', '').trim();
        _isChecking = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7ED),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFFDBA74), width: 1.5),
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryOrange,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.shield, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 10),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'RUDRASENA ELIGIBILITY CLEARANCE',
                        style: TextStyle(
                          color: AppTheme.darkNavy,
                          fontSize: 12.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      Text(
                        'Verify 12-Digit Membership ID (Age 24-44)',
                        style: TextStyle(color: Colors.black54, fontSize: 11),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),

            TextFormField(
              controller: _idController,
              keyboardType: TextInputType.number,
              maxLength: 12,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: InputDecoration(
                counterText: '',
                hintText: 'Enter 12-digit Membership ID',
                prefixIcon: const Icon(Icons.badge_outlined, color: AppTheme.primaryOrange),
                filled: true,
                fillColor: Colors.white,
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: BorderSide(color: Colors.grey.shade300),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(10),
                  borderSide: const BorderSide(color: AppTheme.primaryOrange, width: 2),
                ),
              ),
              validator: (val) {
                if (val == null || val.trim().isEmpty) {
                  return 'Please enter your 12-digit Membership ID';
                }
                if (val.trim().length != 12) {
                  return 'Membership ID must be exactly 12 digits';
                }
                return null;
              },
            ),
            const SizedBox(height: 12),

            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _isChecking ? null : _checkEligibility,
                icon: _isChecking
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.verified_user, size: 18),
                label: Text(_isChecking ? 'VERIFYING WITH PORTAL...' : 'CHECK RUDRASENA CLEARANCE'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryOrange,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ),

            if (_errorMessage != null) ...[
              const SizedBox(height: 12),
              Text(_errorMessage!, style: const TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
            ],

            if (_result != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: _result!.eligible ? const Color(0xFFECFDF5) : Colors.red.shade50,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: _result!.eligible ? const Color(0xFF10B981) : Colors.red.shade300),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(
                      _result!.eligible ? Icons.check_circle : Icons.cancel,
                      color: _result!.eligible ? const Color(0xFF059669) : Colors.red,
                      size: 22,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _result!.message,
                        style: TextStyle(
                          color: _result!.eligible ? const Color(0xFF065F46) : Colors.red.shade900,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
