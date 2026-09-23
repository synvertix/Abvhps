import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';

class AdminTaxCertificatesScreen extends ConsumerStatefulWidget {
  const AdminTaxCertificatesScreen({super.key});

  @override
  ConsumerState<AdminTaxCertificatesScreen> createState() => _AdminTaxCertificatesScreenState();
}

class _AdminTaxCertificatesScreenState extends ConsumerState<AdminTaxCertificatesScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final certsAsync = ref.watch(adminTaxCertificatesProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('TAX CERTIFICATES'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminTaxCertificatesProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Certificates by Title or Doc No',
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
                child: certsAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No compliance certificates found.'));
                    }
                    return ListView.builder(
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isPublic = item.isActive;
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: Colors.red.withValues(alpha: 0.1),
                              child: const Icon(Icons.picture_as_pdf, color: Colors.red),
                            ),
                            title: Text(item.title, style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text('Type: ${item.certificateType} • Doc No: ${item.documentNumber ?? "N/A"}\nStatus: ${isPublic ? "PUBLIC" : "HIDDEN"}'),
                            isThreeLine: true,
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                IconButton(
                                  icon: Icon(
                                    isPublic ? Icons.visibility : Icons.visibility_off,
                                    color: isPublic ? Colors.green : Colors.grey,
                                  ),
                                  tooltip: isPublic ? 'Hide Certificate' : 'Show Certificate',
                                  onPressed: () async {
                                    try {
                                      await ref.read(apiClientProvider).post('/admin/tax-certificates/${item.id}/toggle', data: {});
                                      ref.invalidate(adminTaxCertificatesProvider(_search));
                                    } catch (e) {
                                      if (context.mounted) {
                                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                      }
                                    }
                                  },
                                ),
                                IconButton(
                                  icon: const Icon(Icons.delete, color: Colors.red),
                                  onPressed: () async {
                                    final confirm = await showDialog<bool>(
                                      context: context,
                                      builder: (ctx) => AlertDialog(
                                        title: const Text('Delete Certificate'),
                                        content: Text('Delete "${item.title}"?'),
                                        actions: [
                                          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Cancel')),
                                          ElevatedButton(
                                            onPressed: () => Navigator.pop(ctx, true),
                                            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
                                            child: const Text('Delete'),
                                          ),
                                        ],
                                      ),
                                    );
                                    if (confirm == true) {
                                      try {
                                        await ref.read(apiClientProvider).delete('/admin/tax-certificates/${item.id}');
                                        ref.invalidate(adminTaxCertificatesProvider(_search));
                                      } catch (e) {
                                        if (context.mounted) {
                                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                        }
                                      }
                                    }
                                  },
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    );
                  },
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(child: Text('Error loading tax certificates: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
