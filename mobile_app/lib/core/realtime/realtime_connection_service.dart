import 'dart:async';
import 'dart:convert';
import 'dart:math';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:web_socket_channel/web_socket_channel.dart';
import '../config/app_config.dart';
import 'realtime_event.dart';

enum RealtimeConnectionState {
  disconnected,
  connecting,
  connected,
  reconnecting,
}

typedef WebSocketChannelFactory = WebSocketChannel Function(Uri uri);

final realtimeConnectionServiceProvider = Provider<RealtimeConnectionService>((ref) {
  final service = RealtimeConnectionService();
  ref.onDispose(() => service.dispose());
  return service;
});

class RealtimeConnectionService {
  final String host;
  final int port;
  final String scheme;
  final String appKey;
  final WebSocketChannelFactory _channelFactory;

  RealtimeConnectionState _state = RealtimeConnectionState.disconnected;
  final _stateController = StreamController<RealtimeConnectionState>.broadcast();

  final _eventsController = StreamController<RealtimeEvent>.broadcast();

  WebSocketChannel? _channel;
  StreamSubscription? _subscription;
  Timer? _reconnectTimer;
  int _reconnectAttempts = 0;
  bool _isDisposed = false;
  bool _isExplicitlyDisconnected = false;

  final Set<String> _subscribedChannels = {};

  RealtimeConnectionService({
    String? host,
    int? port,
    String? scheme,
    String? appKey,
    WebSocketChannelFactory? channelFactory,
  })  : host = host ?? AppConfig.wsHost,
        port = port ?? AppConfig.wsPort,
        scheme = scheme ?? AppConfig.wsScheme,
        appKey = appKey ?? AppConfig.reverbAppKey,
        _channelFactory = channelFactory ?? ((uri) => WebSocketChannel.connect(uri));

  RealtimeConnectionState get state => _state;
  Stream<RealtimeConnectionState> get stateStream => _stateController.stream;
  Stream<RealtimeEvent> get eventsStream => _eventsController.stream;
  Set<String> get activeSubscriptions => Set.unmodifiable(_subscribedChannels);

  void _setState(RealtimeConnectionState newState) {
    if (_state != newState && !_isDisposed) {
      _state = newState;
      _stateController.add(_state);
    }
  }

  Uri _buildConnectionUri() {
    return Uri(
      scheme: scheme,
      host: host,
      port: port,
      path: '/app/$appKey',
      queryParameters: {
        'protocol': '7',
        'client': 'js',
        'version': '8.4.0-pusher',
        'flash': 'false',
      },
    );
  }

  Future<void> connect() async {
    if (_isDisposed || _state == RealtimeConnectionState.connected || _state == RealtimeConnectionState.connecting) {
      return;
    }

    _isExplicitlyDisconnected = false;
    _reconnectTimer?.cancel();
    _setState(_reconnectAttempts == 0 ? RealtimeConnectionState.connecting : RealtimeConnectionState.reconnecting);

    try {
      final uri = _buildConnectionUri();
      _channel = _channelFactory(uri);

      _subscription = _channel!.stream.listen(
        _handleIncomingMessage,
        onError: (error) {
          _handleConnectionLoss();
        },
        onDone: () {
          _handleConnectionLoss();
        },
        cancelOnError: false,
      );
    } catch (_) {
      _handleConnectionLoss();
    }
  }

  void _handleIncomingMessage(dynamic rawMessage) {
    if (_isDisposed) return;

    try {
      final decoded = jsonDecode(rawMessage.toString());
      if (decoded is! Map<String, dynamic>) return;

      final eventName = decoded['event']?.toString() ?? '';
      final channelName = decoded['channel']?.toString();
      final dataField = decoded['data'];

      // Handle Pusher Protocol Handshake
      if (eventName == 'pusher:connection_established') {
        _reconnectAttempts = 0;
        _setState(RealtimeConnectionState.connected);

        // Re-subscribe all active channels upon successful connection
        for (final channel in _subscribedChannels) {
          _sendSubscribePayload(channel);
        }
        return;
      }

      // Handle Pusher Ping Heartbeat
      if (eventName == 'pusher:ping') {
        _sendRaw({'event': 'pusher:pong', 'data': {}});
        return;
      }

      // Handle App Invalidation Events
      if (eventName.isNotEmpty && eventName != 'pusher:pong' && eventName != 'pusher_internal:subscription_succeeded') {
        final realtimeEvent = RealtimeEvent.fromRawData(
          dataField,
          channel: channelName,
          rawEventName: eventName,
        );
        _eventsController.add(realtimeEvent);
      }
    } catch (_) {
      // Safely ignore malformed or unrecognized payloads without crashing
    }
  }

  void _sendRaw(Map<String, dynamic> payload) {
    if (_channel != null && _state == RealtimeConnectionState.connected) {
      try {
        _channel!.sink.add(jsonEncode(payload));
      } catch (_) {
        // Socket write failure handled via stream onError
      }
    }
  }

  void _sendSubscribePayload(String channel) {
    _sendRaw({
      'event': 'pusher:subscribe',
      'data': {'channel': channel},
    });
  }

  void subscribe(String channel) {
    final trimmed = channel.trim();
    if (trimmed.isEmpty) return;

    _subscribedChannels.add(trimmed);
    if (_state == RealtimeConnectionState.connected) {
      _sendSubscribePayload(trimmed);
    }
  }

  void unsubscribe(String channel) {
    final trimmed = channel.trim();
    if (trimmed.isEmpty) return;

    _subscribedChannels.remove(trimmed);
    if (_state == RealtimeConnectionState.connected) {
      _sendRaw({
        'event': 'pusher:unsubscribe',
        'data': {'channel': trimmed},
      });
    }
  }

  void _handleConnectionLoss() {
    _cleanupChannel();

    if (_isDisposed || _isExplicitlyDisconnected) {
      _setState(RealtimeConnectionState.disconnected);
      return;
    }

    _setState(RealtimeConnectionState.reconnecting);
    _scheduleReconnect();
  }

  void _scheduleReconnect() {
    _reconnectTimer?.cancel();
    if (_isDisposed || _isExplicitlyDisconnected) return;

    _reconnectAttempts++;
    // Exponential backoff: 1s, 2s, 4s, 8s, 16s, max 30s with jitter
    final baseDelay = min(pow(2, _reconnectAttempts - 1).toInt(), 30);
    final jitter = Random().nextInt(1000); // 0-999ms jitter
    final delayMs = (baseDelay * 1000) + jitter;

    _reconnectTimer = Timer(Duration(milliseconds: delayMs), () {
      if (!_isDisposed && !_isExplicitlyDisconnected) {
        connect();
      }
    });
  }

  void _cleanupChannel() {
    try {
      _subscription?.cancel();
    } catch (_) {}
    _subscription = null;

    try {
      _channel?.sink.close();
    } catch (_) {}
    _channel = null;
  }

  void pause() {
    _reconnectTimer?.cancel();
    _cleanupChannel();
    _setState(RealtimeConnectionState.disconnected);
  }

  void resume() {
    if (!_isDisposed && !_isExplicitlyDisconnected) {
      connect();
    }
  }

  void disconnect() {
    _isExplicitlyDisconnected = true;
    _reconnectTimer?.cancel();
    _cleanupChannel();
    _setState(RealtimeConnectionState.disconnected);
  }

  void dispose() {
    _isDisposed = true;
    _isExplicitlyDisconnected = true;
    _reconnectTimer?.cancel();
    _cleanupChannel();
    _setState(RealtimeConnectionState.disconnected);
    _stateController.close();
    _eventsController.close();
  }
}
