<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Str;

/**
 * HomeContentUpdated — Public home content invalidation signal.
 *
 * This event is an INVALIDATION SIGNAL ONLY.
 * It carries no setting values, no PII, no credentials.
 *
 * On receipt, the Flutter client MUST:
 *   - Invalidate the local home state cache
 *   - Re-fetch GET /api/v1/home (the single source of truth)
 *
 * ShouldDispatchAfterCommit guarantees:
 *   - Event dispatches ONLY after the surrounding DB transaction commits.
 *   - On rollback, ZERO events are dispatched. No false invalidations.
 *
 * ShouldBroadcast routes the event through the database queue
 * and delivers it via Laravel Reverb to the public.home WebSocket channel.
 *
 * Payload is a strict allowlist of exactly 6 keys.
 * Do NOT add setting keys, values, models, or user data to this payload.
 */
class HomeContentUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    /**
     * Payload schema version.
     * Increment when the payload contract changes in a breaking way.
     */
    private const SCHEMA_VERSION = 1;

    /**
     * The strict invalidation payload.
     * Populated once at construction — immutable thereafter.
     *
     * @var array{
     *     schema_version: int,
     *     event_id: string,
     *     entity: string,
     *     id: null,
     *     updated_at: string,
     *     action: string,
     * }
     */
    private array $payload;

    public function __construct()
    {
        $this->payload = [
            'schema_version' => self::SCHEMA_VERSION,
            'event_id'       => (string) Str::uuid(),
            'entity'         => 'home',
            'id'             => null,
            'updated_at'     => now()->toIso8601String(),
            'action'         => 'refresh',
        ];
    }

    /**
     * The WebSocket channel this event broadcasts on.
     *
     * 'public.home' is a PUBLIC channel — no authorization callback required.
     * Any connected client may subscribe and receive invalidation signals.
     * Do NOT change this to PrivateChannel or PresenceChannel.
     */
    public function broadcastOn()
    {
        return new Channel('public.home');
    }

    /**
     * Stable client-facing event name.
     *
     * The Flutter client subscribes to this exact string.
     * Do NOT rename this without a coordinated Flutter + API release.
     * Default Laravel behavior would use the fully-qualified class name —
     * this method provides a stable, namespace-independent contract.
     */
    public function broadcastAs(): string
    {
        return 'home.content.updated';
    }

    /**
     * The strict, allowlisted broadcast payload.
     *
     * Keys: schema_version, event_id, entity, id, updated_at, action.
     * No other keys. No setting values. No PII. No credentials.
     *
     * @return array{
     *     schema_version: int,
     *     event_id: string,
     *     entity: string,
     *     id: null,
     *     updated_at: string,
     *     action: string,
     * }
     */
    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
