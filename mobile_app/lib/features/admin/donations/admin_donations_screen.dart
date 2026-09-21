import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:abvhpsapp/features/admin/repositories/admin_c_repositories.dart';
import 'package:abvhpsapp/features/admin/widgets/admin_drawer.dart';

class AdminDonationsScreen extends ConsumerStatefulWidget {
  const AdminDonationsScreen({super.key});

  @override
  ConsumerState<AdminDonationsScreen> createState() => _AdminDonationsScreenState();
}

class _AdminDonationsScreenState extends ConsumerState<AdminDonationsScreen> {
  String _search = '';

  @override
  Widget build(BuildContext context) {
    final donationsAsync = ref.watch(adminDonationsProvider(_search));

    return Scaffold(
      appBar: AppBar(
        title: const Text('DONATIONS LEDGER'),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
      ),
      drawer: const AdminNavigationDrawer(),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(adminDonationsProvider(_search));
        },
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              TextField(
                decoration: InputDecoration(
                  labelText: 'Search Donor by Name, Contact, PAN, Gateway Ref',
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
                child: donationsAsync.when(
                  data: (items) {
                    if (items.isEmpty) {
                      return const Center(child: Text('No donation records found.'));
                    }
                    return ListView.builder(
                      itemCount: items.length,
                      itemBuilder: (context, index) {
                        final item = items[index];
                        final isPaid = item.paymentStatus.toUpperCase() == 'PAID';
                        return Card(
                          margin: const EdgeInsets.only(bottom: 12),
                          elevation: 2,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          child: ListTile(
                            leading: CircleAvatar(
                              backgroundColor: isPaid ? Colors.green.withValues(alpha: 0.1) : Colors.orange.withValues(alpha: 0.1),
                              child: Icon(
                                isPaid ? Icons.check_circle : Icons.hourglass_top,
                                color: isPaid ? Colors.green : Colors.orange,
                              ),
                            ),
                            title: Text(item.name, style: const TextStyle(fontWeight: FontWeight.bold)),
                            subtitle: Text('₹${item.amount.toStringAsFixed(2)} • ${item.cause}\nGateway: ${item.paymentGateway} • Ref: ${item.gatewayPaymentId ?? item.gatewayOrderId ?? "N/A"}'),
                            isThreeLine: true,
                            trailing: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: isPaid ? Colors.green.withValues(alpha: 0.1) : Colors.orange.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                item.paymentStatus,
                                style: TextStyle(
                                  color: isPaid ? Colors.green : Colors.orange,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ),
                        );
                      },
                    );
                  },
                  loading: () => const Center(child: CircularProgressIndicator()),
                  error: (err, stack) => Center(child: Text('Error loading donations: $err')),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
