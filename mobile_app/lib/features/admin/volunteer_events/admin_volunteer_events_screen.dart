import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../repositories/admin_b_repositories.dart';
import '../models/admin_volunteer_event_model.dart';
import '../widgets/admin_drawer.dart';

class AdminVolunteerEventsScreen extends ConsumerWidget {
  const AdminVolunteerEventsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventsAsync = ref.watch(adminVolunteerEventsProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'VOLUNTEER EVENTS',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, letterSpacing: 0.5),
        ),
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => ref.invalidate(adminVolunteerEventsProvider),
          ),
        ],
      ),
      drawer: const AdminNavigationDrawer(currentRoute: '/admin/volunteer-events'),
      body: Container(
        color: const Color(0xFFF8FAFC),
        child: eventsAsync.when(
          data: (items) {
            if (items.isEmpty) {
              return Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.event_available_outlined, size: 64, color: Colors.grey.shade400),
                    const SizedBox(height: 16),
                    const Text(
                      'No volunteer events recorded.',
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
                return _buildEventCard(context, item);
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
                    'Failed to load volunteer events: $err',
                    textAlign: TextAlign.center,
                    style: const TextStyle(color: Color(0xFF64748B)),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: () => ref.invalidate(adminVolunteerEventsProvider),
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
    );
  }

  Widget _buildEventCard(BuildContext context, AdminVolunteerEvent item) {
    final isCompleted = item.status == 'completed';

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
                  radius: 20,
                  backgroundColor: const Color(0xFFFF6B00).withValues(alpha: 0.12),
                  child: const Icon(Icons.event, color: Color(0xFFFF6B00), size: 20),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.title,
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Type: ${item.eventType}',
                        style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isCompleted ? Colors.blue.shade50 : Colors.amber.shade50,
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: isCompleted ? Colors.blue.shade300 : Colors.amber.shade300),
                  ),
                  child: Text(
                    item.status.toUpperCase(),
                    style: TextStyle(
                      color: isCompleted ? Colors.blue : Colors.amber.shade800,
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
                const Icon(Icons.location_on, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Venue: ${item.venue}${item.district != null ? " (${item.district})" : ""}',
                    style: const TextStyle(fontSize: 13, color: Color(0xFF334155)),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                const Icon(Icons.calendar_month, size: 14, color: Color(0xFF64748B)),
                const SizedBox(width: 6),
                Text('Date: ${item.eventDate}', style: const TextStyle(fontSize: 13, color: Color(0xFF334155))),
                const Spacer(),
                const Icon(Icons.people, size: 14, color: Color(0xFFFF6B00)),
                const SizedBox(width: 6),
                Text(
                  '${item.beneficiariesCount} Beneficiaries',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFFFF6B00)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
