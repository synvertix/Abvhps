import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../core/i18n/i18n.dart';
import '../core/sync/app_lifecycle_sync_observer.dart';
import '../core/sync/sync_coordinator.dart';
import '../core/theme/app_theme.dart';
import 'router.dart';

class AbvhpsApp extends ConsumerStatefulWidget {
  const AbvhpsApp({super.key});

  @override
  ConsumerState<AbvhpsApp> createState() => _AbvhpsAppState();
}

class _AbvhpsAppState extends ConsumerState<AbvhpsApp> {
  late final AppLifecycleSyncObserver _lifecycleObserver;

  @override
  void initState() {
    super.initState();
    _lifecycleObserver = AppLifecycleSyncObserver(ref: ref);
    WidgetsBinding.instance.addObserver(_lifecycleObserver);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(_lifecycleObserver);
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Initialize Central Sync Coordinator
    ref.watch(syncCoordinatorProvider);
    final router = ref.watch(appRouterProvider);
    final i18n = ref.watch(i18nProvider);

    return MaterialApp.router(
      title: 'ABVHPS',
      theme: AppTheme.lightTheme,
      routerConfig: router,
      builder: (context, child) => I18nScope(i18n: i18n, child: child ?? const SizedBox.shrink()),
      debugShowCheckedModeBanner: false,
    );
  }
}
