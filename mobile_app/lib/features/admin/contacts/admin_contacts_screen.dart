import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';
import 'package:abvhpsapp/core/auth/auth_notifier.dart';

class AdminContactsScreen extends ConsumerStatefulWidget {
  const AdminContactsScreen({super.key});

  @override
  ConsumerState<AdminContactsScreen> createState() => _AdminContactsScreenState();
}

class _AdminContactsScreenState extends ConsumerState<AdminContactsScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final contactsAsync = ref.watch(adminContactsProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('CONTACT FORMS AUDIT'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminContactsProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Inquiries by Sender, Email, Subject',
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
                child: contactsAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No contact inquiries found.'));
                    }
                    return ListView.builder(
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isUnread = item.status == 'unread';
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: isUnread ? Colors.red.withValues(alpha: 0.1) : Colors.blue.withValues(alpha: 0.1),
                              child: Icon(
                                isUnread ? Icons.mark_email_unread : Icons.mark_email_read,
                                color: isUnread ? Colors.red : Colors.blue,
                              ),
                            ),
                            title: Text(item.name, style: TextStyle(fontWeight: isUnread ? FontWeight.bold : FontWeight.normal)),
                            subtitle: Text('${item.subject} • ${item.email}\n"${item.message}"'),
                            isThreeLine: true,
                            trailing: IconButton(
                              icon: Icon(
                                isUnread ? Icons.check_circle_outline : Icons.delete,
                                color: isUnread ? Colors.green : Colors.red,
                              ),
                              onPressed: () async {
                                if (isUnread) {
                                  try {
                                    await ref.read(apiClientProvider).post('/admin/contacts/${item.id}/status', data: {'status': 'read'});
                                    ref.invalidate(adminContactsProvider(_search));
                                  } catch (e) {
                                    if (context.mounted) {
                                      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Error: $e')));
                                    }
                                  }
                                } else {
                                  try {
                                    await ref.read(apiClientProvider).delete('/admin/contacts/${item.id}');
                                    ref.invalidate(adminContactsProvider(_search));
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
                  error: (err, stack) => Center(child: Text('Error loading contact messages: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
