import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../features/home/home_provider.dart';
import '../realtime/realtime_connection_service.dart';
import '../realtime/realtime_event.dart';

final syncCoordinatorProvider = Provider<SyncCoordinator>((ref) {
  final realtimeService = ref.watch(realtimeConnectionServiceProvider);
  final coordinator = SyncCoordinator(
    ref: ref,
    realtimeService: realtimeService,
  );
  coordinator.initialize();
  ref.onDispose(() => coordinator.dispose());
  return coordinator;
});

class SyncCoordinator {
  final Ref ref;
  final RealtimeConnectionService realtimeService;

  StreamSubscription<RealtimeEvent>? _eventsSubscription;
  StreamSubscription<RealtimeConnectionState>? _stateSubscription;

  // Bounded deduplication storage (max 200 items FIFO)
  static const int _maxDeduplicationSize = 200;
  final List<String> _processedEventIdsOrder = [];
  final Set<String> _processedEventIdsSet = {};

  // Debounce timers for entity invalidations
  Timer? _homeDebounceTimer;
  static const Duration _debounceDuration = Duration(milliseconds: 300);

  bool _isDisposed = false;
  RealtimeConnectionState _lastKnownState = RealtimeConnectionState.disconnected;

  SyncCoordinator({
    required this.ref,
    required this.realtimeService,
  });

  void initialize() {
    if (_isDisposed) return;

    // Subscribe to public.home channel
    realtimeService.subscribe('public.home');

    // Connect to WebSocket server
    realtimeService.connect();

    // Listen for realtime events
    _eventsSubscription = realtimeService.eventsStream.listen(
      _handleRealtimeEvent,
      onError: (_) {},
    );

    // Listen for connection state changes (reconnection reconciliation)
    _stateSubscription = realtimeService.stateStream.listen(
      _handleStateChange,
      onError: (_) {},
    );
  }

  void _handleRealtimeEvent(RealtimeEvent event) {
    if (_isDisposed) return;

    // 1. Contract Validation
    if (!event.isValidHomeEvent()) {
      // Ignore unknown or malformed events safely
      return;
    }

    // 2. Event Deduplication
    if (_isDuplicate(event.eventId)) {
      return;
    }
    _recordEventId(event.eventId);

    // 3. Entity Mapping & Debounced Invalidation
    if (event.entity == 'home') {
      _debounceInvalidateHome();
    }
  }

  void _handleStateChange(RealtimeConnectionState newState) {
    if (_isDisposed) return;

    final wasReconnectingOrDisconnected = _lastKnownState == RealtimeConnectionState.reconnecting ||
        _lastKnownState == RealtimeConnectionState.disconnected;

    _lastKnownState = newState;

    // On reconnect transition to connected, perform REST reconciliation
    if (newState == RealtimeConnectionState.connected && wasReconnectingOrDisconnected) {
      _debounceInvalidateHome();
    }
  }

  bool _isDuplicate(String eventId) {
    return _processedEventIdsSet.contains(eventId);
  }

  void _recordEventId(String eventId) {
    if (_processedEventIdsOrder.length >= _maxDeduplicationSize) {
      final oldest = _processedEventIdsOrder.removeAt(0);
      _processedEventIdsSet.remove(oldest);
    }
    _processedEventIdsOrder.add(eventId);
    _processedEventIdsSet.add(eventId);
  }

  void _debounceInvalidateHome() {
    _homeDebounceTimer?.cancel();
    _homeDebounceTimer = Timer(_debounceDuration, () {
      if (!_isDisposed) {
        ref.invalidate(homeDataProvider);
      }
    });
  }

  /// Manually or externally trigger authoritative home REST refresh
  void invalidateHome() {
    _debounceInvalidateHome();
  }

  /// Called by AppLifecycleSyncObserver when app returns to foreground
  void handleAppResumed() {
    if (_isDisposed) return;
    realtimeService.resume();
    _debounceInvalidateHome();
  }

  /// Called by AppLifecycleSyncObserver when app goes to background
  void handleAppPaused() {
    if (_isDisposed) return;
    realtimeService.pause();
  }

  void dispose() {
    _isDisposed = true;
    _homeDebounceTimer?.cancel();
    _eventsSubscription?.cancel();
    _stateSubscription?.cancel();
    _processedEventIdsOrder.clear();
    _processedEventIdsSet.clear();
  }
}
