import 'package:flutter/widgets.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'sync_coordinator.dart';

class AppLifecycleSyncObserver extends WidgetsBindingObserver {
  final WidgetRef ref;

  AppLifecycleSyncObserver({required this.ref});

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final syncCoordinator = ref.read(syncCoordinatorProvider);

    switch (state) {
      case AppLifecycleState.resumed:
        syncCoordinator.handleAppResumed();
        break;
      case AppLifecycleState.paused:
      case AppLifecycleState.hidden:
        syncCoordinator.handleAppPaused();
        break;
      default:
        break;
    }
  }
}
