import 'package:flutter/material.dart';
import '../../features/home/widgets/floating_whatsapp_button.dart';
import '../../features/home/widgets/main_header.dart';
import '../../features/home/widgets/public_drawer.dart';
import '../../features/home/widgets/top_contact_bar.dart';
import '../theme/app_theme.dart';

class PublicScaffold extends StatefulWidget {
  final Widget body;
  final String? title;
  final bool showContactBar;
  final bool showFloatingWhatsApp;
  final Map<String, dynamic>? contact;
  final List<dynamic>? socialLinks;
  final Widget? bottomNavigationBar;
  final Color backgroundColor;

  const PublicScaffold({
    super.key,
    required this.body,
    this.title,
    this.showContactBar = true,
    this.showFloatingWhatsApp = true,
    this.contact,
    this.socialLinks,
    this.bottomNavigationBar,
    this.backgroundColor = Colors.white,
  });

  @override
  State<PublicScaffold> createState() => _PublicScaffoldState();
}

class _PublicScaffoldState extends State<PublicScaffold> {
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();

  void _openDrawer() {
    _scaffoldKey.currentState?.openDrawer();
  }

  @override
  Widget build(BuildContext context) {
    final whatsappUrl = widget.contact?['whatsapp_url']?.toString();

    return Scaffold(
      key: _scaffoldKey,
      backgroundColor: widget.backgroundColor,
      drawer: PublicDrawer(contact: widget.contact),
      bottomNavigationBar: widget.bottomNavigationBar,
      body: SafeArea(
        child: Column(
          children: [
            if (widget.showContactBar)
              TopContactBar(
                contact: widget.contact,
                socialLinks: widget.socialLinks,
              ),
            MainHeader(onOpenDrawer: _openDrawer),
            if (widget.title != null)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: const BoxDecoration(
                  color: AppTheme.darkNavy,
                  border: Border(
                    bottom: BorderSide(color: AppTheme.primaryOrange, width: 2),
                  ),
                ),
                child: Text(
                  widget.title!.toUpperCase(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 1.0,
                  ),
                ),
              ),
            Expanded(child: widget.body),
          ],
        ),
      ),
      floatingActionButton: widget.showFloatingWhatsApp
          ? FloatingWhatsAppButton(whatsappUrl: whatsappUrl)
          : null,
    );
  }
}
