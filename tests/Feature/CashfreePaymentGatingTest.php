<?php

namespace Tests\Feature;

use App\Models\FundraisingCampaign;
use App\Models\Donation;
use App\Models\Membership;
use App\Services\CashfreePaymentService;
use App\Services\CashfreeSecureIdService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CashfreePaymentGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.cashfree.payments_enabled', false);
        Config::set('services.razorpay.key_id', 'rzp_test_public_key');
        Config::set('services.razorpay.key_secret', 'rzp_test_secret_key');
    }

    protected function postDonationJson(string $uri, array $data = [])
    {
        Http::fake([
            'https://api.razorpay.com/*' => Http::response([
                'id'       => 'order_RZP_MOCK_123',
                'status'   => 'created',
                'amount'   => 100,
                'currency' => 'INR',
            ], 200),
            'https://api.cashfree.com/*' => Http::response([
                'payment_session_id' => 'session_mock_cf_123',
                'order_id'           => 'order_CF_MOCK_123',
                'order_status'       => 'ACTIVE',
                'cf_order_id'        => 'cf_mock_123',
            ], 200),
            'https://sandbox.cashfree.com/*' => Http::response([
                'payment_session_id' => 'session_mock_cf_123',
                'order_id'           => 'order_CF_MOCK_123',
                'order_status'       => 'ACTIVE',
                'cf_order_id'        => 'cf_mock_123',
            ], 200),
        ]);

        $token = session('_token') ?: csrf_token();
        if (!$token) {
            $token = 'test_token_' . md5(uniqid());
            session(['_token' => $token]);
        }

        return $this->postJson($uri, $data, ['X-CSRF-TOKEN' => $token]);
    }

    private function createCampaign(array $overrides = []): FundraisingCampaign
    {
        return FundraisingCampaign::create(array_merge([
            'title'         => 'TEMPLE SEVA FUND',
            'description'   => 'Sacred contribution fund',
            'cover_image'   => 'campaigns/covers/sample.jpg',
            'target_amount' => 50000.00,
            'raised_amount' => 1000.00,
            'end_date'      => Carbon::now()->addMonths(6)->toDateString(),
            'status'        => 'active',
        ], $overrides));
    }

    /**
     * Requirement 1 & 2: Cashfree card displays UPCOMING and INSTANT is not shown for Cashfree.
     */
    public function test_cashfree_card_displays_upcoming_badge_and_not_instant_on_public_donations_page(): void
    {
        $this->createCampaign();

        $response = $this->get('/donations');

        $response->assertStatus(200);
        $response->assertSee('Cashfree Payments');
        $response->assertSee('UPCOMING');
        $response->assertSee('Cashfree Payments is coming soon. Please use Razorpay to continue.');

        // Verify the Cashfree card does not have Instant badge
        $content = $response->getContent();
        $this->assertStringContainsString('UPCOMING', $content);
        $this->assertStringNotContainsString('<span class="text-[9px] font-black bg-orange-200 text-orange-900 px-2 py-0.5 rounded uppercase">Instant</span>', $content);
    }

    /**
     * Requirement 3: Razorpay remains available and is selected by default in UI.
     */
    public function test_razorpay_is_available_and_selected_by_default_in_gateway_selector(): void
    {
        $this->createCampaign();

        $response = $this->get('/donations');

        $response->assertStatus(200);
        $response->assertSee('Razorpay Payments');
        $response->assertSee('Verified');
        $response->assertSee('value="razorpay" id="radio_razorpay" checked', false);
        $response->assertSee('value="cashfree" id="radio_cashfree" disabled', false);
    }

    /**
     * Requirement 4 & 5: Crafted POST to cashfree initiation is rejected server-side while gated.
     */
    public function test_crafted_cashfree_initiation_post_is_rejected_server_side_when_gated(): void
    {
        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name'  => 'Devotee Tester',
            'email'       => 'tester@abvhps.org',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Cashfree Payments is coming soon. Please use Razorpay to continue.',
        ]);

        // Assert no donation record was persisted
        $this->assertDatabaseMissing('donations', [
            'email' => 'tester@abvhps.org',
        ]);
    }

    /**
     * Requirement 6: Cashfree Create Order API is NOT called when gated.
     */
    public function test_cashfree_create_order_service_and_http_api_never_called_when_gated(): void
    {
        Http::fake();

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name'  => 'Devotee Tester',
            'email'       => 'tester2@abvhps.org',
            'phone'       => '9876543210',
            'amount'      => 1000,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(422);

        // Zero outgoing HTTP calls to Cashfree API
        Http::assertNothingSent();
    }

    /**
     * Requirement 7: User receives exact advisory message.
     */
    public function test_advisory_message_text_exact_match(): void
    {
        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name' => 'Devotee',
            'email'      => 'devotee@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 100,
        ]);

        $response->assertJsonPath('message', 'Cashfree Payments is coming soon. Please use Razorpay to continue.');
    }

    /**
     * Requirement 8: Razorpay payment flow regression tests remain passing.
     */
    public function test_razorpay_flow_creates_order_and_persists_pending_donation(): void
    {
        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'Bhakta Rama',
            'email'       => 'rama@abvhps.org',
            'phone'       => '9876543210',
            'amount'      => 2500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'gateway' => 'razorpay',
        ]);

        $this->assertDatabaseHas('donations', [
            'name'            => 'BHAKTA RAMA',
            'email'           => 'rama@abvhps.org',
            'amount'          => 2500.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'pending',
        ]);
    }

    /**
     * Requirement 9: Cashfree Secure ID verification suite remains untouched and functional.
     */
    public function test_cashfree_secure_id_verification_remains_unaffected_by_payment_gating(): void
    {
        Config::set('services.cashfree.verify_client_id', 'CF_TEST_ID');
        Config::set('services.cashfree.verify_client_secret', 'CF_TEST_SECRET');
        Config::set('services.cashfree.verification_base_url', 'https://sandbox.cashfree.com/verification');

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => Http::response([
                'status'           => 'SUCCESS',
                'valid'            => true,
                'registered_name'  => 'SRI RAMA CHANDRA',
                'pan'              => 'ABCDE1234F',
                'reference_id'     => 998877,
                'type'             => 'Individual',
            ], 200),
        ]);

        $secureIdService = new CashfreeSecureIdService();
        $result = $secureIdService->verifyPan('ABCDE1234F', 'SRI RAMA CHANDRA');

        $this->assertTrue($result['success']);
        $this->assertEquals('VALID', $result['status']);
        $this->assertEquals('SRI RAMA CHANDRA', $result['verified_name']);
    }

    /**
     * Requirement 10: Donation amount rules remain unchanged (₹1 min, ₹500,000 max).
     */
    public function test_donation_amount_validation_rules_remain_strictly_enforced(): void
    {
        // Reject ₹0
        $resZero = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Zero Donor',
            'email'      => 'zero@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 0,
        ]);
        $resZero->assertStatus(422);
        $resZero->assertJsonValidationErrors(['amount']);

        // Reject ₹0.50
        $resFraction = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Fraction Donor',
            'email'      => 'frac@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 0.5,
        ]);
        $resFraction->assertStatus(422);
        $resFraction->assertJsonValidationErrors(['amount']);

        // Accept ₹1
        $resOne = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'One Rupee Donor',
            'email'      => 'one@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 1,
        ]);
        $resOne->assertStatus(200);

        // Reject ₹500,001
        $resOver = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Over Max Donor',
            'email'      => 'over@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 500001,
        ]);
        $resOver->assertStatus(422);
        $resOver->assertJsonValidationErrors(['amount']);
    }
}
