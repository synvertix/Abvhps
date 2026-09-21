import 'dart:convert';

class RealtimeEvent {
  final int schemaVersion;
  final String eventId;
  final String entity;
  final dynamic id;
  final String updatedAt;
  final String action;
  final String? channel;
  final String? rawEventName;

  const RealtimeEvent({
    required this.schemaVersion,
    required this.eventId,
    required this.entity,
    this.id,
    required this.updatedAt,
    required this.action,
    this.channel,
    this.rawEventName,
  });

  /// Factory constructor parsing JSON payload safely without throwing exceptions
  factory RealtimeEvent.fromRawData(
    dynamic data, {
    String? channel,
    String? rawEventName,
  }) {
    Map<String, dynamic> map = {};

    if (data is String) {
      try {
        final decoded = jsonDecode(data);
        if (decoded is Map<String, dynamic>) {
          map = decoded;
        }
      } catch (_) {
        map = {};
      }
    } else if (data is Map<String, dynamic>) {
      map = data;
    }

    final schemaVersion = (map['schema_version'] is num) ? (map['schema_version'] as num).toInt() : 0;
    final eventId = (map['event_id'] != null) ? map['event_id'].toString().trim() : '';
    final entity = (map['entity'] != null) ? map['entity'].toString().trim() : '';
    final id = map['id'];
    final updatedAt = (map['updated_at'] != null) ? map['updated_at'].toString().trim() : '';
    final action = (map['action'] != null) ? map['action'].toString().trim() : '';

    return RealtimeEvent(
      schemaVersion: schemaVersion,
      eventId: eventId,
      entity: entity,
      id: id,
      updatedAt: updatedAt,
      action: action,
      channel: channel,
      rawEventName: rawEventName,
    );
  }

  /// Strict validation against Batch 2 event contract:
  /// - schema_version == 1
  /// - entity == 'home'
  /// - action == 'refresh'
  /// - event_id is non-empty
  bool isValidHomeEvent() {
    if (schemaVersion != 1) return false;
    if (entity != 'home') return false;
    if (action != 'refresh') return false;
    if (eventId.isEmpty) return false;
    if (updatedAt.isEmpty) return false;
    return true;
  }

  @override
  String toString() {
    return 'RealtimeEvent(schema: $schemaVersion, eventId: $eventId, entity: $entity, action: $action, channel: $channel)';
  }
}
