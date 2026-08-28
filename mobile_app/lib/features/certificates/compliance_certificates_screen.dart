import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/url_helper.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/certificate_model.dart';

final certificatesProvider = FutureProvider.autoDispose<List<CertificateModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getCertificates();
});

class ComplianceCertificatesScreen extends ConsumerWidget {
  const ComplianceCertificatesScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final certsAsync = ref.watch(certificatesProvider);

    return PublicScaffold(
      title: 'Tax Exemption & 80G / 12A Certificates',
      body: certsAsync.when(
        data: (certs) => _buildList(context, certs, ref),
        loading: () => const AppLoadingState(message: 'Loading statutory compliance certificates...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(certificatesProvider),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<CertificateModel> certs, WidgetRef ref) {
    if (certs.isEmpty) {
      return AppEmptyState(
        title: 'No Active Certificates',
        subtitle: 'Statutory compliance documents will be displayed here.',
        icon: Icons.verified_user_outlined,
        onAction: () => ref.refresh(certificatesProvider),
        actionLabel: 'Refresh',
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: certs.length,
      itemBuilder: (context, index) {
        final cert = certs[index];
        return Container(
          margin: const EdgeInsets.only(bottom: 16),
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
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppTheme.lightOrange,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(Icons.verified, color: AppTheme.primaryOrange, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: const Color(0xFF0F172A),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            cert.certificateType.toUpperCase(),
                            style: const TextStyle(
                              color: AppTheme.primaryOrange,
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          cert.title,
                          style: const TextStyle(
                            color: AppTheme.darkNavy,
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const Divider(color: Colors.black12, height: 24),

              // Details
              if (cert.documentNumber != null) ...[
                Row(
                  children: [
                    const Text('Registration No: ', style: TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.w600)),
                    Text(
                      cert.documentNumber!,
                      style: const TextStyle(color: AppTheme.darkNavy, fontSize: 12, fontWeight: FontWeight.w800),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
              ],

              Row(
                children: [
                  const Text('Validity: ', style: TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.w600)),
                  Text(
                    cert.validitySummary,
                    style: const TextStyle(color: Color(0xFF059669), fontSize: 12, fontWeight: FontWeight.w800),
                  ),
                ],
              ),

              if (cert.description != null && cert.description!.isNotEmpty) ...[
                const SizedBox(height: 10),
                Text(
                  cert.description!,
                  style: const TextStyle(color: Colors.black54, fontSize: 12, height: 1.4),
                ),
              ],

              const SizedBox(height: 16),

              // Download / View PDF button
              if (cert.downloadUrl != null && cert.downloadUrl!.isNotEmpty)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () => UrlHelper.launchSafeUrl(cert.downloadUrl),
                    icon: const Icon(Icons.picture_as_pdf, size: 18),
                    label: const Text('VIEW / DOWNLOAD CERTIFICATE (PDF)'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.darkNavy,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }
}
