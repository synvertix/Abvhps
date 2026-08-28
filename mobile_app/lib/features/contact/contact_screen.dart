import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/url_helper.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/contact_model.dart';

final contactInfoProvider = FutureProvider.autoDispose<ContactInfoModel>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getContact();
});

class ContactScreen extends ConsumerStatefulWidget {
  const ContactScreen({super.key});

  @override
  ConsumerState<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends ConsumerState<ContactScreen> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _subjectController = TextEditingController();
  final TextEditingController _messageController = TextEditingController();

  bool _isSubmitting = false;
  String? _successMessage;
  String? _errorMessage;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _subjectController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSubmitting = true;
      _successMessage = null;
      _errorMessage = null;
    });

    final repo = ref.read(publicApiRepositoryProvider);
    try {
      final msg = await repo.submitContact(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        phone: _phoneController.text.trim(),
        subject: _subjectController.text.trim(),
        message: _messageController.text.trim(),
      );

      setState(() {
        _isSubmitting = false;
        _successMessage = msg;
        _nameController.clear();
        _emailController.clear();
        _phoneController.clear();
        _subjectController.clear();
        _messageController.clear();
      });
    } catch (e) {
      setState(() {
        _isSubmitting = false;
        _errorMessage = e.toString().replaceAll('ApiException:', '').replaceAll('Exception:', '').trim();
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final contactAsync = ref.watch(contactInfoProvider);

    return PublicScaffold(
      title: 'Contact & Support',
      body: contactAsync.when(
        data: (info) => _buildBody(context, info),
        loading: () => const AppLoadingState(message: 'Loading contact channels...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(contactInfoProvider),
        ),
      ),
    );
  }

  Widget _buildBody(BuildContext context, ContactInfoModel info) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Channels Cards
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
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'OFFICIAL CENTRAL DESK',
                  style: TextStyle(color: AppTheme.primaryOrange, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                ),
                const SizedBox(height: 14),

                // Phone
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(color: AppTheme.lightOrange, borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.phone, color: AppTheme.primaryOrange, size: 20),
                  ),
                  title: const Text('Phone Helpline', style: TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600)),
                  subtitle: Text(info.phone, style: const TextStyle(color: AppTheme.darkNavy, fontWeight: FontWeight.w800, fontSize: 13.5)),
                  trailing: IconButton(
                    icon: const Icon(Icons.call, color: AppTheme.primaryOrange),
                    onPressed: () => UrlHelper.callPhone(info.phone),
                  ),
                ),
                const Divider(height: 12, color: Colors.black12),

                // Email
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(color: AppTheme.lightOrange, borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.email, color: AppTheme.primaryOrange, size: 20),
                  ),
                  title: const Text('Email Support', style: TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600)),
                  subtitle: Text(info.email, style: const TextStyle(color: AppTheme.darkNavy, fontWeight: FontWeight.w800, fontSize: 13.5)),
                  trailing: IconButton(
                    icon: const Icon(Icons.send, color: AppTheme.primaryOrange),
                    onPressed: () => UrlHelper.sendEmail(info.email),
                  ),
                ),
                const Divider(height: 12, color: Colors.black12),

                // WhatsApp
                if (info.whatsappNumber != null) ...[
                  ListTile(
                    contentPadding: EdgeInsets.zero,
                    leading: Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(color: const Color(0xFFDCFCE7), borderRadius: BorderRadius.circular(10)),
                      child: const Icon(Icons.chat, color: Color(0xFF16A34A), size: 20),
                    ),
                    title: const Text('WhatsApp Official Desk', style: TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600)),
                    subtitle: Text(info.whatsappNumber!, style: const TextStyle(color: AppTheme.darkNavy, fontWeight: FontWeight.w800, fontSize: 13.5)),
                    trailing: IconButton(
                      icon: const Icon(Icons.arrow_forward_ios, size: 14, color: Color(0xFF16A34A)),
                      onPressed: () => UrlHelper.openWhatsApp(info.whatsappUrl ?? info.whatsappNumber),
                    ),
                  ),
                  const Divider(height: 12, color: Colors.black12),
                ],

                // Address
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(color: AppTheme.lightOrange, borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.location_on, color: AppTheme.primaryOrange, size: 20),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Headquarters Address', style: TextStyle(color: Colors.grey, fontSize: 11, fontWeight: FontWeight.w600)),
                            const SizedBox(height: 4),
                            Text(
                              info.address,
                              style: const TextStyle(color: AppTheme.darkNavy, fontSize: 12.5, height: 1.4, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // Message Submission Form Card
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
                  const Text(
                    'SEND AN INQUIRY / SEVA REQUEST',
                    style: TextStyle(color: AppTheme.primaryOrange, fontSize: 11, fontWeight: FontWeight.w900, letterSpacing: 0.8),
                  ),
                  const SizedBox(height: 14),

                  TextFormField(
                    controller: _nameController,
                    decoration: InputDecoration(
                      labelText: 'Full Name *',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter your name' : null,
                  ),
                  const SizedBox(height: 12),

                  TextFormField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'Email Address *',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                    validator: (v) => (v == null || !v.contains('@')) ? 'Please enter a valid email address' : null,
                  ),
                  const SizedBox(height: 12),

                  TextFormField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      labelText: 'Phone Number (Optional)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 12),

                  TextFormField(
                    controller: _subjectController,
                    decoration: InputDecoration(
                      labelText: 'Subject / Initiative Name (Optional)',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    ),
                  ),
                  const SizedBox(height: 12),

                  TextFormField(
                    controller: _messageController,
                    maxLines: 4,
                    decoration: InputDecoration(
                      labelText: 'Message / Inquiry Details *',
                      alignLabelWithHint: true,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      contentPadding: const EdgeInsets.all(14),
                    ),
                    validator: (v) {
                      if (v == null || v.trim().length < 5) {
                        return 'Message must be at least 5 characters';
                      }
                      return null;
                    },
                  ),
                  const SizedBox(height: 16),

                  if (_errorMessage != null) ...[
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: Colors.red.shade50,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(_errorMessage!, style: const TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 12),
                  ],

                  if (_successMessage != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(_successMessage!, style: const TextStyle(color: Color(0xFF059669), fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                    const SizedBox(height: 12),
                  ],

                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: _isSubmitting ? null : _submitForm,
                      icon: _isSubmitting
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Icon(Icons.send_rounded, size: 18),
                      label: Text(_isSubmitting ? 'TRANSMITTING...' : 'SUBMIT MESSAGE'),
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
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
