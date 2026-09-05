<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Donation;
use App\Models\FundraisingCampaign;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\CashfreePaymentService;
use App\Services\RazorpayPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DonationPaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function tearDown(): void
    {
        $this->flushSession();
        parent::tearDown();
    }

    /**
     * Helper to make POST requests with faked gateway HTTP and matching CSRF header.
     */
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

    /**
     * Helper to create a test campaign.
     */
    protected function createCampaign(array $overrides = []): FundraisingCampaign
    {
        return FundraisingCampaign::create(array_merge([
            'title'         => 'TEMPLE RENOVATION AKKALREDDYPALLI',
            'description'   => 'Sanatana Dharma temple preservation and consecration fund.',
            'target_amount' => 500000.00,
            'raised_amount' => 10000.00,
            'end_date'      => Carbon::now()->addMonths(6)->toDateString(),
            'cover_image'   => 'campaigns/covers/sample.jpg',
            'status'        => 'active',
        ], $overrides));
    }

    // =========================================================================
    // 1. PUBLIC DONATION PAGE TESTS
    // =========================================================================

    public function test_public_donation_page_renders_successfully(): void
    {
        $campaign = $this->createCampaign();

        $response = $this->get('/donations');

        $response->assertStatus(200);
        $response->assertSee('Support ABVHPS');
        $response->assertSee('Active Fundraising Campaigns');
        $response->assertSee('TEMPLE RENOVATION AKKALREDDYPALLI');
        $response->assertSee('Cashfree Payments');
        $response->assertSee('UPCOMING');
        $response->assertSee('Razorpay Payments');
        $response->assertSee('Donation Amount');
        $response->assertSee('Cashfree Payments is coming soon. Please use Razorpay to continue.');
    }

    public function test_single_campaign_page_renders_with_og_tags(): void
    {
        $campaign = $this->createCampaign();

        $response = $this->get('/donations/campaign/' . $campaign->id);

        $response->assertStatus(200);
        $response->assertSee($campaign->title);
        $response->assertSee($campaign->public_url);
    }

    // =========================================================================
    // 2. CASHFREE GATING & ORDER CREATION TESTS
    // =========================================================================

    public function test_cashfree_order_initiation_blocked_when_gated(): void
    {
        config(['services.cashfree.payments_enabled' => false]);
        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name'  => 'Sri Rama Devotee',
            'email'       => 'devotee@abvhps.org',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
            'pan_number'  => 'ABCDE1234F',
            'guardian'    => 'Dasaratha',
            'message'     => 'Temple Seva Contribution',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Cashfree Payments is coming soon. Please use Razorpay to continue.',
        ]);

        $this->assertDatabaseMissing('donations', [
            'email' => 'devotee@abvhps.org',
        ]);
    }

    public function test_cashfree_order_initiation_creates_pending_donation_when_enabled(): void
    {
        config(['services.cashfree.payments_enabled' => true]);
        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name'  => 'Sri Rama Devotee',
            'email'       => 'devotee@abvhps.org',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
            'pan_number'  => 'ABCDE1234F',
            'guardian'    => 'Dasaratha',
            'message'     => 'Temple Seva Contribution',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'gateway' => 'cashfree',
        ]);

        $this->assertDatabaseHas('donations', [
            'name'            => 'SRI RAMA DEVOTEE',
            'email'           => 'devotee@abvhps.org',
            'phone'           => '9876543210',
            'amount'          => 500.00,
            'payment_gateway' => 'cashfree',
            'payment_status'  => 'pending',
            'campaign_id'     => $campaign->id,
        ]);
    }

    // =========================================================================
    // 3. RAZORPAY ORDER CREATION TESTS
    // =========================================================================

    public function test_razorpay_order_initiation_creates_pending_donation(): void
    {
        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'Lakshmana Bhakta',
            'email'       => 'lakshmana@abvhps.org',
            'phone'       => '9123456789',
            'amount'      => 1000,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'gateway' => 'razorpay',
        ]);

        $this->assertDatabaseHas('donations', [
            'name'            => 'LAKSHMANA BHAKTA',
            'email'           => 'lakshmana@abvhps.org',
            'amount'          => 1000.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'pending',
        ]);
    }

    // =========================================================================
    // 4. AMOUNT VALIDATION & TAMPERING PROTECTION (₹1 MINIMUM)
    // =========================================================================

    public function test_accepts_donation_amount_one_rupee(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Rupee Bhakta',
            'email'      => 'rupee@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'gateway' => 'razorpay']);

        $this->assertDatabaseHas('donations', [
            'email'  => 'rupee@abvhps.org',
            'amount' => 1.00,
        ]);
    }

    public function test_rejects_amount_below_one_rupee(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bhakta',
            'email'      => 'bhakta@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 0.5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
        $response->assertJsonFragment(['Minimum donation amount is ₹1.']);
    }

    public function test_rejects_amount_zero(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bhakta',
            'email'      => 'bhakta@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_rejects_negative_amount(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bhakta',
            'email'      => 'bhakta@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => -10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_accepts_five_lakh_maximum_donation(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Major Donor',
            'email'      => 'major@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 500000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('donations', [
            'email'  => 'major@abvhps.org',
            'amount' => 500000.00,
        ]);
    }

    public function test_rejects_amount_above_five_lakh(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bhakta',
            'email'      => 'bhakta@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 500001,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    public function test_cashfree_initiation_uses_one_rupee_donation_record(): void
    {
        config(['services.cashfree.payments_enabled' => true]);
        Http::fake();

        $response = $this->postDonationJson('/donations/initiate-cashfree', [
            'donor_name' => 'Cashfree One Rupee Donor',
            'email'      => 'cf1@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 1,
        ]);

        $response->assertStatus(200);

        $donation = Donation::where('email', 'cf1@abvhps.org')->first();
        $this->assertNotNull($donation);
        $this->assertEquals(1.00, $donation->amount);
    }

    public function test_razorpay_initiation_converts_one_rupee_correctly_to_100_paise(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Razorpay One Rupee Donor',
            'email'      => 'rzp1@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'gateway' => 'razorpay',
            'session_data' => [
                'amount_paise' => 100,
            ],
        ]);
    }

    public function test_browser_or_request_cannot_bypass_server_validation(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bypasser',
            'email'      => 'bypasser@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 0,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
        $this->assertDatabaseMissing('donations', [
            'email' => 'bypasser@abvhps.org',
        ]);
    }

    public function test_rejects_invalid_pan_number(): void
    {
        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name' => 'Bhakta',
            'email'      => 'bhakta@abvhps.org',
            'phone'      => '9876543210',
            'amount'     => 500,
            'pan_number' => 'INVALID_PAN_123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pan_number']);
    }

    // =========================================================================
    // 5. RAZORPAY SIGNATURE VERIFICATION TESTS
    // =========================================================================

    public function test_razorpay_signature_verification_success_marks_donation_paid(): void
    {
        $campaign = $this->createCampaign(['raised_amount' => 1000.00]);

        $donation = Donation::create([
            'name'             => 'ANJANEYA BHAKTA',
            'email'            => 'anjaneya@abvhps.org',
            'phone'            => '9999888877',
            'contact'          => '9999888877',
            'amount'           => 2500.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_TEST_123',
            'payment_status'   => 'pending',
            'campaign_id'      => $campaign->id,
        ]);

        $keySecret = config('services.razorpay.key_secret');
        $signature = $keySecret
            ? hash_hmac('sha256', 'order_RZP_TEST_123|pay_TEST_999', $keySecret)
            : 'mock_valid_signature';

        // In simulation mode (unconfigured), any signature is verified safely
        $response = $this->postDonationJson('/donations/verify-razorpay', [
            'donation_id'         => $donation->id,
            'razorpay_payment_id' => 'pay_TEST_999',
            'razorpay_signature'  => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'status' => 'paid']);

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);
        $this->assertEquals('pay_TEST_999', $donation->gateway_payment_id);
        $this->assertNotNull($donation->receipt_number);
        $this->assertNotNull($donation->paid_at);

        // Campaign raised_amount incremented atomically
        $campaign->refresh();
        $this->assertEquals(3500.00, (float) $campaign->raised_amount);
    }

    // =========================================================================
    // 6. CASHFREE WEBHOOK & IDEMPOTENCY
    // =========================================================================

    public function test_cashfree_webhook_confirms_payment_and_increments_campaign(): void
    {
        $secret = 'test_cf_secret_key_999';
        config(['services.cashfree.secret_key' => $secret]);

        $campaign = $this->createCampaign(['raised_amount' => 5000.00]);

        $donation = Donation::create([
            'name'             => 'BHARATA DEVOTEE',
            'email'            => 'bharata@abvhps.org',
            'phone'            => '9876543211',
            'contact'          => '9876543211',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-DON-CF-100',
            'payment_status'   => 'pending',
            'campaign_id'      => $campaign->id,
        ]);

        $webhookPayload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order' => [
                    'order_id'     => 'ABVHPS-DON-CF-100',
                    'order_amount' => 1000.00,
                ],
                'payment' => [
                    'cf_payment_id'  => 12345678,
                    'payment_status' => 'SUCCESS',
                    'payment_amount' => 1000.00,
                ]
            ]
        ];

        $rawBody   = json_encode($webhookPayload);
        $timestamp = (string) time();
        $signature = base64_encode(hash_hmac('sha256', $timestamp . $rawBody, $secret, true));

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(200);

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);
        $this->assertEquals('12345678', $donation->gateway_payment_id);

        $campaign->refresh();
        $this->assertEquals(6000.00, (float) $campaign->raised_amount);
    }

    public function test_duplicate_webhook_delivery_is_idempotent(): void
    {
        $secret = 'test_cf_secret_key_999';
        config(['services.cashfree.secret_key' => $secret]);

        $campaign = $this->createCampaign(['raised_amount' => 5000.00]);

        $donation = Donation::create([
            'name'             => 'SATRUGHNA DEVOTEE',
            'email'            => 'satrughna@abvhps.org',
            'phone'            => '9876543212',
            'contact'          => '9876543212',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-DON-CF-IDEMPOTENT',
            'payment_status'   => 'pending',
            'campaign_id'      => $campaign->id,
        ]);

        $webhookPayload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order'   => ['order_id' => 'ABVHPS-DON-CF-IDEMPOTENT'],
                'payment' => ['cf_payment_id' => 999888, 'payment_status' => 'SUCCESS'],
            ]
        ];

        $rawBody   = json_encode($webhookPayload);
        $timestamp = (string) time();
        $signature = base64_encode(hash_hmac('sha256', $timestamp . $rawBody, $secret, true));

        $headers = [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE'             => 'application/json',
        ];

        // Delivery 1
        $res1 = $this->call('POST', '/webhook/cashfree', [], [], [], $headers, $rawBody);
        $res1->assertStatus(200);

        $donation->refresh();
        $firstReceipt = $donation->receipt_number;
        $campaign->refresh();
        $this->assertEquals(6000.00, (float) $campaign->raised_amount);

        // Duplicate Delivery 2
        $res2 = $this->call('POST', '/webhook/cashfree', [], [], [], $headers, $rawBody);
        $res2->assertStatus(200);

        // Duplicate Delivery 3
        $res3 = $this->call('POST', '/webhook/cashfree', [], [], [], $headers, $rawBody);
        $res3->assertStatus(200);

        $donation->refresh();
        $campaign->refresh();

        // Must still be 6000 (NOT 7000 or 8000)
        $this->assertEquals(6000.00, (float) $campaign->raised_amount);
        // Receipt number must remain identical
        $this->assertEquals($firstReceipt, $donation->receipt_number);
    }

    // =========================================================================
    // 7. RAZORPAY WEBHOOK TESTS
    // =========================================================================

    public function test_razorpay_webhook_marks_donation_paid(): void
    {
        $secret = 'test_rzp_webhook_secret_888';
        config(['services.razorpay.webhook_secret' => $secret]);

        $campaign = $this->createCampaign(['raised_amount' => 20000.00]);

        $donation = Donation::create([
            'name'             => 'GITA BHAKTA',
            'email'            => 'gita@abvhps.org',
            'phone'            => '9876543299',
            'contact'          => '9876543299',
            'amount'           => 5000.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_WEBHOOK_1',
            'payment_status'   => 'pending',
            'campaign_id'      => $campaign->id,
        ]);

        $webhookPayload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => 'pay_RZP_CAPTURED_1',
                        'order_id' => 'order_RZP_WEBHOOK_1',
                        'amount'   => 500000, // 5000.00 in paise
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];

        $rawBody   = json_encode($webhookPayload);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $response = $this->call('POST', '/webhook/razorpay', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $rawBody);

        $response->assertStatus(200);

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);
        $this->assertEquals('pay_RZP_CAPTURED_1', $donation->gateway_payment_id);

        $campaign->refresh();
        $this->assertEquals(25000.00, (float) $campaign->raised_amount);
    }

    // =========================================================================
    // 8. PAYMENT STATUS PAGE
    // =========================================================================

    public function test_payment_status_page_displays_paid_state_with_receipt_button(): void
    {
        $donation = Donation::create([
            'name'             => 'DEVOTEE TESTING',
            'email'            => 'test@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'payment_status'   => 'paid',
            'receipt_number'   => 'ABVHPS-RCP-2026-000100',
            'paid_at'          => Carbon::now('Asia/Kolkata'),
        ]);

        $response = $this->withSession(['authorized_donation_ids' => [$donation->id]])
            ->get('/donations/status/' . $donation->id);

        $response->assertStatus(200);
        $response->assertSee('Payment Successful');
        $response->assertSee('DEVOTEE TESTING');
        $response->assertSee('1,000.00');
        $response->assertSee('ABVHPS-RCP-2026-000100');
        $response->assertSee('Download Official 80G Receipt');
        $response->assertSee('Thank You!');
        $response->assertDontSee('Dhanyavadagalu');
        $response->assertDontSee('target="_blank"');
    }

    // =========================================================================
    // 9. RECEIPT GENERATION TESTS
    // =========================================================================

    public function test_donation_receipt_contains_gateway_details_and_branding(): void
    {
        $donation = Donation::create([
            'name'               => 'RAMACHANDRA DEVOTEE',
            'guardian'           => 'DASARATHA MAHARAJA',
            'email'              => 'rama@abvhps.org',
            'phone'              => '9876543210',
            'contact'            => '9876543210',
            'pan_number'         => 'ABCDE1234F',
            'amount'             => 5000.00,
            'payment_gateway'    => 'razorpay',
            'gateway_payment_id' => 'pay_MOCK_RECEIPT_123',
            'payment_status'     => 'paid',
            'receipt_number'     => 'ABVHPS-TXN-000555',
            'paid_at'            => Carbon::now('Asia/Kolkata'),
        ]);

        $response = $this->withSession(['authorized_donation_ids' => [$donation->id]])
            ->get('/donations/receipt/' . $donation->id);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="ABVHPS-TXN-000555.html"');
        $response->assertSee('AKHANDA BHARATA VISWA HINDU PARIRAKSHANA SAMITI');
        $response->assertSee('Official Donation Receipt');
        $response->assertSee('RAMACHANDRA DEVOTEE');
        $response->assertSee('5,000.00');
        $response->assertSee('Razorpay');
        $response->assertSee('pay_MOCK_RECEIPT_123');
        $response->assertSee('PAID');
        $response->assertSee('Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193');
        $response->assertDontSee('Survey No:1035');
        $response->assertDontSee('Sasirekhapuram');
        $response->assertDontSee('Authorized Signatory');
        $response->assertDontSee('Central Administration Node Desk');
    }

    public function test_unauthorized_user_cannot_access_other_donation_status_or_receipt(): void
    {
        $donation = Donation::create([
            'name'               => 'SECRET DEVOTEE',
            'guardian'           => 'SECRET GUARDIAN',
            'email'              => 'secret@abvhps.org',
            'phone'              => '9876543210',
            'contact'            => '9876543210',
            'amount'             => 1000.00,
            'payment_gateway'    => 'razorpay',
            'payment_status'     => 'paid',
            'receipt_number'     => 'ABVHPS-RCP-2026-000999',
            'paid_at'            => Carbon::now('Asia/Kolkata'),
        ]);

        // Unauthorized user without session receives 404
        $this->get('/donations/status/' . $donation->id)->assertStatus(404);
        $this->get('/donations/receipt/' . $donation->id)->assertStatus(404);

        // User with different donation in session receives 404 for this donation
        $this->withSession(['authorized_donation_ids' => [99999]])
            ->get('/donations/status/' . $donation->id)
            ->assertStatus(404);
    }

    // =========================================================================
    // 10. ADMIN LEDGER FILTERING TESTS
    // =========================================================================

    public function test_admin_ledger_filters_by_gateway(): void
    {
        $admin = User::factory()->create();

        Donation::create([
            'name'            => 'CASHFREE DONOR',
            'contact'         => '9876543210',
            'amount'          => 500.00,
            'payment_gateway' => 'cashfree',
            'payment_status'  => 'paid',
        ]);

        Donation::create([
            'name'            => 'RAZORPAY DONOR',
            'contact'         => '9876543211',
            'amount'          => 1000.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'paid',
        ]);

        $response = $this->actingAs($admin)->get('/admin/donations?gateway=cashfree');

        $response->assertStatus(200);
        $response->assertSee('CASHFREE DONOR');
        $response->assertDontSee('RAZORPAY DONOR');
    }

    /**
     * Test: Donation amount field uses flex-based input group and removes absolute positioning hacks
     */
    public function test_donation_amount_input_uses_flex_group_and_no_absolute_positioning(): void
    {
        $response = $this->get('/donations');

        $response->assertStatus(200);
        $response->assertSee('Donation Amount (INR)');
        $response->assertSee('flex items-center w-full bg-gray-50 border-2 border-gray-200 rounded-2xl', false);
        $response->assertSee('id="donation_amount"', false);
        $response->assertSee('name="amount"', false);
        $response->assertSee('min="1"', false);
        $response->assertSee('max="500000"', false);
        $response->assertSee('aria-hidden="true"', false);
        $response->assertDontSee('absolute inset-y-0 left-0', false);
        $response->assertDontSee('preset-amount-button');
    }

    // =========================================================================
    // RAZORPAY CHECKOUT REGRESSION & CSP TESTS
    // =========================================================================

    public function test_donation_form_method_is_post_and_prevents_get_submission(): void
    {
        $response = $this->get('/donations');
        $response->assertStatus(200);

        // Form tag must declare method="POST" and preventDefault on submit
        $response->assertSee('<form id="donation_form" method="POST" action="' . route('donations.initiate_razorpay') . '" onsubmit="handleDonationSubmit(event); return false;"', false);

        // Gateway radio options: Razorpay active/checked, Cashfree disabled
        $response->assertSee('id="radio_razorpay" checked', false);
        $response->assertSee('id="radio_cashfree" disabled', false);

        // Razorpay SDK loaded
        $response->assertSee('https://checkout.razorpay.com/v1/checkout.js', false);
    }

    public function test_donation_javascript_has_valid_try_catch_structure(): void
    {
        $response = $this->get('/donations');
        $html = $response->getContent();

        // Extract <script> content
        preg_match('/<script>([\s\S]*?)<\/script>/', $html, $matches);
        $this->assertNotEmpty($matches, 'Inline payment script tag must be present');

        $script = $matches[1];

        // Must contain handleDonationSubmit
        $this->assertStringContainsString('async function handleDonationSubmit', $script);
        $this->assertStringContainsString('new Razorpay(options)', $script);
        $this->assertStringContainsString('rzp.open()', $script);

        // Ensure no stray double closing braces before catch block
        $this->assertStringNotContainsString("rzp.open();\n            }\n        } catch", $script);
        $this->assertStringNotContainsString("rzp.open();\r\n            }\r\n        } catch", $script);

        // Count balanced braces in handleDonationSubmit function body
        $startPos = strpos($script, 'async function handleDonationSubmit');
        $endPos = strpos($script, 'document.addEventListener', $startPos);
        $funcBody = substr($script, $startPos, $endPos - $startPos);

        $openBraces = substr_count($funcBody, '{');
        $closeBraces = substr_count($funcBody, '}');
        $this->assertEquals($openBraces, $closeBraces, "Braces must be perfectly balanced in handleDonationSubmit (found {$openBraces} open, {$closeBraces} close)");
    }

    public function test_csp_headers_permit_razorpay_cdn_and_checkout_origins(): void
    {
        $response = $this->get('/donations');
        $response->assertStatus(200);

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotEmpty($csp, 'Content-Security-Policy header must be present');

        // script-src must permit Razorpay CDN, Checkout and API
        $this->assertStringContainsString('https://cdn.razorpay.com', $csp);
        $this->assertStringContainsString('https://checkout.razorpay.com', $csp);
        $this->assertStringContainsString('https://api.razorpay.com', $csp);

        // frame-src must permit Razorpay Checkout and API
        $this->assertMatchesRegularExpression('/frame-src [^;]*https:\/\/checkout\.razorpay\.com/', $csp);
        $this->assertMatchesRegularExpression('/frame-src [^;]*https:\/\/api\.razorpay\.com/', $csp);
    }

    public function test_no_razorpay_key_secret_rendered_in_html(): void
    {
        $response = $this->get('/donations');
        $response->assertStatus(200);

        $secret = config('services.razorpay.key_secret');
        if ($secret && $secret !== 'your_razorpay_secret_here') {
            $response->assertDontSee($secret, false);
        }

        // Must not contain secret key patterns
        $response->assertDontSee('key_secret', false);
    }

    // =========================================================================
    // PRODUCTION SECURITY TESTS: RAZORPAY FAIL-CLOSED BEHAVIOR
    // =========================================================================

    public function test_production_with_valid_razorpay_credentials_creates_order(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', 'rzp_live_test_valid');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', 'rzp_live_secret_valid');

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'SANATANA DONOR',
            'email'       => 'donor@example.com',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'gateway'      => 'razorpay',
            'is_simulated' => false,
        ]);
        $response->assertJsonPath('session_data.key_id', 'rzp_live_test_valid');
        $response->assertJsonPath('session_data.razorpay_order_id', 'order_RZP_MOCK_123');
    }

    public function test_production_with_missing_key_id_fails_closed(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', '');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', 'rzp_live_secret_valid');

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'SANATANA DONOR',
            'email'       => 'donor@example.com',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment gateway is temporarily unavailable. Please try again later or contact support.',
        ]);

        $this->assertDatabaseHas('donations', [
            'name'           => 'SANATANA DONOR',
            'payment_status' => 'failed',
        ]);
    }

    public function test_production_with_missing_key_secret_fails_closed(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', 'rzp_live_test_valid');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', '');

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'SANATANA DONOR',
            'email'       => 'donor@example.com',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment gateway is temporarily unavailable. Please try again later or contact support.',
        ]);

        $this->assertDatabaseHas('donations', [
            'name'           => 'SANATANA DONOR',
            'payment_status' => 'failed',
        ]);
    }

    public function test_production_with_both_credentials_missing_fails_closed(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', '');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', '');

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'SANATANA DONOR',
            'email'       => 'donor@example.com',
            'phone'       => '9876543210',
            'amount'      => 500,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment gateway is temporarily unavailable. Please try again later or contact support.',
        ]);
    }

    public function test_production_simulated_payment_attempt_must_not_mark_donation_paid(): void
    {
        $this->app->detectEnvironment(fn() => 'production');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', '');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', '');

        $donation = Donation::create([
            'name'            => 'ATTEMPTED SIMULATOR',
            'email'           => 'attacker@example.com',
            'phone'           => '9876543210',
            'contact'         => '9876543210',
            'amount'          => 1000.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'pending',
            'gateway_order_id'=> 'order_SIMULATION_FAKE123',
        ]);

        $response = $this->postDonationJson('/donations/verify-razorpay', [
            'donation_id'         => $donation->id,
            'razorpay_payment_id' => 'SIM_PAY_123456789',
            'razorpay_signature'  => 'SIM_SIGNATURE',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'status'  => 'failed',
        ]);

        $freshDonation = $donation->fresh();
        $this->assertNotEquals('paid', $freshDonation->payment_status);
        $this->assertEquals('failed', $freshDonation->payment_status);
    }

    public function test_testing_environment_preserves_offline_simulation_if_unconfigured(): void
    {
        // When not in production and keys are intentionally unconfigured
        $this->app->detectEnvironment(fn() => 'testing');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', '');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', '');

        $campaign = $this->createCampaign();

        $response = $this->postDonationJson('/donations/initiate-razorpay', [
            'donor_name'  => 'LOCAL TESTER',
            'email'       => 'local@example.com',
            'phone'       => '9876543210',
            'amount'      => 200,
            'campaign_id' => $campaign->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'      => true,
            'is_simulated' => true,
        ]);
    }
}

