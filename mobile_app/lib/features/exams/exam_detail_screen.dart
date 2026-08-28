import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/url_helper.dart';
import '../../core/widgets/app_network_image.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/exam_model.dart';

final examDetailProvider = FutureProvider.autoDispose.family<ExamModel, int>((ref, id) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getExam(id);
});

class ExamDetailScreen extends ConsumerWidget {
  final int examId;

  const ExamDetailScreen({
    super.key,
    required this.examId,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final examAsync = ref.watch(examDetailProvider(examId));

    return PublicScaffold(
      title: 'Exam Cycle Details',
      body: examAsync.when(
        data: (exam) => _buildDetail(context, exam),
        loading: () => const AppLoadingState(message: 'Loading examination details...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(examDetailProvider(examId)),
        ),
      ),
    );
  }

  Widget _buildDetail(BuildContext context, ExamModel e) {
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
                context.go('/exams/board');
              }
            },
            icon: const Icon(Icons.arrow_back, size: 18, color: AppTheme.primaryOrange),
            label: const Text(
              'Back to Exam Board',
              style: TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 8),

          if (e.bannerImageUrl != null && e.bannerImageUrl!.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: AspectRatio(
                aspectRatio: 16 / 8,
                child: AppNetworkImage(
                  imageUrl: e.bannerImageUrl,
                  fit: BoxFit.cover,
                ),
              ),
            ),
          const SizedBox(height: 16),

          Text(
            e.examTitle,
            style: const TextStyle(
              color: AppTheme.darkNavy,
              fontSize: 18,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.4,
            ),
          ),
          const SizedBox(height: 12),

          // Overview Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              children: [
                _buildInfoRow(Icons.category, 'Type', e.examTypeLabel ?? 'Examination'),
                const Divider(color: Colors.black12, height: 16),
                _buildInfoRow(Icons.event, 'Exam Date', e.examDateTime ?? 'To be announced'),
                const Divider(color: Colors.black12, height: 16),
                _buildInfoRow(Icons.location_on, 'Center', e.examCenterLocation ?? 'Designated Exam Centers'),
                const Divider(color: Colors.black12, height: 16),
                _buildInfoRow(Icons.currency_rupee, 'Application Fee', '₹${e.applicationFee.toStringAsFixed(0)}'),
              ],
            ),
          ),

          if (e.prizes.isNotEmpty) ...[
            const SizedBox(height: 18),
            const Text(
              'PRIZES & AWARDS',
              style: TextStyle(color: AppTheme.darkNavy, fontSize: 13, fontWeight: FontWeight.w900, letterSpacing: 0.6),
            ),
            const SizedBox(height: 10),
            ...e.prizes.map((p) => Container(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFFDE68A)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.emoji_events, color: AppTheme.primaryOrange, size: 20),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          p,
                          style: const TextStyle(color: AppTheme.darkNavy, fontWeight: FontWeight.w700, fontSize: 12.5),
                        ),
                      ),
                    ],
                  ),
                )),
          ],

          if (e.guidelines != null && e.guidelines!.isNotEmpty) ...[
            const SizedBox(height: 18),
            const Text(
              'EXAM GUIDELINES & SYLLABUS',
              style: TextStyle(color: AppTheme.darkNavy, fontSize: 13, fontWeight: FontWeight.w900, letterSpacing: 0.6),
            ),
            const SizedBox(height: 10),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.grey.shade200),
              ),
              child: Text(
                e.guidelines!,
                style: const TextStyle(color: Colors.black87, fontSize: 13, height: 1.5),
              ),
            ),
          ],

          const SizedBox(height: 20),

          if (e.syllabusUrl != null && e.syllabusUrl!.isNotEmpty)
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => UrlHelper.launchSafeUrl(e.syllabusUrl),
                icon: const Icon(Icons.download, size: 18),
                label: const Text('DOWNLOAD OFFICIAL SYLLABUS (PDF)'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.darkNavy,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5),
                ),
              ),
            ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: AppTheme.primaryOrange),
        const SizedBox(width: 8),
        Text('$label: ', style: const TextStyle(color: Colors.grey, fontSize: 12, fontWeight: FontWeight.w600)),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(color: AppTheme.darkNavy, fontSize: 12.5, fontWeight: FontWeight.w800),
            textAlign: TextAlign.right,
          ),
        ),
      ],
    );
  }
}
