import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';

class AdminFundraisingScreen extends ConsumerStatefulWidget {
  const AdminFundraisingScreen({super.key});

  @override
  ConsumerState<AdminFundraisingScreen> createState() => _AdminFundraisingScreenState();
}

class _AdminFundraisingScreenState extends ConsumerState<AdminFundraisingScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final campaignsAsync = ref.watch(adminFundraisingProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('FUNDRAISING MATRICES'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminFundraisingProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Campaigns by Title',
                  prefixIcon: const Icon(Icons.search),
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                ),
                onChanged: (val) {
                  setState(() {
                    _search = val.trim();
                  });
                },
              ),
              const SizedBox(height: 16),
              Expanded(
                child: campaignsAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No fundraising campaigns found.'));
                    }
                    return ListView.builder(
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isActive = item.status == 'active';
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: Padding(
                            padding: const EdgeInsets.all(12.0),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(
                                        item.title,
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                      ),
                                    ),
                                    IconButton(
                                      icon: Icon(isActive ? Icons.visibility : Icons.visibility_off, color: isActive ? Colors.green : Colors.grey),
                                      onPressed: () async {
                                        try {
                                          await ref.read(apiClientProvider).post('/admin/fundraising/${item.id}/toggle', data: {});
                                          ref.invalidate(adminFundraisingProvider(_search));
                                        } catch (e) {
                                          if (context.mounted) {
                                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                          }
                                        }
                                      },
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Text('Raised: ₹${item.raisedAmount.toStringAsFixed(0)} of Target ₹${item.targetAmount.toStringAsFixed(0)} (${item.progressPct.toStringAsFixed(1)}%)'),
                                const SizedBox(height: 8),
                                LinearProgressIndicator(
                                  value: (item.progressPct / 100).clamp(0.0, 1.0),
                                  backgroundColor: Colors.grey[200],
                                  color: const Color(0xFFFF6B00),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    );
                  },
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(child: Text('Error loading campaigns: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
