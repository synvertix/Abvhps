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

final examsNoticeBoardProvider = FutureProvider.autoDispose<List<ExamModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getExams();
});

class ExamsNoticeBoardScreen extends ConsumerWidget {
  const ExamsNoticeBoardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final examsAsync = ref.watch(examsNoticeBoardProvider);

    return PublicScaffold(
      title: 'Sanathana Dharma Examinations',
      body: examsAsync.when(
        data: (exams) => _buildList(context, exams, ref),
        loading: () => const AppLoadingState(message: 'Loading examination cycles & schedule...'),
        error: (err, _) => AppErrorState(
          message: err.toString(),
          onRetry: () => ref.refresh(examsNoticeBoardProvider),
        ),
      ),
    );
  }

  Widget _buildList(BuildContext context, List<ExamModel> exams, WidgetRef ref) {
    return Column(
      children: [
        // Quick Action Bar: View Results & Hall Ticket Lookup
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          color: const Color(0xFF0B1426),
          child: Row(
            children: [
              const Icon(Icons.emoji_events, color: AppTheme.primaryOrange, size: 22),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'EXAMINATION RESULTS & WINNERS',
                  style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w900),
                ),
              ),
              ElevatedButton(
                onPressed: () => context.push('/exams/results'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryOrange,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                  textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 11),
                ),
                child: const Text('CHECK RESULTS'),
              ),
            ],
          ),
        ),

        Expanded(
          child: exams.isEmpty
              ? AppEmptyState(
                  title: 'No Active Exam Notices',
                  subtitle: 'Upcoming exam announcements and syllabus will be posted here.',
                  icon: Icons.school_outlined,
                  onAction: () => ref.refresh(examsNoticeBoardProvider),
                  actionLabel: 'Refresh',
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: exams.length,
                  itemBuilder: (context, index) {
                    final e = exams[index];
                    return _buildExamCard(context, e);
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildExamCard(BuildContext context, ExamModel e) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
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
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => context.push('/exams/${e.id}'),
          borderRadius: BorderRadius.circular(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Banner
              if (e.bannerImageUrl != null && e.bannerImageUrl!.isNotEmpty)
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                  child: AspectRatio(
                    aspectRatio: 16 / 7,
                    child: AppNetworkImage(
                      imageUrl: e.bannerImageUrl,
                      fit: BoxFit.cover,
                    ),
                  ),
                ),

              Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: AppTheme.lightOrange,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            e.examTypeLabel?.toUpperCase() ?? 'EXAMINATION',
                            style: const TextStyle(
                              color: AppTheme.primaryOrange,
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: e.status == 'active' ? const Color(0xFFECFDF5) : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            e.status.toUpperCase(),
                            style: TextStyle(
                              color: e.status == 'active' ? const Color(0xFF059669) : Colors.black54,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),

                    Text(
                      e.examTitle,
                      style: const TextStyle(
                        color: AppTheme.darkNavy,
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 8),

                    if (e.examDateTime != null)
                      Row(
                        children: [
                          const Icon(Icons.event, size: 14, color: AppTheme.primaryOrange),
                          const SizedBox(width: 6),
                          Text(
                            e.examDateTime!,
                            style: const TextStyle(color: Colors.black87, fontSize: 12, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),

                    if (e.examCenterLocation != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined, size: 14, color: Colors.grey),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              e.examCenterLocation!,
                              style: const TextStyle(color: Colors.black54, fontSize: 12),
                            ),
                          ),
                        ],
                      ),
                    ],

                    const Divider(color: Colors.black12, height: 20),

                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Fee: ₹${e.applicationFee.toStringAsFixed(0)}',
                          style: const TextStyle(
                            color: AppTheme.primaryOrange,
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        if (e.syllabusUrl != null && e.syllabusUrl!.isNotEmpty)
                          TextButton.icon(
                            onPressed: () => UrlHelper.launchSafeUrl(e.syllabusUrl),
                            icon: const Icon(Icons.download, size: 14, color: AppTheme.darkNavy),
                            label: const Text(
                              'Syllabus PDF',
                              style: TextStyle(color: AppTheme.darkNavy, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
