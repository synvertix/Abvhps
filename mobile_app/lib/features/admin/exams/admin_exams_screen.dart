import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';

class AdminExamsScreen extends ConsumerWidget {
  const AdminExamsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final examsAsync = ref.watch(adminExamsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('EXAMS INFO BOARD'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminExamsProvider);
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: examsAsync.when(
            data: (items) {
              if (items.isEmpty) {
                return const Center(child: Text('No exam cycles configured.'));
              }
              return ListView.builder(
                itemCount: items.length,
                itemBuilder: (context, index) {
                  final item = items[index];
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    elevation: 2,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: const Color(0xFFFF6B00).withValues(alpha: 0.15),
                        child: const Icon(Icons.school, color: Color(0xFFFF6B00)),
                      ),
                      title: Text(item.examTitle, style: const TextStyle(fontWeight: FontWeight.bold)),
                      subtitle: Text('Fee: ₹${item.applicationFee.toStringAsFixed(0)} • Schedule: ${item.examDateTime}\nApplicants: ${item.totalApplicants} (${item.paidApplicants} Paid)'),
                      isThreeLine: true,
                      trailing: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: item.status == 'active' ? Colors.green.withValues(alpha: 0.1) : Colors.grey.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          item.status.toUpperCase(),
                          style: TextStyle(
                            color: item.status == 'active' ? Colors.green : Colors.grey[700],
                            fontWeight: FontWeight.bold,
                            fontSize: 11,
                          ),
                        ),
                      ),
                    ),
                  );
                },
              );
            },
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (err, stack) => Center(child: Text('Error loading exam cycles: $err')),
          ),
        ),
      ),
    );
  }
}
