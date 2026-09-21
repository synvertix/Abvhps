import 'package:flutter/material.dart';
import '../../../core/i18n/i18n.dart';
import '../../../core/theme/app_theme.dart';

class DivineOriginSection extends StatelessWidget {
  const DivineOriginSection({super.key});

  static const Color _deepGold = Color(0xFFB8860B);

  // English source sentences — also the translation keys (see assets/i18n/*.json).
  static const String _guru = 'Sri Sri Sri Subrahmanneswara Swamy Garu';
  static const String _origin1 =
      'The Akhanda Bharata Viswa Hindu Parirakshana Samithi (ABVHPS) was founded in 2023 and is registered under Registration No. 20/2023. Guided by Rajaguru :guru, it works to preserve and revive Sanatana Dharma.';
  static const String _origin2 =
      'This charitable trust is dedicated to uplifting people mentally, morally and physically. It strives to beautify chosen villages and to nurture spiritual awareness, the wellbeing of our temples, and a deep love for the nation.';
  static const String _blessing =
      "Our main objective is to protect Hindu Sanathana Dharma, construct new temples, expand Goushalas, distribute daily meals under Annapurna, and support children's literacy across every Grama Panchayat.";

  /// Splits [text] around [needle] so the needle can be styled (e.g. the org name / the Guru's name).
  static List<InlineSpan> _highlight(String text, String needle, TextStyle style) {
    final at = text.indexOf(needle);
    if (at < 0) return [TextSpan(text: text)];
    return [
      TextSpan(text: text.substring(0, at)),
      TextSpan(text: needle, style: style),
      TextSpan(text: text.substring(at + needle.length)),
    ];
  }

  @override
  Widget build(BuildContext context) {
    const bodyStyle = TextStyle(color: AppTheme.textSecondary, fontSize: 14, height: 1.7);

    return Container(
      clipBehavior: Clip.hardEdge,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Colors.white, Color(0xFFFFFAF0), Colors.white],
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 30),
      child: Stack(
        children: [
          Positioned(
            right: -50,
            top: -20,
            child: IgnorePointer(
              child: Icon(
                Icons.spa,
                size: 190,
                color: AppTheme.templeGold.withValues(alpha: 0.07),
              ),
            ),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Text(
                    'ॐ',
                    style: TextStyle(color: _deepGold, fontSize: 18, fontWeight: FontWeight.w800),
                  ),
                  const SizedBox(width: 8),
                  Flexible(
                    child: Text(
                      context.tr('Our Divine Origin').toUpperCase(),
                      style: const TextStyle(
                        color: _deepGold,
                        fontSize: 11,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 2.4,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text.rich(
                TextSpan(
                  children: _highlight(
                    context.tr('Why and How :name Was Founded', args: {'name': 'ABVHPS'}),
                    'ABVHPS',
                    const TextStyle(color: AppTheme.primaryOrange),
                  ),
                ),
                style: const TextStyle(
                  color: AppTheme.neutralGray,
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.2,
                  height: 1.25,
                ),
              ),
              const SizedBox(height: 10),
              const _GoldRule(),
              const SizedBox(height: 14),
              Text.rich(
                TextSpan(
                  children: _highlight(
                    context.tr(_origin1, args: {'guru': _guru}),
                    _guru,
                    const TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
                style: bodyStyle,
              ),
              const SizedBox(height: 12),
              Text(context.tr(_origin2), style: bodyStyle),
              const SizedBox(height: 18),
              Row(
                children: [
                  Expanded(child: _FactChip(value: '2023', label: context.tr('Founded').toUpperCase())),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _FactChip(value: '20/2023', label: context.tr('Registration No.').toUpperCase()),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _FactChip(value: 'ॐ', label: context.tr('Charitable Trust').toUpperCase()),
                  ),
                ],
              ),
              const SizedBox(height: 22),
              _buildBlessingsCard(context),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildBlessingsCard(BuildContext context) {
    return Container(
      width: double.infinity,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE9C46A).withValues(alpha: 0.7)),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFFFFF6E0), Colors.white, Color(0xFFFFEFCF)],
        ),
        boxShadow: [
          BoxShadow(
            color: AppTheme.templeGold.withValues(alpha: 0.14),
            blurRadius: 14,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              height: 5,
              decoration: const BoxDecoration(
                gradient: LinearGradient(colors: [_deepGold, Color(0xFFF6D77B), _deepGold]),
              ),
            ),
          ),
          Positioned(
            right: -30,
            bottom: -30,
            child: IgnorePointer(
              child: Icon(Icons.spa, size: 140, color: AppTheme.templeGold.withValues(alpha: 0.12)),
            ),
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 24, 20, 20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      width: 40,
                      height: 40,
                      alignment: Alignment.center,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        gradient: const LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [Color(0xFFFFF3D1), Color(0xFFFBE3A1)],
                        ),
                        border: Border.all(color: AppTheme.templeGold.withValues(alpha: 0.55), width: 2),
                      ),
                      child: const Text(
                        'ॐ',
                        style: TextStyle(color: _deepGold, fontSize: 20, fontWeight: FontWeight.w800),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Flexible(
                      child: Text(
                        context.tr('Divine Blessings').toUpperCase(),
                        style: const TextStyle(
                          color: _deepGold,
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 2.2,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Icon(Icons.format_quote, size: 34, color: AppTheme.templeGold.withValues(alpha: 0.75)),
                Text(
                  context.tr(_blessing),
                  style: const TextStyle(
                    color: Color(0xFF1F2937),
                    fontSize: 15,
                    fontStyle: FontStyle.italic,
                    fontFamilyFallback: ['Georgia', 'serif'],
                    height: 1.65,
                  ),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.only(top: 14),
                  decoration: BoxDecoration(
                    border: Border(top: BorderSide(color: AppTheme.templeGold.withValues(alpha: 0.3))),
                  ),
                  child: Row(
                    children: [
                      const SizedBox(
                        width: 24,
                        child: Divider(color: AppTheme.templeGold, thickness: 1),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              _guru,
                              style: TextStyle(
                                color: AppTheme.neutralGray,
                                fontSize: 13,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              context.tr('Rajaguru').toUpperCase(),
                              style: const TextStyle(
                                color: _deepGold,
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 1.4,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _GoldRule extends StatelessWidget {
  const _GoldRule();

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(width: 50, height: 1, color: AppTheme.templeGold),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 6),
          child: Icon(Icons.diamond, size: 8, color: AppTheme.templeGold),
        ),
      ],
    );
  }
}

class _FactChip extends StatelessWidget {
  final String value;
  final String label;

  const _FactChip({required this.value, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 6),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.85),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE9C46A).withValues(alpha: 0.6)),
      ),
      child: Column(
        children: [
          Text(
            value,
            style: const TextStyle(
              color: AppTheme.primaryOrange,
              fontSize: 17,
              fontWeight: FontWeight.w900,
              height: 1.1,
            ),
          ),
          const SizedBox(height: 5),
          Text(
            label,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Color(0xFF6B7280),
              fontSize: 8.5,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.6,
            ),
          ),
        ],
      ),
    );
  }
}
