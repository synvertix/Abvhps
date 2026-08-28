import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/verification_model.dart';

class VerificationScreen extends ConsumerStatefulWidget {
  final String? initialType;
  final String? initialId;

  const VerificationScreen({
    super.key,
    this.initialType,
    this.initialId,
  });

  @override
  ConsumerState<VerificationScreen> createState() => _VerificationScreenState();
}

class _VerificationScreenState extends ConsumerState<VerificationScreen> {
  final _formKey = GlobalKey<FormState>();
  late String _selectedType;
  late TextEditingController _idController;

  bool _isVerifying = false;
  PublicVerificationResult? _result;
  String? _errorMessage;

  final List<Map<String, String>> _entityTypes = [
    {'value': 'membership', 'label': 'ABVHPS Life Member (12-digit)'},
    {'value': 'volunteer', 'label': 'Authorized Volunteer (VOL-ID)'},
    {'value': 'rudrasena', 'label': 'Rudra Sena Member (RS-ID)'},
    {'value': 'exam', 'label': 'Exam Hall Ticket (11-digit)'},
    {'value': 'organic-farmers', 'label': 'Organic Farmers Group'},
    {'value': 'kala-brundam', 'label': 'Kala Brundam Cultural Wing'},
    {'value': 'grama-seva-dal', 'label': 'Grama Seva Dal Village Wing'},
  ];

  @override
  void initState() {
    super.initState();
    _selectedType = widget.initialType ?? 'membership';
    _idController = TextEditingController(text: widget.initialId ?? '');

    if (widget.initialId != null && widget.initialId!.isNotEmpty) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _verifyRecord();
      });
    }
  }

  @override
  void dispose() {
    _idController.dispose();
    super.dispose();
  }

  Future<void> _verifyRecord() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isVerifying = true;
      _errorMessage = null;
      _result = null;
    });

    final repo = ref.read(publicApiRepositoryProvider);
    try {
      final res = await repo.verifyEntity(_selectedType, _idController.text.trim());
      setState(() {
        _isVerifying = false;
        _result = res;
      });
    } catch (e) {
      setState(() {
        _isVerifying = false;
        _errorMessage = e.toString().replaceAll('ApiException:', '').replaceAll('Exception:', '').trim();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return PublicScaffold(
      title: 'Public Master ID & QR Verification',
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Form Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: Colors.grey.shade200),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
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
                            color: AppTheme.lightOrange,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: const Icon(Icons.qr_code_scanner, color: AppTheme.primaryOrange, size: 22),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'LIVE DIRECTORY VERIFIER',
                                style: TextStyle(
                                  color: AppTheme.darkNavy,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.6,
                                ),
                              ),
                              Text(
                                'Verify members, leaders, wings & hall tickets',
                                style: TextStyle(color: Colors.grey, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Entity Type Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: _selectedType,
                      decoration: InputDecoration(
                        labelText: 'Select Verification Type',
                        filled: true,
                        fillColor: Colors.grey.shade50,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      items: _entityTypes.map((t) {
                        return DropdownMenuItem<String>(
                          value: t['value'],
                          child: Text(t['label']!, style: const TextStyle(fontSize: 12.5)),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) setState(() => _selectedType = val);
                      },
                    ),
                    const SizedBox(height: 14),

                    // ID Number input
                    TextFormField(
                      controller: _idController,
                      decoration: InputDecoration(
                        labelText: 'Enter Official Registration / ID Number',
                        hintText: 'e.g. 100000000001, VOL-1001, RS0001...',
                        filled: true,
                        fillColor: Colors.grey.shade50,
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      ),
                      validator: (val) => (val == null || val.trim().isEmpty) ? 'Please provide an ID number' : null,
                    ),
                    const SizedBox(height: 16),

                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _isVerifying ? null : _verifyRecord,
                        icon: _isVerifying
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.verified_user, size: 18),
                        label: Text(_isVerifying ? 'VERIFYING WITH PORTAL...' : 'VERIFY CREDENTIAL'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primaryOrange,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.red.shade200),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.cancel, color: Colors.red, size: 22),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: TextStyle(color: Colors.red.shade900, fontSize: 12.5, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            if (_result != null) ...[
              const SizedBox(height: 18),
              _buildVerificationResultCard(_result!),
            ],

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildVerificationResultCard(PublicVerificationResult res) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: res.isValid ? const Color(0xFF10B981) : Colors.red, width: 1.5),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: res.isValid ? const Color(0xFFECFDF5) : Colors.red.shade50,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  children: [
                    Icon(
                      res.isValid ? Icons.verified : Icons.warning_amber,
                      color: res.isValid ? const Color(0xFF059669) : Colors.red,
                      size: 16,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      res.status ?? (res.isValid ? 'OFFICIALLY VERIFIED' : 'RECORD NOT FOUND'),
                      style: TextStyle(
                        color: res.isValid ? const Color(0xFF059669) : Colors.red,
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
              if (res.entityType != null)
                Text(
                  res.entityType!,
                  style: const TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600),
                ),
            ],
          ),
          const SizedBox(height: 14),

          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (res.photoUrl != null)
                Container(
                  width: 64,
                  height: 64,
                  margin: const EdgeInsets.only(right: 14),
                  decoration: BoxDecoration(
                    color: AppTheme.lightOrange,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppTheme.primaryOrange, width: 2),
                  ),
                  child: ClipOval(
                    child: AppNetworkImage(
                      imageUrl: res.photoUrl,
                      fallbackAsset: 'assets/branding/logo_abvhps.png',
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (res.name != null)
                      Text(
                        res.name!,
                        style: const TextStyle(color: AppTheme.darkNavy, fontSize: 16, fontWeight: FontWeight.w900),
                      ),
                    if (res.cadre != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        res.cadre!,
                        style: const TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.w800, fontSize: 12.5),
                      ),
                    ],
                    if (res.officialId != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        '${res.officialIdLabel ?? "Official ID"}: ${res.officialId}',
                        style: const TextStyle(color: Colors.black87, fontWeight: FontWeight.w700, fontSize: 12),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),

          if (res.location != null || res.extraDetail != null) ...[
            const Divider(color: Colors.black12, height: 20),
            if (res.location != null)
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.location_on, size: 15, color: Colors.grey),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(res.location!, style: const TextStyle(color: Colors.black87, fontSize: 12)),
                  ),
                ],
              ),
            if (res.extraDetail != null) ...[
              const SizedBox(height: 6),
              Text(res.extraDetail!, style: const TextStyle(color: Colors.black54, fontSize: 11.5)),
            ],
          ],

          if (res.verifiedSince != null) ...[
            const SizedBox(height: 10),
            Align(
              alignment: Alignment.centerRight,
              child: Text(
                'Registration Date: ${res.verifiedSince}',
                style: const TextStyle(color: Colors.grey, fontSize: 10.5),
              ),
            ),
          ],
        ],
      ),
    );
  }
}
