import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../repositories/admin_b_repositories.dart';
import '../models/admin_membership_model.dart';
import '../widgets/admin_drawer.dart';

class AdminApprovedMembershipsScreen extends ConsumerStatefulWidget {
  const AdminApprovedMembershipsScreen({super.key});

  @override
  ConsumerState<AdminApprovedMembershipsScreen> createState() => _AdminApprovedMembershipsScreenState();
}

class _AdminApprovedMembershipsScreenState extends ConsumerState<AdminApprovedMembershipsScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final membershipsAsync = ref.watch(adminApprovedMembershipsProvider(_searchQuery));

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'APPROVED MEMBERSHIPS',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 0.5),
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(adminApprovedMembershipsProvider(_searchQuery)),
          ),
        ],
      ),
      drawer: const AdminNavigationDrawer(currentRoute: '/admin/memberships'),
      body: Container(
        color: const Color(0xFFF8FAFC),
        child: Column(
          children: [
            // Search Bar header
            Container(
              padding: const EdgeInsets.all(16),
              color: Colors.white,
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search by ID, name, mobile, district...',
                  prefixIcon: const Icon(Icons.search, color: Color(0xFFFF6B00)),
                  suffixIcon: _searchQuery.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear),
                          onPressed: () {
                            _searchController.clear();
                            setState(() => _searchQuery = '');
                          },
                        )
                      : null,
                  filled: true,
                  fillColor: const Color(0xFFF1F5F9),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                ),
                onSubmitted: (value) => setState(() => _searchQuery = value),
              ),
            ),
            // Roster list
            Expanded(
              child: membershipsAsync.when(
                data: (items) {
                  if (items.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.badge_outlined, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          const Text(
                            'No approved memberships found.',
                            style: TextStyle(fontSize: 16, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                          ),
                        ],
                      ),
                    );
                  }

                  return ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: items.length,
                    itemBuilder: (context, index) {
                      final item = items[index];
                      return _buildMembershipCard(context, item);
                    },
                  );
                },
                loading: () => const Center(
                  child: CircularProgressIndicator(color: Color(0xFFFF6B00)),
                ),
                error: (err, stack) => Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: Colors.redAccent),
                        const SizedBox(height: 12),
                        Text(
                          'Failed to load memberships: $err',
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => ref.invalidate(adminApprovedMembershipsProvider(_searchQuery)),
                          icon: const Icon(Icons.refresh),
                          label: const Text('Retry'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF0F172A),
                            foregroundColor: Colors.white,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMembershipCard(BuildContext context, AdminMembership item) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 22,
                  backgroundColor: const Color(0xFFFF6B00).withValues(alpha: 0.15),
                  child: Text(
                    item.fullName.isNotEmpty ? item.fullName[0].toUpperCase() : 'M',
                    style: const TextStyle(color: Color(0xFFFF6B00), fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.fullName,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'ID: ${item.membershipId}',
                        style: const TextStyle(color: Color(0xFFFF6B00), fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: Colors.green.shade300),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: const [
                      Icon(Icons.check_circle, size: 12, color: Colors.green),
                      SizedBox(width: 4),
                      Text(
                        'APPROVED',
                        style: TextStyle(color: Colors.green, fontSize: 10, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 24),
            Row(
              children: [
                const Icon(Icons.phone, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Text(item.phone, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                if (item.email != null && item.email!.isNotEmpty) ...[
                  const Spacer(),
                  const Icon(Icons.email, size: 14, color: Color(0xFF64748B)),
                  const SizedBox(width: 6),
                  Flexible(
                    child: Text(
                      item.email!,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
                    ),
                  ),
                ],
              ],
            ),
            if (item.district != null || item.mandal != null) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.location_on, size: 14, color: Color(0xFF64748B)),
                  const SizedBox(width: 6),
                  Text(
                    [item.gramaPanchayat, item.mandal, item.district, item.state].where((e) => e != null && e.isNotEmpty).join(', '),
                    style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }
}
