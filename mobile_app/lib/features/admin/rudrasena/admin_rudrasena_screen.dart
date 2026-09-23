import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/auth/auth_notifier.dart';
import '../repositories/admin_b_repositories.dart';
import '../models/admin_rudrasena_model.dart';
import '../widgets/admin_drawer.dart';

class AdminRudrasenaScreen extends ConsumerStatefulWidget {
  const AdminRudrasenaScreen({super.key});

  @override
  ConsumerState<AdminRudrasenaScreen> createState() => _AdminRudrasenaScreenState();
}

class _AdminRudrasenaScreenState extends ConsumerState<AdminRudrasenaScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final rudrasenaAsync = ref.watch(adminRudrasenaProvider(_searchQuery));

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'RUDRASENA DESK',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 0.5),
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(adminRudrasenaProvider(_searchQuery)),
          ),
        ],
      ),
      drawer: const AdminNavigationDrawer(currentRoute: '/admin/rudrasena'),
      body: Container(
        color: const Color(0xFFF8FAFC),
        child: Column(
          children: [
            // Search Bar
            Container(
              padding: const EdgeInsets.all(16),
              color: Colors.white,
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Search Rudrasena ID, name, mobile, status...',
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
            // Rudrasena list
            Expanded(
              child: rudrasenaAsync.when(
                data: (items) {
                  if (items.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.shield_outlined, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          const Text(
                            'No Rudrasena cadets found.',
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
                      return _buildRudrasenaCard(context, item);
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
                          'Failed to load Rudrasena roster: $err',
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => ref.invalidate(adminRudrasenaProvider(_searchQuery)),
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

  Widget _buildRudrasenaCard(BuildContext context, AdminRudrasenaMember item) {
    final isVerified = item.status == 'verified' || item.status == 'approved';
    final isPending = item.status == 'pending';

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
                  backgroundColor: const Color(0xFFFF6B00),
                  child: const Icon(Icons.shield, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.name,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          Text(
                            'RS ID: ${item.rudrasenaId}',
                            style: const TextStyle(color: Color(0xFFFF6B00), fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                          if (item.age != null) ...[
                            const SizedBox(width: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: item.isAgeEligible ? Colors.blue.shade50 : Colors.red.shade50,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                'Age: ${item.age} ${item.isAgeEligible ? "(24-44 OK)" : "(INVALID AGE)"}',
                                style: TextStyle(
                                  color: item.isAgeEligible ? Colors.blue.shade800 : Colors.red,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          ],
                        ],
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isVerified ? Colors.green.shade50 : (isPending ? Colors.amber.shade50 : Colors.red.shade50),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: isVerified ? Colors.green.shade300 : (isPending ? Colors.amber.shade300 : Colors.red.shade300),
                    ),
                  ),
                  child: Text(
                    item.status.toUpperCase(),
                    style: TextStyle(
                      color: isVerified ? Colors.green : (isPending ? Colors.amber.shade800 : Colors.red),
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              children: [
                const Icon(Icons.phone, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Text(item.mobile, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                const Spacer(),
                const Icon(Icons.military_tech, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Text(item.assignedCadder, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                ElevatedButton.icon(
                  onPressed: () => _showStatusApprovalDialog(context, item),
                  icon: const Icon(Icons.check_circle_outline, size: 16),
                  label: const Text('Update / Approve Cadet'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F172A),
                    foregroundColor: Colors.white,
                    visualDensity: VisualDensity.compact,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showStatusApprovalDialog(BuildContext context, AdminRudrasenaMember item) {
    String selectedStatus = item.status == 'verified' ? 'verified' : item.status;
    final cadderController = TextEditingController(text: item.assignedCadder);
    final localityController = TextEditingController(text: item.assignedLocality);

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: Text('Rudrasena Cadet #${item.rudrasenaId}'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (!item.isAgeEligible) ...[
                      Container(
                        padding: const EdgeInsets.all(10),
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.red.shade200),
                        ),
                        child: const Text(
                          'AGE INVARIANT ALERT: Member age must be 24-44 inclusive to be verified as Rudrasena.',
                          style: TextStyle(color: Colors.red, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                    const Text('Status:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    DropdownButton<String>(
                      value: selectedStatus,
                      isExpanded: true,
                      items: const [
                        DropdownMenuItem(value: 'verified', child: Text('Verified (Approve)')),
                        DropdownMenuItem(value: 'pending', child: Text('Pending')),
                        DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => selectedStatus = val);
                      },
                    ),
                    const SizedBox(height: 12),
                    const Text('Assigned Cadder:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    TextField(
                      controller: cadderController,
                      decoration: const InputDecoration(hintText: 'e.g. Dal Leader, Dal Member'),
                    ),
                    const SizedBox(height: 12),
                    const Text('Assigned Locality:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    TextField(
                      controller: localityController,
                      decoration: const InputDecoration(hintText: 'e.g. State HQ, Mandal Unit'),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(dialogCtx),
                  child: const Text('Cancel'),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFF6B00),
                    foregroundColor: Colors.white,
                  ),
                  onPressed: () async {
                    Navigator.pop(dialogCtx);
                    try {
                      final apiClient = ref.read(apiClientProvider);
                      final resp = await apiClient.post('/admin/rudrasena/${item.id}/status', data: {
                        'status': selectedStatus,
                        'assigned_cadder': cadderController.text.trim(),
                        'assigned_locality': localityController.text.trim(),
                      });

                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(resp.data['message'] ?? 'Rudrasena status updated successfully.'),
                            backgroundColor: Colors.green,
                          ),
                        );
                        ref.invalidate(adminRudrasenaProvider(_searchQuery));
                      }
                    } catch (e) {
                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text('Update failed: $e'),
                            backgroundColor: Colors.red,
                          ),
                        );
                      }
                    }
                  },
                  child: const Text('Save Changes'),
                ),
              ],
            );
          },
        );
      },
    );
  }
}
