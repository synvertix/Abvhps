import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/auth/auth_notifier.dart';
import 'admin_dashboard_provider.dart';
import 'models/admin_dashboard_model.dart';
import 'widgets/admin_drawer.dart';

class AdminDashboardScreen extends ConsumerStatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  ConsumerState<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends ConsumerState<AdminDashboardScreen> with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      ref.invalidate(adminDashboardDataProvider);
    }
  }

  String _formatIndianCurrency(double amount) {
    final intValue = amount.round();
    final digits = intValue.toString();
    if (digits.length <= 3) {
      return '₹$digits';
    }
    final lastThree = digits.substring(digits.length - 3);
    final rest = digits.substring(0, digits.length - 3);
    final regex = RegExp(r'(\d+?)(?=(\d{2})+$)');
    final formattedRest = rest.replaceAllMapped(regex, (match) => '${match[1]},');
    return '₹$formattedRest,$lastThree';
  }

  void _handleSignOut() {
    showDialog(
      context: context,
      builder: (dialogCtx) => AlertDialog(
        backgroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.logout_rounded, color: Color(0xFFE11D48), size: 24),
            SizedBox(width: 10),
            Text(
              'Sign Out Admin',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: Color(0xFF0F172A),
              ),
            ),
          ],
        ),
        content: const Text(
          'Are you sure you want to end your active administrative session? You will need to log in again to manage samiti records.',
          style: TextStyle(
            fontSize: 13,
            color: Color(0xFF475569),
            height: 1.4,
          ),
        ),
        actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogCtx).pop(),
            child: const Text(
              'Cancel',
              style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600),
            ),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE11D48),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              Navigator.of(dialogCtx).pop();
              await ref.read(authNotifierProvider.notifier).logout();
            },
            child: const Text('Sign Out', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final dashboardAsync = ref.watch(adminDashboardDataProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF1F5F9),
      drawer: const AdminNavigationDrawer(currentRoute: '/admin/dashboard'),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 1,
        titleSpacing: 0,
        leading: Builder(
          builder: (scaffoldCtx) => IconButton(
            icon: const Icon(Icons.menu, size: 22),
            tooltip: 'Open Admin Navigation',
            onPressed: () => Scaffold.of(scaffoldCtx).openDrawer(),
          ),
        ),
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 8,
              height: 8,
              decoration: const BoxDecoration(
                color: Color(0xFFFF6B00),
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 8),
            const Flexible(
              child: Text(
                'ABVHPS Admin Portal',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                  letterSpacing: 0.3,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, size: 20),
            tooltip: 'Refresh Data',
            onPressed: () {
              ref.invalidate(adminDashboardDataProvider);
            },
          ),
          IconButton(
            icon: const Icon(Icons.logout, size: 20),
            tooltip: 'Sign Out',
            onPressed: _handleSignOut,
          ),
        ],
      ),
      body: RefreshIndicator(
        color: const Color(0xFFFF6B00),
        onRefresh: () async {
          ref.invalidate(adminDashboardDataProvider);
        },
        child: dashboardAsync.when(
          loading: () => const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFFF6B00)),
                ),
                SizedBox(height: 16),
                Text(
                  'Loading administrative dashboard metrics...',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ),
          error: (error, stack) => Center(
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(24),
              child: Card(
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: const BorderSide(color: Color(0xFFFECDD3)),
                ),
                color: const Color(0xFFFFF1F2),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    children: [
                      const Icon(Icons.warning_amber_rounded, size: 40, color: Color(0xFFE11D48)),
                      const SizedBox(height: 12),
                      const Text(
                        'Unable to Load Admin Metrics',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF9F1239),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        error.toString().replaceFirst('Exception: ', ''),
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 12, color: Color(0xFFBE123C)),
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0F172A),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                        icon: const Icon(Icons.refresh, size: 16),
                        label: const Text('Retry Fetch'),
                        onPressed: () {
                          ref.invalidate(adminDashboardDataProvider);
                        },
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          data: (data) => _buildDashboardContent(context, data),
        ),
      ),
    );
  }

  Widget _buildDashboardContent(BuildContext context, AdminDashboardData data) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: [
        // 1. Administrator Identity Card
        _buildAdministratorCard(data.administrator),
        const SizedBox(height: 16),

        // 2. Executive Summary
        _buildSectionHeader('Executive Summary'),
        const SizedBox(height: 8),
        _buildExecutiveSummary(data.summary),
        const SizedBox(height: 20),

        // 3. Organizational Wing Overview
        _buildSectionHeader('Organizational Wing Overview'),
        const SizedBox(height: 8),
        _buildWingsOverview(data.wings),
        const SizedBox(height: 20),

        // 4. Admin Attention — Pending Actions
        _buildSectionHeader('Admin Attention — Pending Actions'),
        const SizedBox(height: 8),
        _buildPendingActions(data.pending),
        const SizedBox(height: 20),

        // 5. System Status
        _buildSectionHeader('System Status'),
        const SizedBox(height: 8),
        _buildSystemStatus(data.system),
        const SizedBox(height: 20),

        // 6. Examination Overview
        _buildSectionHeader('Examination Overview'),
        const SizedBox(height: 8),
        _buildExamsOverview(data.exams),
        const SizedBox(height: 20),

        // 7. Dharma Seva Fundraising
        _buildSectionHeader('Dharma Seva Fundraising'),
        const SizedBox(height: 8),
        _buildFundraisingOverview(data.fundraising),
        const SizedBox(height: 20),

        // 8. Content Overview
        _buildSectionHeader('Content Overview'),
        const SizedBox(height: 8),
        _buildContentOverview(data.content),
        const SizedBox(height: 20),

        // 9. Recent System Activity
        _buildSectionHeader('Recent System Activity'),
        const SizedBox(height: 8),
        _buildRecentActivity(data.recentActivity),
        const SizedBox(height: 20),

        // 10. Quick Actions
        _buildSectionHeader('Quick Actions'),
        const SizedBox(height: 8),
        _buildQuickActions(),
        const SizedBox(height: 32),
      ],
    );
  }

  Widget _buildSectionHeader(String title) {
    return Row(
      children: [
        Container(
          width: 4,
          height: 14,
          decoration: BoxDecoration(
            color: const Color(0xFFFF6B00),
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            title.toUpperCase(),
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w900,
              letterSpacing: 0.8,
              color: Color(0xFF64748B),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildAdministratorCard(AdminUser admin) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x05000000),
            blurRadius: 4,
            offset: Offset(0, 2),
          ),
        ],
      ),
      padding: const EdgeInsets.all(14),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: const Color(0xFF0F172A),
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Icon(Icons.shield_outlined, color: Color(0xFFFF6B00), size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  admin.name.isNotEmpty ? admin.name : 'Local Admin',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                if (admin.email.isNotEmpty)
                  Text(
                    admin.email,
                    style: const TextStyle(
                      fontSize: 11,
                      color: Color(0xFF64748B),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                const SizedBox(height: 4),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFF7ED),
                    borderRadius: BorderRadius.circular(4),
                    border: Border.all(color: const Color(0xFFFFEDD5)),
                  ),
                  child: const Text(
                    'AUTHENTICATED SYSTEM ADMINISTRATOR',
                    style: TextStyle(
                      fontSize: 8,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFFE65100),
                      letterSpacing: 0.4,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildExecutiveSummary(AdminSummary summary) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final cardWidth = (constraints.maxWidth - 12) / 2;
        return Wrap(
          spacing: 12,
          runSpacing: 12,
          children: [
            _buildMetricCard(
              width: cardWidth,
              topColor: const Color(0xFFFF6B00),
              label: 'TOTAL PROFILES',
              value: '${summary.totalProfiles}',
              subtitle: 'Registered memberships',
            ),
            _buildMetricCard(
              width: cardWidth,
              topColor: const Color(0xFF2563EB),
              label: 'VOLUNTEERS',
              value: '${summary.volunteers}',
              subtitle: 'Approved cadre members',
            ),
            _buildMetricCard(
              width: cardWidth,
              topColor: summary.pendingActions > 0 ? const Color(0xFFF59E0B) : const Color(0xFF94A3B8),
              label: 'PENDING ACTIONS',
              value: '${summary.pendingActions}',
              subtitle: 'Require admin review',
            ),
            _buildMetricCard(
              width: cardWidth,
              topColor: const Color(0xFFD97706),
              label: 'FUNDS RAISED',
              value: _formatIndianCurrency(summary.fundsRaised),
              subtitle: 'Consolidated campaigns',
            ),
          ],
        );
      },
    );
  }

  Widget _buildMetricCard({
    required double width,
    required Color topColor,
    required String label,
    required String value,
    required String subtitle,
  }) {
    return Container(
      width: width,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x04000000),
            blurRadius: 3,
            offset: Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            height: 3,
            decoration: BoxDecoration(
              color: topColor,
              borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF94A3B8),
                    letterSpacing: 0.6,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                    fontFamily: 'monospace',
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  subtitle,
                  style: const TextStyle(
                    fontSize: 9,
                    color: Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWingsOverview(AdminWings wings) {
    final wingsList = [
      {'icon': '👥', 'name': 'Central Base', 'count': wings.centralBase, 'desc': 'Verified Profiles', 'color': const Color(0xFF0F172A)},
      {'icon': '🛡️', 'name': 'Rudra Sena', 'count': wings.rudraSena, 'desc': 'Command Force', 'color': const Color(0xFFFF6B00)},
      {'icon': '🪘', 'name': 'Kala Brundham', 'count': wings.kalaBrundham, 'desc': 'Cultural Artists', 'color': const Color(0xFF4338CA)},
      {'icon': '🌿', 'name': 'Grama Seva Dal', 'count': wings.gramaSevaDal, 'desc': 'Village Charters', 'color': const Color(0xFF047857)},
      {'icon': '🌾', 'name': 'Organic Farmers', 'count': wings.organicFarmers, 'desc': 'Nature Certified', 'color': const Color(0xFF15803D)},
      {'icon': '🪔', 'name': 'Dharma Seva', 'count': wings.dharmaSeva, 'desc': 'Active Campaigns', 'color': const Color(0xFFB45309)},
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final cardWidth = (constraints.maxWidth - 16) / 3;
        return Wrap(
          spacing: 8,
          runSpacing: 8,
          children: wingsList.map((wing) {
            return Container(
              width: cardWidth,
              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 6),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                children: [
                  Text(wing['icon'] as String, style: const TextStyle(fontSize: 20)),
                  const SizedBox(height: 4),
                  Text(
                    wing['name'] as String,
                    textAlign: TextAlign.center,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 9,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF64748B),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${wing['count']}',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      fontFamily: 'monospace',
                      color: wing['color'] as Color,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    wing['desc'] as String,
                    textAlign: TextAlign.center,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 7,
                      color: Color(0xFF94A3B8),
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
        );
      },
    );
  }

  Widget _buildPendingActions(AdminPending pending) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          _buildPendingRow('Volunteers awaiting approval', pending.volunteers, pending.volunteers > 0),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          _buildPendingRow('Memberships awaiting review', pending.memberships, pending.memberships > 0),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          _buildPendingRow('Exam applications received', pending.examApplications, false),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          _buildPendingRow('Exam results published', pending.resultsPublished, false),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          _buildPendingRow('Active fundraising campaigns', pending.activeCampaigns, false),
        ],
      ),
    );
  }

  Widget _buildPendingRow(String label, int count, bool highlight) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Color(0xFF334155),
              ),
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
            decoration: BoxDecoration(
              color: highlight ? const Color(0xFFFEF3C7) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              '$count',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w900,
                fontFamily: 'monospace',
                color: highlight ? const Color(0xFFD97706) : const Color(0xFF64748B),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSystemStatus(AdminSystem system) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      child: Column(
        children: [
          _buildStatusRow('Application', system.application, system.application == 'Running'),
          const Divider(height: 1, color: Color(0xFFF8FAFC)),
          _buildStatusRow('Database', system.database, system.databaseStatus == 'ok'),
          const Divider(height: 1, color: Color(0xFFF8FAFC)),
          _buildStatusRow('Storage', system.storage, system.storageStatus == 'ok'),
          const Divider(height: 1, color: Color(0xFFF8FAFC)),
          _buildStatusRow('Total Records', '${system.totalRecords} entries', true),
          const Divider(height: 1, color: Color(0xFFF8FAFC)),
          _buildStatusRow('Total Exams', '${system.totalExams} configured', true),
          const Divider(height: 1, color: Color(0xFFF8FAFC)),
          _buildStatusRow('Active Campaigns', '${system.activeCampaigns} live', true),
        ],
      ),
    );
  }

  Widget _buildStatusRow(String label, String value, bool isOk) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 7),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Color(0xFF475569),
              ),
            ),
          ),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                value,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF64748B),
                ),
              ),
              const SizedBox(width: 6),
              Container(
                width: 7,
                height: 7,
                decoration: BoxDecoration(
                  color: isOk ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                  shape: BoxShape.circle,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildExamsOverview(AdminExams exams) {
    return _buildOverviewContainer(
      icon: '📝',
      title: 'Examination Overview',
      items: [
        {'label': 'Total Exams', 'value': '${exams.total}', 'color': const Color(0xFF0F172A)},
        {'label': 'Active Exams', 'value': '${exams.active}', 'color': const Color(0xFF10B981)},
        {'label': 'Total Applications', 'value': '${exams.applications}', 'color': const Color(0xFF2563EB)},
        {'label': 'Results Published', 'value': '${exams.resultsPublished}', 'color': const Color(0xFFFF6B00)},
      ],
    );
  }

  Widget _buildFundraisingOverview(AdminFundraising fundraising) {
    return _buildOverviewContainer(
      icon: '🪔',
      title: 'Dharma Seva Fundraising',
      items: [
        {'label': 'Total Campaigns', 'value': '${fundraising.totalCampaigns}', 'color': const Color(0xFF0F172A)},
        {'label': 'Active Campaigns', 'value': '${fundraising.activeCampaigns}', 'color': const Color(0xFF10B981)},
        {'label': 'Total Donors', 'value': '${fundraising.totalDonors}', 'color': const Color(0xFF2563EB)},
        {'label': 'Amount Raised', 'value': _formatIndianCurrency(fundraising.amountRaised), 'color': const Color(0xFFD97706)},
      ],
    );
  }

  Widget _buildContentOverview(AdminContent content) {
    return _buildOverviewContainer(
      icon: '📰',
      title: 'Content Overview',
      items: [
        {'label': 'Total Blogs', 'value': '${content.blogs}', 'color': const Color(0xFF0F172A)},
        {'label': 'Published Blogs', 'value': '${content.publishedBlogs}', 'color': const Color(0xFF10B981)},
        {'label': 'Gallery Media', 'value': '${content.galleryMedia}', 'color': const Color(0xFF2563EB)},
        {'label': 'Support Cores', 'value': '${content.supportCores}', 'color': const Color(0xFFFF6B00)},
      ],
    );
  }

  Widget _buildOverviewContainer({
    required String icon,
    required String title,
    required List<Map<String, dynamic>> items,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(icon, style: const TextStyle(fontSize: 16)),
              const SizedBox(width: 6),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF0F172A),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ...items.map((item) {
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    item['label'] as String,
                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w500),
                  ),
                  Text(
                    item['value'] as String,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                      fontFamily: 'monospace',
                      color: item['color'] as Color,
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildRecentActivity(List<AdminActivity> activities) {
    if (activities.isEmpty) {
      return Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        padding: const EdgeInsets.all(24),
        child: const Center(
          child: Column(
            children: [
              Text('📋', style: TextStyle(fontSize: 24)),
              SizedBox(height: 8),
              Text(
                'No recent activity records found.',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
              ),
              SizedBox(height: 2),
              Text(
                'Activity will appear here as admin actions are performed.',
                style: TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
              ),
            ],
          ),
        ),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: activities.length,
        separatorBuilder: (context, index) => const Divider(height: 1, color: Color(0xFFF1F5F9)),
        itemBuilder: (context, index) {
          final act = activities[index];
          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 6,
                  height: 6,
                  margin: const EdgeInsets.only(top: 5),
                  decoration: const BoxDecoration(
                    color: Color(0xFFFF6B00),
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        act.action.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                      if (act.actorIdentifier.isNotEmpty || act.targetType.isNotEmpty)
                        Text(
                          [
                            if (act.actorIdentifier.isNotEmpty) act.actorIdentifier,
                            if (act.targetType.isNotEmpty) '${act.targetType}${act.targetId != null ? " #${act.targetId}" : ""}',
                          ].join(' · '),
                          style: const TextStyle(fontSize: 10, color: Color(0xFF94A3B8)),
                        ),
                    ],
                  ),
                ),
                if (act.formattedTime.isNotEmpty)
                  Text(
                    act.formattedTime,
                    style: const TextStyle(
                      fontSize: 10,
                      fontFamily: 'monospace',
                      color: Color(0xFF94A3B8),
                    ),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildQuickActions() {
    final actions = [
      {'icon': '⏳', 'title': 'Review Memberships', 'route': null},
      {'icon': '🤝', 'title': 'Review Volunteers', 'route': null},
      {'icon': '📝', 'title': 'Add New Exam', 'route': null},
      {'icon': '🪔', 'title': 'New Campaign', 'route': null},
      {'icon': '🖼️', 'title': 'Upload Media', 'route': null},
      {'icon': '📰', 'title': 'Manage Blogs', 'route': null},
      {'icon': '🔱', 'title': 'Rudra Sena Roster', 'route': null},
      {'icon': '🚩', 'title': 'Page Banners', 'route': null},
      {'icon': '⚙️', 'title': 'Site Settings', 'route': null},
    ];

    return LayoutBuilder(
      builder: (context, constraints) {
        final itemWidth = (constraints.maxWidth - 8) / 2;
        return Wrap(
          spacing: 8,
          runSpacing: 8,
          children: actions.map((act) {
            return InkWell(
              onTap: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text('${act['title']} is managed via the central ABVHPS Web Portal.'),
                    duration: const Duration(seconds: 2),
                    behavior: SnackBarBehavior.floating,
                  ),
                );
              },
              borderRadius: BorderRadius.circular(10),
              child: Container(
                width: itemWidth,
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Row(
                  children: [
                    Text(act['icon'] as String, style: const TextStyle(fontSize: 16)),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        act['title'] as String,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF334155),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        );
      },
    );
  }
}
