import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../../core/config/app_config.dart';
import '../../../core/theme/app_theme.dart';

class PublicDrawer extends StatefulWidget {
  final Map<String, dynamic>? contact;

  const PublicDrawer({
    super.key,
    this.contact,
  });

  @override
  State<PublicDrawer> createState() => _PublicDrawerState();
}

class _PublicDrawerState extends State<PublicDrawer> {
  bool _examsExpanded = false;
  bool _wingsExpanded = false;

  String _getCurrentRoute(BuildContext context) {
    try {
      return GoRouterState.of(context).matchedLocation;
    } catch (_) {
      return '/';
    }
  }

  void _showLoginPortalsModal(BuildContext context) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          clipBehavior: Clip.antiAlias,
          child: SafeArea(
            top: false,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Modal Header (Navy #0B1426 + Orange line)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                    decoration: const BoxDecoration(
                      color: Color(0xFF0B1426),
                      border: Border(
                        bottom: BorderSide(color: AppTheme.primaryOrange, width: 2),
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          padding: const EdgeInsets.all(2),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            shape: BoxShape.circle,
                            border: Border.all(color: AppTheme.primaryOrange, width: 1.5),
                          ),
                          child: ClipOval(
                            child: Image.asset(
                              'assets/branding/logo_abvhps.png',
                              fit: BoxFit.contain,
                              errorBuilder: (context, error, stackTrace) => const Icon(
                                Icons.shield,
                                color: AppTheme.primaryOrange,
                                size: 20,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'SELECT LOGIN PORTAL',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 14,
                                  letterSpacing: 0.8,
                                ),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
                                style: TextStyle(
                                  color: Color(0xFFFED7AA),
                                  fontSize: 10,
                                  fontWeight: FontWeight.w600,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: () => Navigator.of(sheetContext).pop(),
                          icon: Container(
                            width: 32,
                            height: 32,
                            decoration: BoxDecoration(
                              color: Colors.white12,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(
                              Icons.close,
                              color: Colors.white,
                              size: 18,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Portal Selection Cards
                  Padding(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Card 1: Member Login (OTP)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE5E7EB), width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withValues(alpha: 0.04),
                                blurRadius: 8,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 42,
                                    height: 42,
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFFEDD5),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Icon(
                                      Icons.person,
                                      color: AppTheme.primaryOrange,
                                      size: 22,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'MEMBER LOGIN',
                                          style: TextStyle(
                                            color: Color(0xFF1E293B),
                                            fontWeight: FontWeight.w900,
                                            fontSize: 13,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                        Text(
                                          'Registered members access via OTP',
                                          style: TextStyle(
                                            color: Color(0xFF64748B),
                                            fontSize: 11,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 14),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  key: const Key('drawer_member_login_button'),
                                  onPressed: () {
                                    Navigator.of(sheetContext).pop();
                                    Navigator.of(context).pop();
                                    context.push('/login?type=member');
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: const Color(0xFF111C2E),
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    elevation: 0,
                                  ),
                                  child: const Text(
                                    'LOGIN WITH OTP  →',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 14),

                        // Card 2: Volunteer Login
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFFED7AA), width: 2),
                            boxShadow: [
                              BoxShadow(
                                color: AppTheme.primaryOrange.withValues(alpha: 0.08),
                                blurRadius: 10,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    width: 42,
                                    height: 42,
                                    decoration: BoxDecoration(
                                      color: AppTheme.primaryOrange,
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: const Icon(
                                      Icons.volunteer_activism,
                                      color: Colors.white,
                                      size: 22,
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  const Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          'VOLUNTEER LOGIN',
                                          style: TextStyle(
                                            color: AppTheme.primaryOrange,
                                            fontWeight: FontWeight.w900,
                                            fontSize: 13,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                        Text(
                                          'Approved ABVHPS cadre & coordinators',
                                          style: TextStyle(
                                            color: Color(0xFF64748B),
                                            fontSize: 11,
                                            fontWeight: FontWeight.w500,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 14),
                              SizedBox(
                                width: double.infinity,
                                child: ElevatedButton(
                                  key: const Key('drawer_volunteer_login_button'),
                                  onPressed: () {
                                    Navigator.of(sheetContext).pop();
                                    Navigator.of(context).pop();
                                    context.push('/login?type=volunteer');
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppTheme.primaryOrange,
                                    foregroundColor: Colors.white,
                                    padding: const EdgeInsets.symmetric(vertical: 12),
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    elevation: 2,
                                  ),
                                  child: const Text(
                                    'LOGIN AS VOLUNTEER  →',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w900,
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Modal Footer
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 20),
                    decoration: const BoxDecoration(
                      color: Color(0xFFF8FAFC),
                      border: Border(
                        top: BorderSide(color: Color(0xFFE2E8F0), width: 1),
                      ),
                    ),
                    child: const Text(
                      'Need help accessing your portal? Contact info@abvhps.org',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontSize: 11,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final phone = widget.contact?['phone'] ?? AppConfig.defaultPhone;
    final email = widget.contact?['email'] ?? AppConfig.defaultEmail;
    final currentRoute = _getCurrentRoute(context);

    // Responsive width matching website w-[min(360px,88vw)]
    final screenWidth = MediaQuery.sizeOf(context).width;
    final drawerWidth = math.min(360.0, screenWidth * 0.88);

    return Drawer(
      width: drawerWidth,
      backgroundColor: const Color(0xFF161E2E), // Exact website mobile drawer background
      surfaceTintColor: Colors.transparent,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.zero,
      ),
      child: SafeArea(
        top: true,
        bottom: true,
        child: Column(
          children: [
            // ==========================================
            // 1. DRAWER HEADER (Opaque #0B1426, min-h: 96px)
            // ==========================================
            Container(
              constraints: const BoxConstraints(minHeight: 96),
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              decoration: const BoxDecoration(
                color: Color(0xFF0B1426),
                border: Border(
                  bottom: BorderSide(color: Color(0x1AFFFFFF), width: 1),
                ),
              ),
              child: Row(
                children: [
                  // Logo + Title block
                  Expanded(
                    child: InkWell(
                      onTap: () {
                        Navigator.of(context).pop();
                        if (currentRoute != '/') {
                          context.go('/');
                        }
                      },
                      borderRadius: BorderRadius.circular(8),
                      child: Row(
                        children: [
                          Container(
                            width: 48,
                            height: 48,
                            padding: const EdgeInsets.all(2),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppTheme.primaryOrange, width: 2),
                              boxShadow: const [
                                BoxShadow(
                                  color: Colors.black26,
                                  blurRadius: 4,
                                  offset: Offset(0, 2),
                                ),
                              ],
                            ),
                            child: ClipOval(
                              child: Image.asset(
                                'assets/branding/logo_abvhps.png',
                                fit: BoxFit.contain,
                                errorBuilder: (context, error, stackTrace) => const Icon(
                                  Icons.shield,
                                  color: AppTheme.primaryOrange,
                                  size: 24,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          const Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text(
                                  'ABVHPS CENTRAL',
                                  style: TextStyle(
                                    color: AppTheme.primaryOrange,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 13,
                                    letterSpacing: 0.8,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                SizedBox(height: 2),
                                Text(
                                  'Parirakshana Samiti',
                                  style: TextStyle(
                                    color: Color(0xFF9CA3AF),
                                    fontWeight: FontWeight.w700,
                                    fontSize: 9,
                                    letterSpacing: 0.5,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(width: 8),

                  // 58x58 Square Close Button with Bold Orange Outline
                  Semantics(
                    button: true,
                    label: 'Close navigation',
                    child: InkWell(
                      key: const Key('drawer_close_button'),
                      onTap: () => Navigator.of(context).pop(),
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        width: 58,
                        height: 58,
                        decoration: BoxDecoration(
                          color: const Color(0xFF111C2E),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppTheme.primaryOrange, width: 2.5),
                          boxShadow: const [
                            BoxShadow(
                              color: Colors.black38,
                              blurRadius: 8,
                              offset: Offset(0, 2),
                            ),
                          ],
                        ),
                        child: const Center(
                          child: Icon(
                            Icons.close,
                            color: Colors.white,
                            size: 24,
                            weight: 2.5,
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ==========================================
            // 2. SCROLLABLE NAVIGATION LIST
            // ==========================================
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                physics: const ClampingScrollPhysics(),
                children: [
                  // SECTION 1: EXPLORE SAMITI
                  _buildSectionHeading('EXPLORE SAMITI', isFirst: true),
                  _buildNavItem(
                    title: 'HOME',
                    key: const Key('drawer_nav_home'),
                    isActive: currentRoute == '/',
                    onTap: () {
                      Navigator.of(context).pop();
                      context.go('/');
                    },
                  ),
                  _buildNavItem(
                    title: 'ABOUT US',
                    key: const Key('drawer_nav_about'),
                    isActive: currentRoute.startsWith('/about'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/about');
                    },
                  ),
                  _buildNavItem(
                    title: 'OUR TEAM',
                    key: const Key('drawer_nav_team'),
                    isActive: currentRoute.startsWith('/team'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/team');
                    },
                  ),
                  _buildNavItem(
                    title: 'MEDIA GALLERY',
                    key: const Key('drawer_nav_gallery'),
                    isActive: currentRoute.startsWith('/gallery'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/gallery');
                    },
                  ),
                  _buildNavItem(
                    title: 'MEMBERSHIP PORTAL',
                    key: const Key('drawer_nav_membership'),
                    isActive: currentRoute.startsWith('/login') && currentRoute.contains('type=member'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/login?type=member');
                    },
                  ),
                  _buildNavItem(
                    title: 'VOLUNTEER CADRE',
                    key: const Key('drawer_nav_volunteer_cadre'),
                    isActive: currentRoute.startsWith('/login') && currentRoute.contains('type=volunteer'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/login?type=volunteer');
                    },
                  ),

                  // SECTION 2: ACADEMICS & SERVICES
                  _buildSectionHeading('ACADEMICS & SERVICES'),
                  _buildExpandableItem(
                    title: 'EXAMS INFO & RESULTS',
                    key: const Key('drawer_accordion_exams'),
                    isExpanded: _examsExpanded,
                    onToggle: () => setState(() => _examsExpanded = !_examsExpanded),
                    children: [
                      _buildSubItem(
                        'EXAMS NOTICE BOARD',
                        key: const Key('drawer_sub_exams_board'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/exams/board');
                        },
                      ),
                      _buildSubItem(
                        'APPLY ONLINE',
                        key: const Key('drawer_sub_exams_apply'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/exams/board');
                        },
                      ),
                      _buildSubItem(
                        'VIEW RESULTS',
                        key: const Key('drawer_sub_exams_results'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/exams/results');
                        },
                      ),
                    ],
                  ),

                  // SECTION 3: OUR WINGS SUBSYSTEMS
                  _buildSectionHeading('OUR WINGS SUBSYSTEMS'),
                  _buildExpandableItem(
                    title: 'OUR WINGS',
                    key: const Key('drawer_accordion_wings'),
                    isExpanded: _wingsExpanded,
                    onToggle: () => setState(() => _wingsExpanded = !_wingsExpanded),
                    children: [
                      _buildSubItem(
                        'RUDRASENA DAL',
                        key: const Key('drawer_sub_wing_rudrasena'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/wings/rudrasena');
                        },
                      ),
                      _buildSubItem(
                        'KALA BRUNDAM',
                        key: const Key('drawer_sub_wing_kala_brundam'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/wings/kala-brundam');
                        },
                      ),
                      _buildSubItem(
                        'GRAMA SEVA DAL',
                        key: const Key('drawer_sub_wing_grama_seva'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/wings/grama-seva-dal');
                        },
                      ),
                      _buildSubItem(
                        'ORGANIC FARMERS',
                        key: const Key('drawer_sub_wing_organic_farmers'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/wings/organic-farmers');
                        },
                      ),
                    ],
                  ),

                  // SECTION 4: COMMUNITY & SUPPORT
                  _buildSectionHeading('COMMUNITY & SUPPORT'),
                  _buildNavItem(
                    title: 'FUNDRAISE CAMPAIGNS',
                    key: const Key('drawer_nav_campaigns'),
                    isActive: currentRoute.startsWith('/campaigns'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/campaigns');
                    },
                  ),
                  _buildNavItem(
                    title: 'BLOGS & UPDATES',
                    key: const Key('drawer_nav_blogs'),
                    isActive: currentRoute.startsWith('/blogs'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/blogs');
                    },
                  ),
                  _buildNavItem(
                    title: 'CONTACT US',
                    key: const Key('drawer_nav_contact'),
                    isActive: currentRoute.startsWith('/contact'),
                    onTap: () {
                      Navigator.of(context).pop();
                      context.push('/contact');
                    },
                  ),

                  const SizedBox(height: 8),

                  // ==========================================
                  // 3. CALL TO ACTION (CTA) BUTTONS
                  // ==========================================
                  // Button 1: LOGIN PORTALS (Website Exact: Dark navy #111C2E with orange outline)
                  Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF111C2E),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: AppTheme.primaryOrange.withValues(alpha: 0.6),
                        width: 1.5,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.2),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        key: const Key('drawer_login_portals_button'),
                        onTap: () => _showLoginPortalsModal(context),
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          constraints: const BoxConstraints(minHeight: 48),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          alignment: Alignment.center,
                          child: const Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.lock_person_outlined,
                                color: AppTheme.primaryOrange,
                                size: 18,
                              ),
                              SizedBox(width: 8),
                              Text(
                                'LOGIN PORTALS',
                                style: TextStyle(
                                  color: AppTheme.primaryOrange,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 12,
                                  letterSpacing: 0.8,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),

                  // Button 2: MAKE A DONATION (Website Exact: Full Orange CTA)
                  Container(
                    margin: const EdgeInsets.only(bottom: 4),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryOrange,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: const Color(0xFFFB923C).withValues(alpha: 0.6),
                        width: 1,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: AppTheme.primaryOrange.withValues(alpha: 0.35),
                          blurRadius: 10,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        key: const Key('drawer_make_donation_button'),
                        onTap: () {
                          Navigator.of(context).pop();
                          context.push('/campaigns');
                        },
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          constraints: const BoxConstraints(minHeight: 48),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                          alignment: Alignment.center,
                          child: const Text(
                            'MAKE A DONATION',
                            style: TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w900,
                              fontSize: 12,
                              letterSpacing: 0.8,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ==========================================
            // 4. DRAWER FOOTER (Opaque #0B1426)
            // ==========================================
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: const BoxDecoration(
                color: Color(0xFF0B1426),
                border: Border(
                  top: BorderSide(color: Color(0x1AFFFFFF), width: 1),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Text(
                        '📞 ',
                        style: TextStyle(fontSize: 10),
                      ),
                      Expanded(
                        child: Text(
                          phone,
                          style: const TextStyle(
                            color: Color(0xFF9CA3AF),
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      const Text(
                        '✉️ ',
                        style: TextStyle(fontSize: 10),
                      ),
                      Expanded(
                        child: Text(
                          email,
                          style: const TextStyle(
                            color: Color(0xFF9CA3AF),
                            fontSize: 10,
                            fontWeight: FontWeight.w700,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  const Divider(color: Color(0x1AFFFFFF), height: 1, thickness: 1),
                  const SizedBox(height: 8),
                  const Center(
                    child: Text(
                      'ABVHPS CENTRAL PORTAL V2.0',
                      style: TextStyle(
                        color: Color(0xFF6B7280),
                        fontSize: 8.5,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 1.0,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeading(String title, {bool isFirst = false}) {
    return Container(
      margin: EdgeInsets.only(top: isFirst ? 2 : 12, bottom: 6),
      padding: const EdgeInsets.only(bottom: 6),
      decoration: const BoxDecoration(
        border: Border(
          bottom: BorderSide(
            color: Color(0x66475569), // border-slate-600/40
            width: 1,
          ),
        ),
      ),
      child: Text(
        title,
        style: const TextStyle(
          color: AppTheme.primaryOrange,
          fontSize: 9.5,
          fontWeight: FontWeight.w900,
          letterSpacing: 1.5,
        ),
      ),
    );
  }

  Widget _buildNavItem({
    required String title,
    required VoidCallback onTap,
    bool isActive = false,
    Key? key,
  }) {
    return Container(
      key: key,
      margin: const EdgeInsets.only(bottom: 6),
      decoration: BoxDecoration(
        color: isActive ? AppTheme.primaryOrange : const Color(0x33475569), // rgba(71, 85, 105, 0.20)
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isActive ? const Color(0x4DFFFFFF) : const Color(0x1AFFFFFF),
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: isActive
                ? AppTheme.primaryOrange.withValues(alpha: 0.35)
                : Colors.black.withValues(alpha: 0.05),
            blurRadius: isActive ? 12 : 2,
            offset: Offset(0, isActive ? 4 : 1),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            constraints: const BoxConstraints(minHeight: 48),
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            alignment: Alignment.centerLeft,
            child: Text(
              title,
              style: TextStyle(
                color: isActive ? Colors.white : const Color(0xFFE5E7EB),
                fontWeight: isActive ? FontWeight.w900 : FontWeight.w800,
                fontSize: 11,
                letterSpacing: 0.8,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildExpandableItem({
    required String title,
    required bool isExpanded,
    required VoidCallback onToggle,
    required List<Widget> children,
    Key? key,
  }) {
    return Container(
      key: key,
      margin: const EdgeInsets.only(bottom: 6),
      child: Column(
        children: [
          Container(
            decoration: BoxDecoration(
              color: const Color(0x33475569),
              borderRadius: isExpanded
                  ? const BorderRadius.vertical(top: Radius.circular(12))
                  : BorderRadius.circular(12),
              border: Border.all(color: const Color(0x1AFFFFFF), width: 1),
            ),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: onToggle,
                borderRadius: isExpanded
                    ? const BorderRadius.vertical(top: Radius.circular(12))
                    : BorderRadius.circular(12),
                child: Container(
                  constraints: const BoxConstraints(minHeight: 48),
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          title,
                          style: TextStyle(
                            color: isExpanded ? AppTheme.primaryOrange : const Color(0xFFE5E7EB),
                            fontWeight: FontWeight.w800,
                            fontSize: 11,
                            letterSpacing: 0.8,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Icon(
                        isExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                        color: isExpanded ? AppTheme.primaryOrange : Colors.white70,
                        size: 20,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
          if (isExpanded)
            Container(
              padding: const EdgeInsets.fromLTRB(10, 6, 10, 8),
              decoration: const BoxDecoration(
                color: Color(0x660F172A), // rgba(15, 23, 42, 0.40)
                borderRadius: BorderRadius.vertical(bottom: Radius.circular(12)),
                border: Border(
                  left: BorderSide(color: Color(0x1AFFFFFF), width: 1),
                  right: BorderSide(color: Color(0x1AFFFFFF), width: 1),
                  bottom: BorderSide(color: Color(0x1AFFFFFF), width: 1),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: children,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSubItem(
    String title, {
    required VoidCallback onTap,
    Key? key,
  }) {
    return Container(
      key: key,
      margin: const EdgeInsets.symmetric(vertical: 2),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 12),
            child: Text(
              title,
              style: const TextStyle(
                color: Color(0xFFD1D5DB),
                fontWeight: FontWeight.w700,
                fontSize: 10.5,
                letterSpacing: 0.5,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
