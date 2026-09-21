import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';

class AdminSupportCoresScreen extends ConsumerStatefulWidget {
  const AdminSupportCoresScreen({super.key});

  @override
  ConsumerState<AdminSupportCoresScreen> createState() => _AdminSupportCoresScreenState();
}

class _AdminSupportCoresScreenState extends ConsumerState<AdminSupportCoresScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final supportsAsync = ref.watch(adminSupportCoresProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('OUR SUPPORT CORES'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminSupportCoresProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Core Projects by Name or Info',
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
                child: supportsAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No support core projects found.'));
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
                              backgroundColor: const Color(0xFF0F172A).withValues(alpha: 0.1),
                              child: Text('${item.sortOrder}', style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF0F172A))),
                            ),
                            title: Text(item.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text(item.shortInfo, maxLines: 2, overflow: TextOverflow.ellipsis),
                            trailing: IconButton(
                              icon: const Icon(Icons.delete, color: Colors.red),
                              onPressed: () async {
                                final confirm = await showDialog<bool>(
                                  context: context,
                                  builder: (ctx) => AlertDialog(
                                    title: const Text('Delete Project'),
                                    content: Text('Delete "${item.name}" project?'),
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
                                    await ref.read(apiClientProvider).delete('/admin/support-cores/${item.id}');
                                    ref.invalidate(adminSupportCoresProvider(_search));
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Project deleted.')));
                                    }
                                  } catch (e) {
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                    }
                                  }
                                }
                              },
                            ),
                          ),
                        );
                      },
                    );
                  },
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(child: Text('Error loading support cores: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
