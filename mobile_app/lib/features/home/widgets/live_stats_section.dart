import 'package:flutter/material.dart';
import '../../../core/theme/app_theme.dart';

class LiveStatsSection extends StatelessWidget {
  final Map<String, dynamic>? stats;

  const LiveStatsSection({
    super.key,
    this.stats,
  });

  @override
  Widget build(BuildContext context) {
    final tiles = _tiles();

    return Container(
      width: double.infinity,
      clipBehavior: Clip.hardEdge,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
          colors: [
            AppTheme.deepSaffron,
            AppTheme.primaryOrange,
            AppTheme.warmGold,
          ],
          stops: [0.0, 0.45, 1.0],
        ),
        border: Border(
          top: BorderSide(color: AppTheme.templeGold, width: 2),
          bottom: BorderSide(color: AppTheme.templeGold, width: 2),
        ),
      ),
      child: Stack(
        children: [
          // Devotional lotus watermark
          Positioned(
            right: -44,
            bottom: -44,
            child: IgnorePointer(
              child: Icon(
                Icons.spa,
                size: 200,
                color: AppTheme.softGold.withValues(alpha: 0.18),
              ),
            ),
          ),
          Positioned(
            left: -36,
            top: -36,
            child: IgnorePointer(
              child: Icon(
                Icons.spa,
                size: 120,
                color: Colors.white.withValues(alpha: 0.08),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
            child: Column(
              children: [
                _buildHeading(),
                const SizedBox(height: 18),
                Wrap(
                  alignment: WrapAlignment.center,
                  runSpacing: 16,
                  children: [
                    for (final t in tiles)
                      SizedBox(
                        width: tiles.length >= 4
                            ? (MediaQuery.of(context).size.width - 32) / 2
                            : (MediaQuery.of(context).size.width - 32) / 3,
                        child: _buildStatItem(count: t.$1, label: t.$2),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  /// Only real, non-zero counters are shown; the band is completed with true organisation facts.
  List<(String, String)> _tiles() {
    int read(String key) => int.tryParse('${stats?[key] ?? 0}') ?? 0;

    final tiles = <(String, String)>[];
    for (final (key, label) in [
      ('donors', 'VERIFIED DONORS'),
      ('members', 'REGISTERED MEMBERS'),
      ('volunteers', 'TOTAL VOLUNTEERS'),
    ]) {
      final value = read(key);
      if (value > 0) tiles.add((value.toString(), label));
    }
    final years = read('years');
    if (years > 0) tiles.add((years.toString(), 'YEARS OF SERVICE'));

    for (final fact in [('2023', 'ESTABLISHED'), ('20/2023', 'REG. NO.')]) {
      if (tiles.length < 4) tiles.add(fact);
    }
    return tiles;
  }

  Widget _buildHeading() {
    Widget line(bool fadeLeft) => Expanded(
          child: Container(
            height: 1,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: fadeLeft ? Alignment.centerLeft : Alignment.centerRight,
                end: fadeLeft ? Alignment.centerRight : Alignment.centerLeft,
                colors: [
                  AppTheme.softGold.withValues(alpha: 0.0),
                  AppTheme.softGold.withValues(alpha: 0.85),
                ],
              ),
            ),
          ),
        );

    return Row(
      children: [
        line(true),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 10),
          child: Text(
            'OUR SEVA IN NUMBERS',
            style: TextStyle(
              color: Colors.white,
              fontSize: 10,
              fontWeight: FontWeight.w900,
              letterSpacing: 2.4,
            ),
          ),
        ),
        line(false),
      ],
    );
  }

  Widget _buildStatItem({required String count, required String label}) {
    return Column(
      children: [
        Text(
          count,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 28,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.5,
            shadows: [
              Shadow(
                color: Color(0x596E2300),
                blurRadius: 6,
                offset: Offset(0, 2),
              ),
            ],
          ),
        ),
        const SizedBox(height: 4),
        Text(
          label,
          textAlign: TextAlign.center,
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.92),
            fontSize: 10,
            fontWeight: FontWeight.w800,
            letterSpacing: 0.8,
          ),
        ),
      ],
    );
  }
}
