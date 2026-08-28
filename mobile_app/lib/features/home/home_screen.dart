import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/theme/app_theme.dart';
import 'home_provider.dart';
import 'widgets/top_contact_bar.dart';
import 'widgets/main_header.dart';
import 'widgets/hero_banner.dart';
import 'widgets/divine_origin_section.dart';
import 'widgets/vision_mission_section.dart';
import 'widgets/live_stats_section.dart';
import 'widgets/campaigns_section.dart';
import 'widgets/projects_section.dart';
import 'widgets/public_footer.dart';
import 'widgets/public_drawer.dart';
import 'widgets/floating_whatsapp_button.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  Widget build(BuildContext context) {
    final homeAsync = ref.watch(homeDataProvider);

    return homeAsync.when(
      data: (data) => _buildScaffold(context, data),
      loading: () => _buildScaffold(context, const {}),
      error: (err, stack) => _buildScaffold(context, const {}),
    );
  }

  Widget _buildScaffold(
    BuildContext context,
    Map<String, dynamic> data,
  ) {
    final contact = data['contact'] as Map<String, dynamic>?;
    final socialStrip = data['social_strip'] as Map<String, dynamic>?;
    final socialLinks = socialStrip?['platforms'] as List<dynamic>?;
    final banner = data['banner'] as Map<String, dynamic>?;
    final sliders = data['sliders'] as List<dynamic>?;
    final stats = data['stats'] as Map<String, dynamic>?;
    final campaigns = data['campaigns'] as List<dynamic>?;
    final projects = data['projects'] as List<dynamic>?;
    final whatsappNumber = contact?['whatsapp_number']?.toString();
    final whatsappUrl = contact?['whatsapp_url']?.toString();

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: AppTheme.lightGray,
      drawer: PublicDrawer(contact: contact),
      floatingActionButton: FloatingWhatsAppButton(
        whatsappNumber: whatsappNumber,
        whatsappUrl: whatsappUrl,
      ),
      body: SafeArea(
        top: true,
        bottom: false,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // 1. Dark Top Contact & Social Bar
              TopContactBar(
                contact: contact,
                socialLinks: socialLinks,
              ),

              // 2. White Main Header with Emblem, Wordmark, and Hamburger Menu
              MainHeader(
                onOpenDrawer: () => _scaffoldKey.currentState?.openDrawer(),
              ),

              // 3. Responsive Tall Hero Banner Section
              HeroBanner(
                banner: banner,
                sliders: sliders,
              ),

              // 4. Our Divine Origin Section
              const DivineOriginSection(),

              // 5. Vision, Mission & Goal Section
              const VisionMissionSection(),

              // 6. Live Counter Statistics Orange Band
              LiveStatsSection(stats: stats),

              // 7. Fundraising Campaigns Section (if available)
              if (campaigns != null && campaigns.isNotEmpty)
                CampaignsSection(campaigns: campaigns),

              // 8. Our Core Service Projects Section (if available)
              if (projects != null && projects.isNotEmpty)
                ProjectsSection(projects: projects),

              // 9. Full Website Footer
              PublicFooter(contact: contact),
            ],
          ),
        ),
      ),
    );
  }
}
