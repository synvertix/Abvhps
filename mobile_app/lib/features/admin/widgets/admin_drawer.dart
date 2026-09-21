import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/auth/auth_notifier.dart';

class AdminNavigationDrawer extends ConsumerWidget {
  final String currentRoute;

  const AdminNavigationDrawer({
    super.key,
    this.currentRoute = '/admin/dashboard',
  });

  Future<void> _launchWhatsApp(BuildContext context) async {
    final uri = Uri.parse('https://wa.me/919989980055');
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('WhatsApp is not available on this device.'),
              backgroundColor: Color(0xFFEF4444),
            ),
          );
        }
      }
    } catch (_) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Could not launch WhatsApp support.'),
            backgroundColor: Color(0xFFEF4444),
          ),
        );
      }
    }
  }

  void _handleSignOut(BuildContext context, WidgetRef ref) {
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
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
            ),
          ],
        ),
        content: const Text(
          'Are you sure you want to end your active administrative session? You will need to log in again to manage samiti records.',
          style: TextStyle(fontSize: 13, color: Color(0xFF475569), height: 1.4),
        ),
        actionsPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogCtx).pop(),
            child: const Text('Cancel', style: TextStyle(color: Color(0xFF64748B), fontWeight: FontWeight.w600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE11D48),
              foregroundColor: Colors.white,
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () async {
              Navigator.of(dialogCtx).pop(); // Close dialog
              if (context.mounted) {
                Navigator.of(context).pop(); // Close drawer if open
              }
              await ref.read(authNotifierProvider.notifier).logout();
            },
            child: const Text('Sign Out', style: TextStyle(fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authNotifierProvider);
    final adminName = authState.profile?['name'] ?? 'Admin Commander';
    final adminEmail = authState.profile?['email'] ?? 'admin@abvhps.org';

    return Drawer(
      backgroundColor: const Color(0xFF0F172A),
      elevation: 16,
      child: SafeArea(
        child: Column(
          children: [
            // Drawer Header Profile Block
            Container(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 14),
              decoration: const BoxDecoration(
                color: Color(0xFF0B1426),
                border: Border(bottom: BorderSide(color: Color(0x1AFFFFFF))),
              ),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    padding: const EdgeInsets.all(2),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      border: Border.all(color: const Color(0xFFFF6B00), width: 2),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFFFF6B00).withValues(alpha: 0.3),
                          blurRadius: 8,
                        ),
                      ],
                    ),
                    child: ClipOval(
                      child: Image.asset(
                        'assets/branding/logo_abvhps.png',
                        fit: BoxFit.contain,
                        errorBuilder: (context, error, stackTrace) => const Icon(Icons.shield, color: Color(0xFFFF6B00), size: 24),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Text(
                          'ABVHPS CENTRAL',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 0.8,
                            color: Color(0xFFFF6B00),
                          ),
                        ),
                        const SizedBox(height: 1),
                        const Text(
                          'ADMIN CONTROL DESK',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.5,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          '$adminName • $adminEmail',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontSize: 9,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF94A3B8),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // Scrollable Menu List
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
                children: [
                  // SECTION: MAIN
                  _buildSectionHeader('MAIN'),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.dashboard_rounded,
                    label: 'Dashboard Home',
                    route: '/admin/dashboard',
                    isSelected: currentRoute == '/admin/dashboard',
                  ),

                  const SizedBox(height: 14),

                  // SECTION: WINGS / CONTENT
                  _buildSectionHeader('WINGS & CONTENT'),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.group_outlined,
                    label: 'Our Team',
                    route: '/admin/team',
                    isSelected: currentRoute == '/admin/team',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.volunteer_activism_outlined,
                    label: 'Donations Ledger',
                    route: '/admin/donations',
                    isSelected: currentRoute == '/admin/donations',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.article_outlined,
                    label: 'Blogs Manager',
                    route: '/admin/blogs',
                    isSelected: currentRoute == '/admin/blogs',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.photo_library_outlined,
                    label: 'Media Gallery',
                    route: '/admin/gallery',
                    isSelected: currentRoute == '/admin/gallery',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.handshake_outlined,
                    label: 'Our Support Cores',
                    route: '/admin/support-cores',
                    isSelected: currentRoute == '/admin/support-cores',
                  ),

                  const SizedBox(height: 14),

                  // SECTION: MEMBERSHIP & CADRES
                  _buildSectionHeader('MEMBERSHIP & CADRES'),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.card_membership_outlined,
                    label: 'Approved Membership',
                    route: '/admin/memberships',
                    isSelected: currentRoute == '/admin/memberships',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.pending_actions_outlined,
                    label: 'Pending Membership List',
                    route: '/admin/memberships/pending',
                    isSelected: currentRoute == '/admin/memberships/pending',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.assignment_ind_outlined,
                    label: 'Volunteer Desk',
                    route: '/admin/volunteers',
                    isSelected: currentRoute == '/admin/volunteers',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.event_available_outlined,
                    label: 'Volunteer Events',
                    route: '/admin/volunteer-events',
                    isSelected: currentRoute == '/admin/volunteer-events',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.shield_outlined,
                    label: 'Rudrasena',
                    route: '/admin/rudrasena',
                    isSelected: currentRoute == '/admin/rudrasena',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.location_city_outlined,
                    label: 'Local GP Gateways',
                    route: '/admin/local-gateways',
                    isSelected: currentRoute == '/admin/local-gateways',
                  ),

                  const SizedBox(height: 14),

                  // SECTION: SERVICES & CORES
                  _buildSectionHeader('SERVICES & CORES'),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.school_outlined,
                    label: 'Exams Info Board',
                    route: '/admin/exams',
                    isSelected: currentRoute == '/admin/exams',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.campaign_outlined,
                    label: 'Fundraising Matrices',
                    route: '/admin/fundraising',
                    isSelected: currentRoute == '/admin/fundraising',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.mail_outline,
                    label: 'Contact Forms Audit',
                    route: '/admin/contacts',
                    isSelected: currentRoute == '/admin/contacts',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.receipt_long_outlined,
                    label: 'Tax Certificates',
                    route: '/admin/tax-certificates',
                    isSelected: currentRoute == '/admin/tax-certificates',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.settings_outlined,
                    label: 'Site Global Settings',
                    route: '/admin/settings',
                    isSelected: currentRoute == '/admin/settings',
                  ),
                  _buildDrawerItem(
                    context: context,
                    icon: Icons.view_carousel_outlined,
                    label: 'Banner Management',
                    route: '/admin/banners',
                    isSelected: currentRoute == '/admin/banners',
                  ),

                  const SizedBox(height: 14),

                  // SECTION: SYSTEM
                  _buildSectionHeader('SYSTEM'),
                  Container(
                    margin: const EdgeInsets.symmetric(vertical: 2),
                    child: Material(
                      color: Colors.transparent,
                      borderRadius: BorderRadius.circular(10),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(10),
                        onTap: () {
                          Navigator.of(context).pop();
                          _launchWhatsApp(context);
                        },
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          child: Row(
                            children: [
                              const Icon(Icons.chat_bubble_outline, color: Color(0xFF10B981), size: 20),
                              const SizedBox(width: 12),
                              const Expanded(
                                child: Text(
                                  'WhatsApp Support',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFFE2E8F0),
                                  ),
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: const Color(0xFF10B981).withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text(
                                  'ONLINE',
                                  style: TextStyle(fontSize: 8, fontWeight: FontWeight.w900, color: Color(0xFF10B981)),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                  Container(
                    margin: const EdgeInsets.symmetric(vertical: 2),
                    child: Material(
                      color: Colors.transparent,
                      borderRadius: BorderRadius.circular(10),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(10),
                        onTap: () => _handleSignOut(context, ref),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          child: Row(
                            children: [
                              Icon(Icons.logout_rounded, color: Color(0xFFF43F5E), size: 20),
                              SizedBox(width: 12),
                              Expanded(
                                child: Text(
                                  'Sign Out',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFFF43F5E),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
              ),
            ),

            // Footer info
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: const BoxDecoration(
                color: Color(0xFF0B1426),
                border: Border(top: BorderSide(color: Color(0x1AFFFFFF))),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'ABVHPS Native Admin v2.0',
                      style: TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                    ),
                  ),
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.lock_outline, size: 11, color: Color(0xFF10B981)),
                      SizedBox(width: 4),
                      Text(
                        'SANCTUM SECURE',
                        style: TextStyle(fontSize: 8, fontWeight: FontWeight.w800, color: Color(0xFF10B981)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(6, 4, 6, 6),
      child: Text(
        title,
        style: const TextStyle(
          fontSize: 9,
          fontWeight: FontWeight.w900,
          letterSpacing: 1.0,
          color: Color(0xFF94A3B8),
        ),
      ),
    );
  }

  Widget _buildDrawerItem({
    required BuildContext context,
    required IconData icon,
    required String label,
    required String route,
    required bool isSelected,
  }) {
    return Container(
      margin: const EdgeInsets.symmetric(vertical: 2),
      child: Material(
        color: isSelected ? const Color(0xFFFF6B00) : Colors.transparent,
        borderRadius: BorderRadius.circular(10),
        child: InkWell(
          borderRadius: BorderRadius.circular(10),
          onTap: () {
            Navigator.of(context).pop(); // Close drawer
            if (!isSelected) {
              context.go(route);
            }
          },
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            child: Row(
              children: [
                Icon(
                  icon,
                  color: isSelected ? Colors.white : const Color(0xFF94A3B8),
                  size: 20,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    label,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                      color: isSelected ? Colors.white : const Color(0xFFE2E8F0),
                    ),
                  ),
                ),
                if (isSelected)
                  const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white, size: 12),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
