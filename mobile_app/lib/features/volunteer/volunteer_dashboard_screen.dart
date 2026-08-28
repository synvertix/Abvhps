import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';
import '../../core/theme/app_theme.dart';

final volunteerDashboardDataProvider = FutureProvider<Map<String, dynamic>>((ref) async {
  final client = ref.watch(apiClientProvider);
  final response = await client.get('/volunteer/dashboard');
  if (response.data['success'] == true) {
    return response.data['data'] as Map<String, dynamic>;
  }
  throw Exception('Failed to load dashboard data');
});

class VolunteerDashboardScreen extends ConsumerWidget {
  const VolunteerDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authNotifierProvider);
    final dashboardAsync = ref.watch(volunteerDashboardDataProvider);
    final profile = authState.profile ?? {};

    return Scaffold(
      appBar: AppBar(
        title: const Text('Volunteer Portal'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Refresh',
            onPressed: () => ref.refresh(volunteerDashboardDataProvider),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Logout',
            onPressed: () => ref.read(authNotifierProvider.notifier).logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => ref.refresh(volunteerDashboardDataProvider),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Profile Header Card
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 32,
                        backgroundColor: AppTheme.primaryOrange.withValues(alpha: 0.15),
                        child: const Icon(Icons.person, size: 36, color: AppTheme.primaryOrange),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              profile['full_name']?.toString() ?? 'Volunteer',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'ID: #${profile['volunteer_id'] ?? profile['volunteer_login_id'] ?? '—'}',
                              style: const TextStyle(color: AppTheme.primaryOrange, fontWeight: FontWeight.w600),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              profile['cadre_label']?.toString() ?? profile['cadre']?.toString() ?? 'Volunteer',
                              style: const TextStyle(color: AppTheme.textSecondary, fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Jurisdiction / Scope
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(Icons.location_on, color: AppTheme.primaryOrange, size: 20),
                          SizedBox(width: 8),
                          Text('Assigned Jurisdiction', style: TextStyle(fontWeight: FontWeight.bold)),
                        ],
                      ),
                      const Divider(height: 20),
                      Text(
                        profile['jurisdiction_summary']?.toString() ?? 'General Volunteer',
                        style: const TextStyle(fontSize: 15),
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 16),

              // Statistics Section
              dashboardAsync.when(
                data: (data) {
                  final stats = data['stats'] as Map<String, dynamic>? ?? {};
                  final isPresident = data['is_president'] == true;

                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Text(
                        'Event Statistics',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          _buildStatCard(
                            title: 'Total Events',
                            value: stats['total_events']?.toString() ?? '0',
                            icon: Icons.event,
                            color: Colors.blue,
                          ),
                          const SizedBox(width: 8),
                          _buildStatCard(
                            title: 'Conducted',
                            value: stats['events_conducted']?.toString() ?? '0',
                            icon: Icons.check_circle,
                            color: Colors.green,
                          ),
                          const SizedBox(width: 8),
                          _buildStatCard(
                            title: 'Upcoming',
                            value: stats['upcoming_events']?.toString() ?? '0',
                            icon: Icons.upcoming,
                            color: Colors.orange,
                          ),
                        ],
                      ),
                      if (isPresident) ...[
                        const SizedBox(height: 16),
                        const Text(
                          'Subordinate Units',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                        ),
                        const SizedBox(height: 8),
                        Card(
                          child: Padding(
                            padding: const EdgeInsets.all(16.0),
                            child: Text(
                              'President cadre verified. Subordinate directory loaded (${(data['subordinate_units'] as List?)?.length ?? 0} units).',
                              style: const TextStyle(color: AppTheme.textSecondary),
                            ),
                          ),
                        ),
                      ],
                    ],
                  );
                },
                loading: () => const Center(
                  child: Padding(
                    padding: EdgeInsets.all(24.0),
                    child: CircularProgressIndicator(),
                  ),
                ),
                error: (err, _) => Card(
                  color: Colors.red.shade50,
                  child: Padding(
                    padding: const EdgeInsets.all(16.0),
                    child: Text('Could not load statistics: $err'),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16.0, horizontal: 8.0),
          child: Column(
            children: [
              Icon(icon, color: color, size: 28),
              const SizedBox(height: 8),
              Text(
                value,
                style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
              Text(
                title,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
