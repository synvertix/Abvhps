import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';

class AdminBannersScreen extends ConsumerStatefulWidget {
  const AdminBannersScreen({super.key});

  @override
  ConsumerState<AdminBannersScreen> createState() => _AdminBannersScreenState();
}

class _AdminBannersScreenState extends ConsumerState<AdminBannersScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final bannersAsync = ref.watch(adminBannersProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('BANNER MANAGEMENT'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminBannersProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Banners by Page Key or Title',
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
                child: bannersAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No banners configured.'));
                    }
                    return ListView.builder(
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isShow = item.status == 'show';
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: const Color(0xFF0F172A).withValues(alpha: 0.1),
                              child: const Icon(Icons.view_carousel, color: Color(0xFF0F172A)),
                            ),
                            title: Text(item.title ?? item.pageKey.toUpperCase(), style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text('Page: ${item.pageKey} • Status: ${item.status.toUpperCase()}'),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                IconButton(
                                  icon: Icon(
                                    isShow ? Icons.visibility : Icons.visibility_off,
                                    color: isShow ? Colors.green : Colors.grey,
                                  ),
                                  onPressed: () async {
                                    try {
                                      await ref.read(apiClientProvider).post('/admin/banners/${item.id}/toggle', data: {});
                                      ref.invalidate(adminBannersProvider(_search));
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
                                        title: const Text('Delete Banner'),
                                        content: const Text('Delete this page banner?'),
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
                                        await ref.read(apiClientProvider).delete('/admin/banners/${item.id}');
                                        ref.invalidate(adminBannersProvider(_search));
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
                  error: (err, stack) => Center(child: Text('Error loading banners: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
