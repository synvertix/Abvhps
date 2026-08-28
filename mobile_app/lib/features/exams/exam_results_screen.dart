import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/api/public_api_repository.dart';
import '../../core/theme/app_theme.dart';
import '../../core/widgets/app_states.dart';
import '../../core/widgets/public_scaffold.dart';
import 'models/exam_model.dart';

final examWinnersProvider = FutureProvider.autoDispose<List<ExamWinnerModel>>((ref) async {
  final repo = ref.watch(publicApiRepositoryProvider);
  return repo.getExamWinners();
});

class ExamResultsScreen extends ConsumerStatefulWidget {
  const ExamResultsScreen({super.key});

  @override
  ConsumerState<ExamResultsScreen> createState() => _ExamResultsScreenState();
}

class _ExamResultsScreenState extends ConsumerState<ExamResultsScreen> {
  final TextEditingController _ticketController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  bool _isSearching = false;
  ExamResultModel? _searchResult;
  String? _errorMessage;

  @override
  void dispose() {
    _ticketController.dispose();
    super.dispose();
  }

  Future<void> _performSearch() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isSearching = true;
      _errorMessage = null;
      _searchResult = null;
    });

    final repo = ref.read(publicApiRepositoryProvider);
    try {
      final res = await repo.searchExamResult(_ticketController.text.trim());
      setState(() {
        _searchResult = res;
        _isSearching = false;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceAll('Exception:', '').trim();
        _isSearching = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final winnersAsync = ref.watch(examWinnersProvider);

    return PublicScaffold(
      title: 'Examination Results Portal',
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Hall Ticket Search Card
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppTheme.primaryOrange.withValues(alpha: 0.3)),
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
                          child: const Icon(Icons.search, color: AppTheme.primaryOrange, size: 20),
                        ),
                        const SizedBox(width: 10),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'CANDIDATE RESULT LOOKUP',
                                style: TextStyle(
                                  color: AppTheme.darkNavy,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.6,
                                ),
                              ),
                              Text(
                                'Enter your 11-digit Hall Ticket Number',
                                style: TextStyle(color: Colors.grey, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),

                    TextFormField(
                      controller: _ticketController,
                      keyboardType: TextInputType.number,
                      maxLength: 11,
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      decoration: InputDecoration(
                        counterText: '',
                        hintText: 'e.g. 10000000001',
                        prefixIcon: const Icon(Icons.confirmation_number_outlined, color: AppTheme.primaryOrange),
                        filled: true,
                        fillColor: Colors.grey.shade50,
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
                          return 'Please enter your Hall Ticket Number';
                        }
                        if (val.trim().length != 11) {
                          return 'Hall Ticket Number must be exactly 11 digits';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 14),

                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: _isSearching ? null : _performSearch,
                        icon: _isSearching
                            ? const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : const Icon(Icons.arrow_forward, size: 18),
                        label: Text(_isSearching ? 'SEARCHING ARCHIVE...' : 'SEARCH RESULT'),
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

            // Search Error / Message
            if (_errorMessage != null) ...[
              const SizedBox(height: 16),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.amber.shade300),
                ),
                child: Row(
                  children: [
                    Icon(Icons.info_outline, color: Colors.amber.shade900, size: 22),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _errorMessage!,
                        style: TextStyle(color: Colors.amber.shade900, fontSize: 12.5, fontWeight: FontWeight.w600),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            // Search Result Card
            if (_searchResult != null) ...[
              const SizedBox(height: 16),
              _buildCandidateResultCard(_searchResult!),
            ],

            const SizedBox(height: 24),

            // Winners Wall Section
            const Text(
              'TOP MERIT RANK HOLDERS (WINNERS WALL)',
              style: TextStyle(
                color: AppTheme.darkNavy,
                fontSize: 13,
                fontWeight: FontWeight.w900,
                letterSpacing: 0.6,
              ),
            ),
            const SizedBox(height: 10),

            winnersAsync.when(
              data: (winners) => _buildWinnersWall(winners),
              loading: () => const AppLoadingState(message: 'Loading state merit ranks...'),
              error: (err, _) => Container(
                padding: const EdgeInsets.all(12),
                child: Text('Notice: Merit board pending announcement.', style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
              ),
            ),

            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildCandidateResultCard(ExamResultModel r) {
    final isPassed = (r.status?.toLowerCase() == 'passed');

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: isPassed ? const Color(0xFF10B981) : Colors.red),
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
                  color: isPassed ? const Color(0xFFECFDF5) : Colors.red.shade50,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  r.status?.toUpperCase() ?? 'ANNOUNCED',
                  style: TextStyle(
                    color: isPassed ? const Color(0xFF059669) : Colors.red,
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (r.grade != null)
                Text(
                  'Grade: ${r.grade}',
                  style: const TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.w900, fontSize: 13),
                ),
            ],
          ),
          const SizedBox(height: 12),

          Text(
            r.fullName,
            style: const TextStyle(color: AppTheme.darkNavy, fontSize: 16, fontWeight: FontWeight.w900),
          ),
          if (r.schoolName != null) ...[
            const SizedBox(height: 2),
            Text(r.schoolName!, style: const TextStyle(color: Colors.black54, fontSize: 12)),
          ],
          const Divider(color: Colors.black12, height: 20),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('HALL TICKET', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                  Text(r.hallTicket, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                ],
              ),
              if (r.marksObtained != null && r.totalMarks != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    const Text('MARKS', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                    Text('${r.marksObtained} / ${r.totalMarks}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13)),
                  ],
                ),
              if (r.percentage != null)
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    const Text('PERCENTAGE', style: TextStyle(color: Colors.grey, fontSize: 10, fontWeight: FontWeight.bold)),
                    Text('${r.percentage}%', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13, color: AppTheme.primaryOrange)),
                  ],
                ),
            ],
          ),

          if (r.prizeTitleWon != null && r.prizeTitleWon!.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.emoji_events, color: AppTheme.primaryOrange, size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Special Award: ${r.prizeTitleWon}',
                      style: const TextStyle(color: AppTheme.darkNavy, fontSize: 11.5, fontWeight: FontWeight.w700),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildWinnersWall(List<ExamWinnerModel> winners) {
    if (winners.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade200),
        ),
        child: const Center(
          child: Text(
            'The merit winners wall for current cycles will be featured here upon official release.',
            style: TextStyle(color: Colors.black54, fontSize: 12),
            textAlign: TextAlign.center,
          ),
        ),
      );
    }

    return Column(
      children: winners.map((w) {
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Row(
            children: [
              Container(
                width: 38,
                height: 38,
                decoration: BoxDecoration(
                  color: w.winnerRank == 1
                      ? const Color(0xFFFEF3C7)
                      : (w.winnerRank == 2 ? const Color(0xFFF3F4F6) : const Color(0xFFFFEDD5)),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: w.winnerRank == 1 ? const Color(0xFFF59E0B) : Colors.grey.shade400,
                  ),
                ),
                child: Center(
                  child: Text(
                    '#${w.winnerRank}',
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12, color: AppTheme.darkNavy),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      w.fullName,
                      style: const TextStyle(color: AppTheme.darkNavy, fontWeight: FontWeight.w800, fontSize: 13),
                    ),
                    if (w.schoolName != null)
                      Text(w.schoolName!, style: const TextStyle(color: Colors.grey, fontSize: 11)),
                    if (w.prizeTitleWon != null)
                      Text(
                        w.prizeTitleWon!,
                        style: const TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.bold, fontSize: 11),
                      ),
                  ],
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}
