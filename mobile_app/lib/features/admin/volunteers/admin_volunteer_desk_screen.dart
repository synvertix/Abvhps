import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/auth/auth_notifier.dart';
import '../repositories/admin_b_repositories.dart';
import '../models/admin_volunteer_model.dart';
import '../widgets/admin_drawer.dart';

class AdminVolunteerDeskScreen extends ConsumerStatefulWidget {
  const AdminVolunteerDeskScreen({super.key});

  @override
  ConsumerState<AdminVolunteerDeskScreen> createState() => _AdminVolunteerDeskScreenState();
}

class _AdminVolunteerDeskScreenState extends ConsumerState<AdminVolunteerDeskScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final volunteersAsync = ref.watch(adminVolunteersProvider(_searchQuery));

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'VOLUNTEER DESK',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 0.5),
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(adminVolunteersProvider(_searchQuery)),
          ),
        ],
      ),
      drawer: const AdminNavigationDrawer(currentRoute: '/admin/volunteers'),
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
                  hintText: 'Search volunteer ID, name, mobile, cadre...',
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
            // Roster List
            Expanded(
              child: volunteersAsync.when(
                data: (items) {
                  if (items.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.people_outline, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 16),
                          const Text(
                            'No volunteers found in desk roster.',
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
                      return _buildVolunteerCard(context, item);
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
                          'Failed to load volunteers: $err',
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: () => ref.invalidate(adminVolunteersProvider(_searchQuery)),
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

  Widget _buildVolunteerCard(BuildContext context, AdminVolunteer item) {
    final isApproved = item.status == 'approved';
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
                  backgroundColor: const Color(0xFF0F172A).withValues(alpha: 0.1),
                  child: Text(
                    item.name.isNotEmpty ? item.name[0].toUpperCase() : 'V',
                    style: const TextStyle(color: Color(0xFF0F172A), fontWeight: FontWeight.bold, fontSize: 18),
                  ),
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
                            'VOL ID: ${item.volunteerId}',
                            style: const TextStyle(color: Color(0xFFFF6B00), fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '(${item.cadre})',
                            style: const TextStyle(color: Color(0xFF64748B), fontSize: 12, fontStyle: FontStyle.italic),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isApproved ? Colors.green.shade50 : (isPending ? Colors.amber.shade50 : Colors.red.shade50),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(
                      color: isApproved ? Colors.green.shade300 : (isPending ? Colors.amber.shade300 : Colors.red.shade300),
                    ),
                  ),
                  child: Text(
                    item.status.toUpperCase(),
                    style: TextStyle(
                      color: isApproved ? Colors.green : (isPending ? Colors.amber.shade800 : Colors.red),
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
                Text(item.phone, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                const Spacer(),
                const Icon(Icons.school, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Text(item.qualification, style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                OutlinedButton.icon(
                  onPressed: () => _showCadreUpdateDialog(context, item),
                  icon: const Icon(Icons.edit, size: 14),
                  label: const Text('Update Cadre / Status'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFF0F172A),
                    side: const BorderSide(color: Color(0xFF0F172A)),
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

  void _showCadreUpdateDialog(BuildContext context, AdminVolunteer item) {
    String selectedStatus = item.status;
    String cadreLevel = 'volunteer';
    final cadreController = TextEditingController(text: item.cadre);
    final localityController = TextEditingController(text: item.district ?? '');

    showDialog(
      context: context,
      builder: (dialogCtx) {
        return StatefulBuilder(
          builder: (context, setDialogState) {
            return AlertDialog(
              title: Text('Update Volunteer #${item.volunteerId}'),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Status:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    DropdownButton<String>(
                      value: selectedStatus,
                      isExpanded: true,
                      items: const [
                        DropdownMenuItem(value: 'approved', child: Text('Approved')),
                        DropdownMenuItem(value: 'pending', child: Text('Pending')),
                        DropdownMenuItem(value: 'rejected', child: Text('Rejected')),
                      ],
                      onChanged: (val) {
                        if (val != null) setDialogState(() => selectedStatus = val);
                      },
                    ),
                    const SizedBox(height: 12),
                    const Text('Cadre Title:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    TextField(
                      controller: cadreController,
                      decoration: const InputDecoration(hintText: 'e.g. Mandal President, Volunteer'),
                    ),
                    const SizedBox(height: 12),
                    const Text('Jurisdiction Locality:', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    TextField(
                      controller: localityController,
                      decoration: const InputDecoration(hintText: 'e.g. Hyderabad District'),
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
                      final resp = await apiClient.post('/admin/volunteers/${item.id}/cadre', data: {
                        'status': selectedStatus,
                        'cadre_level': cadreLevel,
                        'cadre': cadreController.text.trim(),
                        'locality': localityController.text.trim(),
                      });

                      if (context.mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(resp.data['message'] ?? 'Volunteer cadre updated successfully.'),
                            backgroundColor: Colors.green,
                          ),
                        );
                        ref.invalidate(adminVolunteersProvider(_searchQuery));
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
