import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';
import 'package:async/async.dart';
import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:stream_channel/stream_channel.dart';
import 'package:abvhpsapp/core/api/api_client.dart';
import 'package:abvhpsapp/core/realtime/realtime_connection_service.dart';
import 'package:abvhpsapp/core/realtime/realtime_event.dart';
import 'package:abvhpsapp/core/storage/token_storage.dart';
import 'package:abvhpsapp/core/sync/sync_coordinator.dart';
import 'package:abvhpsapp/features/home/home_provider.dart';
import 'package:web_socket_channel/web_socket_channel.dart';

class _FakeWebSocketSink implements WebSocketSink {
  final StreamController<dynamic> _controller;
  final List<dynamic> sentMessages;

  _FakeWebSocketSink(this._controller, this.sentMessages);

  @override
  void add(dynamic data) {
    sentMessages.add(data);
    _controller.add(data);
  }

  @override
  void addError(Object error, [StackTrace? stackTrace]) {
    _controller.addError(error, stackTrace);
  }

  @override
  Future addStream(Stream stream) => _controller.addStream(stream);

  @override
  Future close([int? closeCode, String? closeReason]) => _controller.close();

  @override
  Future get done => _controller.done;
}

class FakeWebSocketChannel implements WebSocketChannel {
  final StreamController<dynamic> _incomingController = StreamController<dynamic>.broadcast();
  final StreamController<dynamic> _sinkController = StreamController<dynamic>();
  final List<dynamic> sentMessages = [];
  late final _FakeWebSocketSink _fakeSink;

  FakeWebSocketChannel() {
    _fakeSink = _FakeWebSocketSink(_sinkController, sentMessages);
  }

  @override
  Stream<dynamic> get stream => _incomingController.stream;

  @override
  WebSocketSink get sink => _fakeSink;

  @override
  String? get protocol => 'pusher';

  @override
  int? get closeCode => null;

  @override
  String? get closeReason => null;

  @override
  Future<void> get ready => Future.value();

  @override
  StreamChannel<S> cast<S>() => throw UnimplementedError();

  @override
  StreamChannel<dynamic> changeSink(StreamSink<dynamic> Function(StreamSink<dynamic>) change) => throw UnimplementedError();

  @override
  StreamChannel<dynamic> changeStream(Stream<dynamic> Function(Stream<dynamic>) change) => throw UnimplementedError();

  @override
  void pipe(StreamChannel<dynamic> other) => throw UnimplementedError();

  @override
  StreamChannel<S> transform<S>(StreamChannelTransformer<S, dynamic> transformer) => throw UnimplementedError();

  @override
  StreamChannel<dynamic> transformSink(StreamSinkTransformer<dynamic, dynamic> transformer) => throw UnimplementedError();

  @override
  StreamChannel<dynamic> transformStream(StreamTransformer<dynamic, dynamic> transformer) => throw UnimplementedError();

  void emitMessage(dynamic message) {
    _incomingController.add(message);
  }

  void emitError(dynamic error) {
    _incomingController.addError(error);
  }

  void closeChannel() {
    _incomingController.close();
    _sinkController.close();
  }
}

class MockTokenStorage implements TokenStorage {
  String? token;
  String? accountType;

  MockTokenStorage({this.token, this.accountType});

  @override
  Future<String?> getToken() async => token;

  @override
  Future<void> saveToken(String t) async {
    token = t;
  }

  @override
  Future<void> clearToken() async {
    token = null;
    accountType = null;
  }

  @override
  Future<String?> getAccountType() async => accountType;

  @override
  Future<void> saveAccountType(String a) async {
    accountType = a;
  }
}

class Mock401Adapter implements HttpClientAdapter {
  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? requestStream,
    Future<void>? cancelFuture,
  ) async {
    final responsePayload = jsonEncode({
      'success': false,
      'message': 'Unauthenticated or token expired.',
    });
    return ResponseBody.fromString(
      responsePayload,
      401,
      headers: {
        Headers.contentTypeHeader: [Headers.jsonContentType],
      },
    );
  }

  @override
  void close({bool force = false}) {}
}

void main() {
  group('Realtime & Sync Foundation Unit Tests', () {
    test('1. RealtimeEvent parses valid Batch 2 payload and validates correctly', () {
      final validRaw = jsonEncode({
        'schema_version': 1,
        'event_id': '8f5a2b1c-99d8-4f2a-bc81-1234567890ab',
        'entity': 'home',
        'id': null,
        'updated_at': '2026-08-28T12:00:00Z',
        'action': 'refresh',
      });

      final event = RealtimeEvent.fromRawData(
        validRaw,
        channel: 'public.home',
        rawEventName: 'home.content.updated',
      );

      expect(event.schemaVersion, equals(1));
      expect(event.eventId, equals('8f5a2b1c-99d8-4f2a-bc81-1234567890ab'));
      expect(event.entity, equals('home'));
      expect(event.action, equals('refresh'));
      expect(event.channel, equals('public.home'));
      expect(event.rawEventName, equals('home.content.updated'));
      expect(event.isValidHomeEvent(), isTrue);
    });

    test('2. RealtimeEvent safely rejects invalid schema or unknown entity', () {
      // Invalid schema version
      final invalidSchema = RealtimeEvent.fromRawData({
        'schema_version': 2,
        'event_id': 'uuid-1',
        'entity': 'home',
        'updated_at': '2026-08-28T12:00:00Z',
        'action': 'refresh',
      });
      expect(invalidSchema.isValidHomeEvent(), isFalse);

      // Unknown entity
      final unknownEntity = RealtimeEvent.fromRawData({
        'schema_version': 1,
        'event_id': 'uuid-2',
        'entity': 'unknown_table',
        'updated_at': '2026-08-28T12:00:00Z',
        'action': 'refresh',
      });
      expect(unknownEntity.isValidHomeEvent(), isFalse);

      // Malformed / Empty
      final emptyEvent = RealtimeEvent.fromRawData('malformed-non-json');
      expect(emptyEvent.isValidHomeEvent(), isFalse);
    });

    test('3. RealtimeConnectionService manages Pusher handshake and connection states', () async {
      late FakeWebSocketChannel activeChannel;
      final service = RealtimeConnectionService(
        host: '127.0.0.1',
        port: 8080,
        scheme: 'ws',
        appKey: 'test-key',
        channelFactory: (uri) {
          activeChannel = FakeWebSocketChannel();
          return activeChannel;
        },
      );

      expect(service.state, equals(RealtimeConnectionState.disconnected));

      final stateList = <RealtimeConnectionState>[];
      final stateSub = service.stateStream.listen(stateList.add);

      // Connect
      await service.connect();
      expect(service.state, equals(RealtimeConnectionState.connecting));

      // Simulate Pusher handshake
      activeChannel.emitMessage(jsonEncode({
        'event': 'pusher:connection_established',
        'data': jsonEncode({'socket_id': '1234.5678'}),
      }));

      await Future.delayed(const Duration(milliseconds: 20));
      expect(service.state, equals(RealtimeConnectionState.connected));

      // Subscribe to public.home
      service.subscribe('public.home');
      expect(activeChannel.sentMessages.last, contains('pusher:subscribe'));
      expect(activeChannel.sentMessages.last, contains('public.home'));

      // Simulate Ping Heartbeat
      activeChannel.emitMessage(jsonEncode({
        'event': 'pusher:ping',
        'data': {},
      }));
      await Future.delayed(const Duration(milliseconds: 20));
      expect(activeChannel.sentMessages.last, contains('pusher:pong'));

      await stateSub.cancel();
      service.dispose();
      activeChannel.closeChannel();
    });

    test('4. SyncCoordinator deduplicates rapid identical event_id and invalidates provider', () async {
      late FakeWebSocketChannel activeChannel;
      final service = RealtimeConnectionService(
        channelFactory: (uri) {
          activeChannel = FakeWebSocketChannel();
          return activeChannel;
        },
      );

      int homeProviderBuildCount = 0;

      final container = ProviderContainer(
        overrides: [
          realtimeConnectionServiceProvider.overrideWithValue(service),
          homeDataProvider.overrideWith((ref) async {
            homeProviderBuildCount++;
            return {'title': 'Home Data'};
          }),
        ],
      );

      final coordinator = container.read(syncCoordinatorProvider);
      final sub = container.listen(homeDataProvider, (previous, next) {});

      // Settle initial connection handshake
      activeChannel.emitMessage(jsonEncode({
        'event': 'pusher:connection_established',
        'data': jsonEncode({'socket_id': '1234'}),
      }));
      await Future.delayed(const Duration(milliseconds: 400));
      final initialCount = homeProviderBuildCount;

      final eventPayload = jsonEncode({
        'event': 'home.content.updated',
        'channel': 'public.home',
        'data': jsonEncode({
          'schema_version': 1,
          'event_id': 'duplicate-test-uuid-1',
          'entity': 'home',
          'id': null,
          'updated_at': '2026-08-28T12:00:00Z',
          'action': 'refresh',
        }),
      });

      // Send identical event 3 times in rapid succession
      activeChannel.emitMessage(eventPayload);
      activeChannel.emitMessage(eventPayload);
      activeChannel.emitMessage(eventPayload);

      // Wait for debounce duration (300ms + buffer)
      await Future.delayed(const Duration(milliseconds: 450));

      // Provider was invalidated and refetched exactly once more
      expect(homeProviderBuildCount, equals(initialCount + 1));

      sub.close();
      coordinator.dispose();
      service.dispose();
      container.dispose();
      activeChannel.closeChannel();
    });

    test('5. Reconnect triggers authoritative REST reconciliation', () async {
      late FakeWebSocketChannel channel1;
      late FakeWebSocketChannel channel2;
      int connectCount = 0;

      final service = RealtimeConnectionService(
        channelFactory: (uri) {
          connectCount++;
          if (connectCount == 1) {
            channel1 = FakeWebSocketChannel();
            return channel1;
          } else {
            channel2 = FakeWebSocketChannel();
            return channel2;
          }
        },
      );

      int homeProviderBuildCount = 0;

      final container = ProviderContainer(
        overrides: [
          realtimeConnectionServiceProvider.overrideWithValue(service),
          homeDataProvider.overrideWith((ref) async {
            homeProviderBuildCount++;
            return {'title': 'Home Data'};
          }),
        ],
      );

      container.read(syncCoordinatorProvider);
      final sub = container.listen(homeDataProvider, (previous, next) {});

      // Handshake 1 & settle
      channel1.emitMessage(jsonEncode({
        'event': 'pusher:connection_established',
        'data': jsonEncode({'socket_id': '1234'}),
      }));
      await Future.delayed(const Duration(milliseconds: 400));
      final initialCount = homeProviderBuildCount;

      // Simulate connection loss
      channel1.emitError('Connection lost');
      await Future.delayed(const Duration(milliseconds: 20));
      expect(service.state, equals(RealtimeConnectionState.reconnecting));

      // Reconnect manually / via scheduler
      await service.connect();
      channel2.emitMessage(jsonEncode({
        'event': 'pusher:connection_established',
        'data': jsonEncode({'socket_id': '5678'}),
      }));

      // Wait for reconciliation debounce
      await Future.delayed(const Duration(milliseconds: 450));

      expect(homeProviderBuildCount, equals(initialCount + 1));

      sub.close();
      service.dispose();
      container.dispose();
      channel1.closeChannel();
      channel2.closeChannel();
    });

    test('6. App resume triggers reconciliation', () async {
      late FakeWebSocketChannel activeChannel;
      final service = RealtimeConnectionService(
        channelFactory: (uri) {
          activeChannel = FakeWebSocketChannel();
          return activeChannel;
        },
      );

      int homeProviderBuildCount = 0;

      final container = ProviderContainer(
        overrides: [
          realtimeConnectionServiceProvider.overrideWithValue(service),
          homeDataProvider.overrideWith((ref) async {
            homeProviderBuildCount++;
            return {'title': 'Home Data'};
          }),
        ],
      );

      final coordinator = container.read(syncCoordinatorProvider);
      final sub = container.listen(homeDataProvider, (previous, next) {});
      await Future.delayed(const Duration(milliseconds: 400));
      final initialCount = homeProviderBuildCount;

      // Trigger app resume
      coordinator.handleAppResumed();
      await Future.delayed(const Duration(milliseconds: 450));

      expect(homeProviderBuildCount, equals(initialCount + 1));

      sub.close();
      service.dispose();
      container.dispose();
      activeChannel.closeChannel();
    });

    test('7. ApiClient clears token on HTTP 401 Unauthorized', () async {
      final storage = MockTokenStorage(token: 'expired-token', accountType: 'member');
      final dio = Dio();
      dio.httpClientAdapter = Mock401Adapter();

      final client = ApiClient(tokenStorage: storage, customDio: dio);

      expect(await storage.getToken(), equals('expired-token'));

      try {
        await client.get('/protected-endpoint');
      } catch (e) {
        // Expected ApiException
      }

      // Assert token was cleared on 401
      expect(await storage.getToken(), isNull);
    });
  });
}
