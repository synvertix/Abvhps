import 'dart:async';

import 'package:flutter/material.dart';
import '../../../core/i18n/i18n.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/widgets/app_network_image.dart';

/// Home hero: rotating slides (auto-advance + swipe + dots).
///
/// A configured admin banner wins and is shown as a single static slide.
/// Otherwise the API `sliders` are used; if none arrive, built-in slides keep the hero alive.
class HeroBanner extends StatefulWidget {
  final Map<String, dynamic>? banner;
  final List<dynamic>? sliders;

  const HeroBanner({
    super.key,
    this.banner,
    this.sliders,
  });

  @override
  State<HeroBanner> createState() => _HeroBannerState();
}

class _HeroBannerState extends State<HeroBanner> {
  static const List<Map<String, String?>> _defaultSlides = [
    {
      'title': 'Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
      'subtitle': 'Preserving Sanathana Dharma and Empowering Communities',
    },
    {
      'title': 'Serve with Devotion',
      'subtitle':
          "Help us care for temples, protect Goshalas, share Annapurna meals and support children's literacy across every Grama Panchayat.",
    },
    {
      'title': 'Become Part of the Seva Family',
      'subtitle':
          'Join as a member or volunteer and walk with us in service to Dharma and to society.',
    },
  ];

  final PageController _controller = PageController();
  Timer? _timer;
  int _index = 0;

  List<Map<String, String?>> get _slides {
    final banner = widget.banner;
    if (banner != null) {
      final bannerUrl =
          (banner['mobile_banner'] ?? banner['desktop_banner'])?.toString();
      return [
        {
          'title': banner['title']?.toString() ?? _defaultSlides[0]['title'],
          'subtitle':
              banner['subtitle']?.toString() ?? _defaultSlides[0]['subtitle'],
          'image': bannerUrl,
        },
      ];
    }

    final fromApi = (widget.sliders ?? const <dynamic>[])
        .whereType<Map>()
        .map<Map<String, String?>>((m) => {
              'title': m['title']?.toString(),
              'subtitle': m['subtitle']?.toString(),
              'image': m['image_url']?.toString(),
            })
        .where((s) => (s['title'] ?? '').isNotEmpty)
        .toList();

    return fromApi.isNotEmpty ? fromApi : _defaultSlides;
  }

  @override
  void initState() {
    super.initState();
    _startTimer();
  }

  @override
  void didUpdateWidget(covariant HeroBanner oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (_index >= _slides.length) {
      _index = 0;
      if (_controller.hasClients) _controller.jumpToPage(0);
    }
    _startTimer();
  }

  void _startTimer() {
    _timer?.cancel();
    if (_slides.length < 2) return;
    _timer = Timer.periodic(const Duration(milliseconds: 5500), (_) {
      if (!mounted || !_controller.hasClients) return;
      final next = (_index + 1) % _slides.length;
      _controller.animateToPage(
        next,
        duration: const Duration(milliseconds: 700),
        curve: Curves.easeInOut,
      );
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final double screenWidth = MediaQuery.of(context).size.width;
    // Responsive portrait/tall banner height matching website 380-420px
    final double heroHeight = (screenWidth * 1.05).clamp(360.0, 430.0);
    final slides = _slides;

    return Container(
      width: double.infinity,
      height: heroHeight,
      decoration: const BoxDecoration(
        color: Color(0xFF2A1204),
        border: Border(
          bottom: BorderSide(color: AppTheme.templeGold, width: 4),
        ),
      ),
      child: Stack(
        fit: StackFit.expand,
        children: [
          PageView.builder(
            controller: _controller,
            itemCount: slides.length,
            onPageChanged: (i) => setState(() => _index = i),
            itemBuilder: (context, i) => _buildSlide(slides[i]),
          ),
          if (slides.length > 1)
            Positioned(
              left: 0,
              right: 0,
              bottom: 16,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(slides.length, (i) {
                  final active = i == _index;
                  return AnimatedContainer(
                    duration: const Duration(milliseconds: 250),
                    margin: const EdgeInsets.symmetric(horizontal: 4),
                    width: active ? 22 : 8,
                    height: 8,
                    decoration: BoxDecoration(
                      color: active
                          ? AppTheme.softGold
                          : Colors.white.withValues(alpha: 0.55),
                      borderRadius: BorderRadius.circular(4),
                    ),
                  );
                }),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildSlide(Map<String, String?> slide) {
    final title = context.tr(slide['title'] ?? '');
    final subtitle = context.tr(slide['subtitle'] ?? '');

    return Stack(
      fit: StackFit.expand,
      children: [
        // Background image (dynamic or permanent fallback)
        AppNetworkImage(
          imageUrl: slide['image'],
          fallbackAsset: 'assets/branding/ourteam_bg.png',
          fit: BoxFit.cover,
          errorWidget: Container(color: const Color(0xFF2A1204)),
        ),

        // Warm dark overlay for text readability
        Container(color: const Color(0xFF140802).withValues(alpha: 0.55)),

        // Content
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 24, 20, 36),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Text(
                'ॐ  ABVHPS',
                style: TextStyle(
                  color: AppTheme.softGold.withValues(alpha: 0.95),
                  fontSize: 12,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 2.4,
                ),
              ),
              const SizedBox(height: 12),
              Text(
                title.toUpperCase(),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0.8,
                  height: 1.25,
                  shadows: [
                    Shadow(
                      color: Colors.black87,
                      blurRadius: 10,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              Text(
                subtitle,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: AppTheme.lightOrange,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  height: 1.4,
                  shadows: [
                    Shadow(
                      color: Colors.black87,
                      blurRadius: 8,
                      offset: Offset(0, 1),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
