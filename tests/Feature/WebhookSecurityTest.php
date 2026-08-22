<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\FundraisingCampaign;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    protected function createCampaign(): FundraisingCampaign
    {
        return FundraisingCampaign::create([
            'title'         => 'TEMPLE RENOVATION AKKALREDDYPALLI',
            'description'   => 'Sanatana Dharma temple preservation.',
            'target_amount' => 500000.00,
            'raised_amount' => 10000.00,
            'end_date'      => Carbon::now()->addMonths(6)->toDateString(),
            'cover_image'   => 'campaigns/covers/sample.jpg',
            'status'        => 'active',
        ]);
    }

    // =========================================================================
    // CASHFREE WEBHOOK SECURITY TESTS
    // =========================================================================

    public function test_cashfree_webhook_valid_signature_accepted(): void
    {
        $secret = 'test_cashfree_secret_key_12345';
        config(['services.cashfree.secret_key' => $secret]);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE CASHFREE',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-CF-SECURE-001',
            'payment_status'   => 'pending',
        ]);

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order'   => ['order_id' => 'ABVHPS-CF-SECURE-001', 'order_amount' => 1000.00],
                'payment' => ['cf_payment_id' => 88877766, 'payment_status' => 'SUCCESS', 'payment_amount' => 1000.00],
            ]
        ];

        $rawBody   = json_encode($payload);
        $timestamp = (string) time();
        $signature = base64_encode(hash_hmac('sha256', $timestamp . $rawBody, $secret, true));

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);
        $this->assertEquals('88877766', $donation->gateway_payment_id);
    }

    public function test_cashfree_webhook_wrong_signature_rejected(): void
    {
        config(['services.cashfree.secret_key' => 'test_cashfree_secret_key_12345']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE CASHFREE',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-CF-SECURE-002',
            'payment_status'   => 'pending',
        ]);

        $payload = [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => [
                'order'   => ['order_id' => 'ABVHPS-CF-SECURE-002'],
                'payment' => ['cf_payment_id' => 111222, 'payment_status' => 'SUCCESS'],
            ]
        ];

        $rawBody   = json_encode($payload);
        $timestamp = (string) time();
        $signature = 'INVALID_SIGNATURE_BASE64==';

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_TIMESTAMP' => $timestamp,
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_cashfree_webhook_missing_signature_rejected(): void
    {
        config(['services.cashfree.secret_key' => 'test_cashfree_secret_key_12345']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE CASHFREE',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-CF-SECURE-003',
            'payment_status'   => 'pending',
        ]);

        $rawBody = json_encode(['data' => ['order' => ['order_id' => 'ABVHPS-CF-SECURE-003']]]);

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_TIMESTAMP' => (string) time(),
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_cashfree_webhook_missing_timestamp_rejected(): void
    {
        $secret = 'test_cashfree_secret_key_12345';
        config(['services.cashfree.secret_key' => $secret]);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE CASHFREE',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-CF-SECURE-004',
            'payment_status'   => 'pending',
        ]);

        $rawBody   = json_encode(['data' => ['order' => ['order_id' => 'ABVHPS-CF-SECURE-004']]]);
        $signature = base64_encode(hash_hmac('sha256', '12345' . $rawBody, $secret, true));

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_cashfree_webhook_missing_secret_rejected(): void
    {
        config(['services.cashfree.secret_key' => '']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE CASHFREE',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 1000.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'ABVHPS-CF-SECURE-005',
            'payment_status'   => 'pending',
        ]);

        $rawBody = json_encode(['data' => ['order' => ['order_id' => 'ABVHPS-CF-SECURE-005']]]);

        $response = $this->call('POST', '/webhook/cashfree', [], [], [], [
            'HTTP_X_WEBHOOK_TIMESTAMP' => (string) time(),
            'HTTP_X_WEBHOOK_SIGNATURE' => 'some_sig',
            'CONTENT_TYPE'             => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    // =========================================================================
    // RAZORPAY WEBHOOK SECURITY TESTS
    // =========================================================================

    public function test_razorpay_webhook_valid_signature_accepted(): void
    {
        $secret = 'test_razorpay_webhook_secret_67890';
        config(['services.razorpay.webhook_secret' => $secret]);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE RAZORPAY',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 2000.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_SECURE_001',
            'payment_status'   => 'pending',
        ]);

        $payload = [
            'event'   => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id'       => 'pay_RZP_SECURE_PAY_1',
                        'order_id' => 'order_RZP_SECURE_001',
                        'amount'   => 200000,
                        'status'   => 'captured',
                    ]
                ]
            ]
        ];

        $rawBody   = json_encode($payload);
        $signature = hash_hmac('sha256', $rawBody, $secret);

        $response = $this->call('POST', '/webhook/razorpay', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => $signature,
            'CONTENT_TYPE'              => 'application/json',
        ], $rawBody);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);
        $this->assertEquals('pay_RZP_SECURE_PAY_1', $donation->gateway_payment_id);
    }

    public function test_razorpay_webhook_wrong_signature_rejected(): void
    {
        config(['services.razorpay.webhook_secret' => 'test_razorpay_webhook_secret_67890']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE RAZORPAY',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 2000.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_SECURE_002',
            'payment_status'   => 'pending',
        ]);

        $payload = ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => ['order_id' => 'order_RZP_SECURE_002']]]];
        $rawBody = json_encode($payload);

        $response = $this->call('POST', '/webhook/razorpay', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => 'INVALID_HEX_SIGNATURE_123',
            'CONTENT_TYPE'              => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_razorpay_webhook_missing_signature_rejected(): void
    {
        config(['services.razorpay.webhook_secret' => 'test_razorpay_webhook_secret_67890']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE RAZORPAY',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 2000.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_SECURE_003',
            'payment_status'   => 'pending',
        ]);

        $rawBody = json_encode(['event' => 'payment.captured']);

        $response = $this->call('POST', '/webhook/razorpay', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_razorpay_webhook_missing_webhook_secret_rejected(): void
    {
        config(['services.razorpay.webhook_secret' => '']);

        $donation = Donation::create([
            'name'             => 'TEST DEVOTEE RAZORPAY',
            'email'            => 'devotee@abvhps.org',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 2000.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_RZP_SECURE_004',
            'payment_status'   => 'pending',
        ]);

        $rawBody = json_encode(['event' => 'payment.captured']);

        $response = $this->call('POST', '/webhook/razorpay', [], [], [], [
            'HTTP_X_RAZORPAY_SIGNATURE' => 'some_signature',
            'CONTENT_TYPE'              => 'application/json',
        ], $rawBody);

        $response->assertStatus(401);
        $donation->refresh();
        $this->assertEquals('pending', $donation->payment_status);
    }

    public function test_unsigned_webhook_requests_cannot_update_donation_payment_status(): void
    {
        config(['services.cashfree.secret_key' => 'secret_1']);
        config(['services.razorpay.webhook_secret' => 'secret_2']);

        $cfDonation = Donation::create([
            'name'             => 'UNSIGNED CF DONOR',
            'email'            => 'cf@test.com',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 500.00,
            'payment_gateway'  => 'cashfree',
            'gateway_order_id' => 'CF-UNSIGNED-101',
            'payment_status'   => 'pending',
        ]);

        $rzpDonation = Donation::create([
            'name'             => 'UNSIGNED RZP DONOR',
            'email'            => 'rzp@test.com',
            'phone'            => '9876543210',
            'contact'          => '9876543210',
            'amount'           => 500.00,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'RZP-UNSIGNED-102',
            'payment_status'   => 'pending',
        ]);

        // Unsigned Cashfree request
        $res1 = $this->postJson('/webhook/cashfree', [
            'type' => 'PAYMENT_SUCCESS_WEBHOOK',
            'data' => ['order' => ['order_id' => 'CF-UNSIGNED-101'], 'payment' => ['cf_payment_id' => 111, 'payment_status' => 'SUCCESS']]
        ]);
        $res1->assertStatus(401);

        // Unsigned Razorpay request
        $res2 = $this->postJson('/webhook/razorpay', [
            'event'   => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_111', 'order_id' => 'RZP-UNSIGNED-102', 'status' => 'captured']]]
        ]);
        $res2->assertStatus(401);

        $cfDonation->refresh();
        $rzpDonation->refresh();

        $this->assertEquals('pending', $cfDonation->payment_status);
        $this->assertEquals('pending', $rzpDonation->payment_status);
    }
}
