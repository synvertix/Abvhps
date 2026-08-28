<?php

namespace Tests\Feature\Api\V1;

use App\Events\HomeContentUpdated;
use App\Models\SiteSetting;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * HomeContentUpdatedEventTest
 *
 * Tests the server-side realtime invalidation event architecture.
 *
 * IMPORTANT: Event::fake() proves Laravel event dispatch semantics.
 * It does NOT prove:
 *   - The database queue worker processed the broadcast job.
 *   - Reverb sent a WebSocket frame.
 *   - A realtime client received the message.
 * Those layers are verified by the manual Batch 2 E2E procedure.
 */
class HomeContentUpdatedEventTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // 1. Contact phone mutation dispatches the event
    // ---------------------------------------------------------------

    public function test_contact_phone_mutation_dispatches_home_content_updated(): void
    {
        Event::fake();

        SiteSetting::set('contact_phone', '+91 9000000001');

        Event::assertDispatched(HomeContentUpdated::class);
    }

    // ---------------------------------------------------------------
    // 2. Unrelated key does NOT dispatch the event
    // ---------------------------------------------------------------

    public function test_unrelated_setting_key_does_not_dispatch_home_content_updated(): void
    {
        Event::fake();

        // This key is read by the home API but is NOT contact_phone.
        // Only contact_phone triggers realtime in Batch 2.
        SiteSetting::set('homepage_join_enabled', '1');

        Event::assertNotDispatched(HomeContentUpdated::class);
    }

    // ---------------------------------------------------------------
    // 3. HomeContentUpdated implements ShouldBroadcast
    // ---------------------------------------------------------------

    public function test_home_content_updated_implements_should_broadcast(): void
    {
        $event = new HomeContentUpdated();

        $this->assertInstanceOf(ShouldBroadcast::class, $event);
    }

    // ---------------------------------------------------------------
    // 4. HomeContentUpdated implements ShouldDispatchAfterCommit
    // ---------------------------------------------------------------

    public function test_home_content_updated_implements_should_dispatch_after_commit(): void
    {
        $event = new HomeContentUpdated();

        $this->assertInstanceOf(ShouldDispatchAfterCommit::class, $event);
    }

    // ---------------------------------------------------------------
    // 5. broadcastOn() returns Illuminate\Broadcasting\Channel
    // ---------------------------------------------------------------

    public function test_broadcast_on_returns_illuminate_channel(): void
    {
        $event = new HomeContentUpdated();
        $channels = $event->broadcastOn();

        // broadcastOn() may return a single Channel or an array.
        // Normalise to array for uniform assertion.
        if (!is_array($channels)) {
            $channels = [$channels];
        }

        $this->assertNotEmpty($channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
    }

    // ---------------------------------------------------------------
    // 6. Channel name is exactly 'public.home'
    // ---------------------------------------------------------------

    public function test_channel_name_is_public_home(): void
    {
        $event = new HomeContentUpdated();
        $channels = $event->broadcastOn();

        if (!is_array($channels)) {
            $channels = [$channels];
        }

        $this->assertSame('public.home', $channels[0]->name);
    }

    // ---------------------------------------------------------------
    // 7. Channel is NOT a PrivateChannel
    // ---------------------------------------------------------------

    public function test_channel_is_not_private_channel(): void
    {
        $event = new HomeContentUpdated();
        $channels = $event->broadcastOn();

        if (!is_array($channels)) {
            $channels = [$channels];
        }

        $this->assertNotInstanceOf(
            \Illuminate\Broadcasting\PrivateChannel::class,
            $channels[0]
        );
    }

    // ---------------------------------------------------------------
    // 8. Broadcast event name is exactly 'home.content.updated'
    // ---------------------------------------------------------------

    public function test_broadcast_as_returns_stable_event_name(): void
    {
        $event = new HomeContentUpdated();

        $this->assertSame('home.content.updated', $event->broadcastAs());
    }

    // ---------------------------------------------------------------
    // 9-13. Payload contains exactly the 6 allowlisted keys
    // ---------------------------------------------------------------

    public function test_payload_contains_exactly_the_six_allowlisted_keys(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $expectedKeys = ['schema_version', 'event_id', 'entity', 'id', 'updated_at', 'action'];
        sort($expectedKeys);

        $actualKeys = array_keys($payload);
        sort($actualKeys);

        $this->assertSame($expectedKeys, $actualKeys, 'Payload must contain exactly 6 allowlisted keys');
    }

    public function test_schema_version_is_integer_one(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertSame(1, $payload['schema_version']);
    }

    public function test_entity_is_home(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertSame('home', $payload['entity']);
    }

    public function test_id_is_null(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertNull($payload['id']);
    }

    public function test_action_is_refresh(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertSame('refresh', $payload['action']);
    }

    // ---------------------------------------------------------------
    // 14. event_id is a valid UUID v4
    // ---------------------------------------------------------------

    public function test_event_id_is_a_valid_uuid(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            (string) $payload['event_id'],
            'event_id must be a valid UUID v4'
        );
    }

    // ---------------------------------------------------------------
    // 15. updated_at is valid parseable ISO-8601
    // ---------------------------------------------------------------

    public function test_updated_at_is_parseable_iso8601(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();

        $this->assertIsString($payload['updated_at']);
        $parsed = \Carbon\Carbon::parse($payload['updated_at']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $parsed);
    }

    // ---------------------------------------------------------------
    // 16. Payload contains no private or sensitive data
    // ---------------------------------------------------------------

    public function test_payload_contains_no_sensitive_data(): void
    {
        $event = new HomeContentUpdated();
        $payload = $event->broadcastWith();
        $json = json_encode($payload);

        // No phone number patterns
        $this->assertDoesNotMatchRegularExpression('/\+?91[0-9]{10}/', $json);
        $this->assertDoesNotMatchRegularExpression('/\b[6-9][0-9]{9}\b/', $json);

        // No email addresses
        $this->assertDoesNotMatchRegularExpression('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $json);

        // No keywords indicating private data
        $sensitiveKeywords = [
            'password', 'token', 'secret', 'key', 'aadhaar', 'voter', 'bank',
            'account', 'ifsc', 'credential', 'aws', 'fast2sms', 'cashfree',
            'razorpay', 'contact_phone', 'setting', 'value',
        ];
        foreach ($sensitiveKeywords as $keyword) {
            $this->assertStringNotContainsStringIgnoringCase(
                $keyword,
                $json,
                "Payload must not contain keyword: {$keyword}"
            );
        }

        // No filesystem paths
        $this->assertStringNotContainsString('xampp', $json);
        $this->assertStringNotContainsString('/var/www', $json);
        $this->assertStringNotContainsString('C:\\\\', $json);
        $this->assertStringNotContainsString('.env', $json);
    }

    // ---------------------------------------------------------------
    // 17. Outer transaction rollback dispatches ZERO events
    // ---------------------------------------------------------------

    public function test_outer_transaction_rollback_dispatches_zero_events(): void
    {
        Event::fake();

        try {
            DB::transaction(function () {
                SiteSetting::set('contact_phone', '+91 9000000002');
                throw new \RuntimeException('Simulated rollback');
            });
        } catch (\RuntimeException) {
            // Expected — the transaction rolled back
        }

        // ShouldDispatchAfterCommit: rolled-back transaction = zero events
        Event::assertNotDispatched(HomeContentUpdated::class);
    }

    // ---------------------------------------------------------------
    // 18. Committed transaction dispatches the event
    // ---------------------------------------------------------------

    public function test_committed_transaction_dispatches_home_content_updated(): void
    {
        Event::fake();

        DB::transaction(function () {
            SiteSetting::set('contact_phone', '+91 9000000003');
        });

        Event::assertDispatched(HomeContentUpdated::class);
    }

    // ---------------------------------------------------------------
    // 19. GET /api/v1/home remains HTTP 200 after mutation
    // ---------------------------------------------------------------

    public function test_get_api_v1_home_returns_http_200_after_contact_phone_mutation(): void
    {
        Event::fake();

        SiteSetting::set('contact_phone', '+91 9000000004');

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
    }

    // ---------------------------------------------------------------
    // 20. After mutation, GET /api/v1/home reflects the committed value
    // ---------------------------------------------------------------

    public function test_get_api_v1_home_reflects_committed_contact_phone_after_mutation(): void
    {
        Event::fake();

        SiteSetting::set('contact_phone', '+91 8765000000');

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $this->assertSame('+91 8765000000', $response->json('data.contact.phone'));
    }

    // ---------------------------------------------------------------
    // Additional: Each construction produces a unique event_id
    // ---------------------------------------------------------------

    public function test_each_event_construction_produces_unique_event_id(): void
    {
        $event1 = new HomeContentUpdated();
        $event2 = new HomeContentUpdated();

        $this->assertNotSame(
            $event1->broadcastWith()['event_id'],
            $event2->broadcastWith()['event_id']
        );
    }
}
