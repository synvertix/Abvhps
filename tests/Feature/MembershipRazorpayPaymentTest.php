<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Models\Membership;

class MembershipRazorpayPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected string $keyId = 'rzp_test_key_id_12345';
    protected string $keySecret = 'rzp_test_key_secret_67890';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.razorpay.key_id', $this->keyId);
        Config::set('services.razorpay.key_secret', $this->keySecret);
        Config::set('services.razorpay.webhook_secret', 'rzp_test_webhook_secret_999');

        Config::set('services.cashfree.verify_client_id', 'CF_TEST_VERIFY_CLIENT_ID_12345');
        Config::set('services.cashfree.verify_client_secret', 'cfsk_test_verify_secret_key_67890');
        Config::set('services.cashfree.verification_base_url', 'https://sandbox.cashfree.com/verification');
    }

    /**
     * Test 1: OTP verified user is directed to payment before application.
     */
    public function test_otp_verified_user_is_directed_to_payment_before_application(): void
    {
        $phone = '9876543210';
        $otp = '123456';

        \Illuminate\Support\Facades\DB::table('phone_verifications')->insert([
            'phone'       => $phone,
            'otp'         => $otp,
            'is_verified' => false,
            'expired_at'  => now()->addMinutes(5),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $response = $this->post('/membership/verify-otp', [
            'phone' => $phone,
            'otp'   => $otp,
        ]);

        $response->assertRedirect('/membership/payment');
    }

    /**
     * Test 2: Unpaid user cannot manually open /membership/application.
     */
    public function test_unpaid_user_cannot_manually_open_application(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'          => $phone,
            'payment_status' => 'pending',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/application');

        $response->assertRedirect('/membership/payment');
    }

    /**
     * Test 3: Unpaid user cannot POST /membership/aadhaar/start.
     */
    public function test_unpaid_user_cannot_post_aadhaar_start(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'          => $phone,
            'payment_status' => 'pending',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Membership payment is required before Aadhaar verification.',
        ]);
    }

    /**
     * Test 4: Unpaid user cannot use Aadhaar status/callback to bypass payment.
     */
    public function test_unpaid_user_cannot_use_aadhaar_status_or_callback_to_bypass_payment(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'          => $phone,
            'payment_status' => 'pending',
        ]);

        $statusResponse = $this->withSession(['verified_membership_phone' => $phone])
            ->getJson('/membership/aadhaar/status');

        $statusResponse->assertStatus(403);

        $callbackResponse = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/aadhaar/callback');

        $callbackResponse->assertRedirect('/membership/payment');
    }

    /**
     * Test 5: Initiate endpoint requires verified phone session.
     */
    public function test_initiate_requires_verified_phone_session(): void
    {
        $response = $this->postJson('/membership/payment/razorpay/initiate', [
            'phone' => '9876543210',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 6: Initiate ignores any browser-supplied amount and forces 10000 paise (₹100.00).
     */
    public function test_initiate_forces_server_controlled_10000_paise(): void
    {
        $phone = '9876543210';

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id'       => 'order_RZP123456',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/initiate', [
                'amount' => 999999, // Attempting to pass custom amount
            ]);

        $response->assertOk();
        $response->assertJson([
            'success'      => true,
            'key_id'       => $this->keyId,
            'order_id'     => 'order_RZP123456',
            'amount_paise' => 10000,
            'currency'     => 'INR',
        ]);

        Http::assertSent(function ($request) {
            $data = json_decode($request->body(), true);
            return $data['amount'] === 10000;
        });
    }

    /**
     * Test 7: Initiate never marks payment success.
     */
    public function test_initiate_never_marks_payment_success(): void
    {
        $phone = '9876543210';

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_RZP123456',
            ], 200),
        ]);

        $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/initiate');

        $member = Membership::where('phone', $phone)->first();
        $this->assertEquals('pending', $member->payment_status);
        $this->assertNull($member->payment_verified_at);
        $this->assertNull($member->membership_id);
    }

    /**
     * Test 8: Razorpay order ID is stored server-side.
     */
    public function test_razorpay_order_id_stored_serverside(): void
    {
        $phone = '9876543210';

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id' => 'order_RZP999888',
            ], 200),
        ]);

        $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/initiate');

        $member = Membership::where('phone', $phone)->first();
        $this->assertEquals('order_RZP999888', $member->payment_order_id);
        $this->assertEquals('razorpay', $member->payment_gateway);
        $this->assertEquals(100.00, (float) $member->payment_amount);
    }

    /**
     * Test 9: Missing Razorpay credentials fail closed for membership.
     */
    public function test_missing_razorpay_credentials_fail_closed(): void
    {
        Config::set('services.razorpay.key_id', '');
        Config::set('services.razorpay.key_secret', '');

        $phone = '9876543210';

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/initiate');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Test 10: Fake browser success without signature cannot mark paid.
     */
    public function test_fake_browser_success_without_signature_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => 'order_RZP123',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => 'pay_FAKE123',
                // signature missing
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 11: Wrong signature cannot mark paid.
     */
    public function test_wrong_signature_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => 'order_RZP123',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => 'pay_REAL123',
                'razorpay_signature'  => 'invalid_hmac_signature',
                'razorpay_order_id'    => 'order_RZP123',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }

    /**
     * Test 12: Browser order ID mismatch cannot mark paid.
     */
    public function test_browser_order_id_mismatch_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => 'order_SERVER_123',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => 'pay_123',
                'razorpay_signature'  => 'signature',
                'razorpay_order_id'    => 'order_TAMPERED_456',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment order ID mismatch.',
        ]);
    }

    /**
     * Test 13: Valid signature but Razorpay status=authorized cannot mark paid.
     */
    public function test_status_authorized_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_RZP123';
        $paymentId = 'pay_AUTHORIZED123';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'authorized', // Not captured!
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
                'razorpay_order_id'    => $orderId,
            ]);

        $response->assertStatus(422);

        $member = Membership::where('phone', $phone)->first();
        $this->assertEquals('pending', $member->payment_status);
    }

    /**
     * Test 14: Valid signature but status=failed cannot mark paid.
     */
    public function test_status_failed_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_RZP123';
        $paymentId = 'pay_FAILED123';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'failed',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 15: Valid signature but amount != 10000 cannot mark paid.
     */
    public function test_amount_mismatch_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_RZP123';
        $paymentId = 'pay_WRONG_AMT';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 5000, // ₹50 instead of ₹100
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 16: Valid signature but currency != INR cannot mark paid.
     */
    public function test_currency_mismatch_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_RZP123';
        $paymentId = 'pay_USD';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'USD',
                'order_id' => $orderId,
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 17: Valid signature but payment order ID mismatch cannot mark paid.
     */
    public function test_payment_order_id_mismatch_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_DB_123';
        $paymentId = 'pay_ORDER_MISMATCH';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => 'order_DIFFERENT_999',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 18: Provider HTTP failure cannot mark paid.
     */
    public function test_provider_http_failure_cannot_mark_paid(): void
    {
        $phone = '9876543210';
        $orderId = 'order_HTTP_FAIL';
        $paymentId = 'pay_HTTP_FAIL';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response(null, 500),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 19: CAPTURED + valid signature + correct amount/order/currency marks payment success.
     */
    public function test_captured_status_marks_payment_success(): void
    {
        $phone = '9876543210';
        $orderId = 'order_SUCCESS123';
        $paymentId = 'pay_CAPTURED123';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
            "https://api.razorpay.com/v1/orders/{$orderId}" => Http::response([
                'id'       => $orderId,
                'status'   => 'paid',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
                'razorpay_order_id'    => $orderId,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success'      => true,
            'redirect_url' => '/membership/application',
        ]);

        $member = Membership::where('phone', $phone)->first();
        $this->assertEquals('success', $member->payment_status);
        $this->assertEquals('razorpay', $member->payment_gateway);
        $this->assertEquals($paymentId, $member->payment_id);
        $this->assertEquals(100.00, (float) $member->payment_amount);
        $this->assertNotNull($member->payment_verified_at);
    }

    /**
     * Test 20: Successful verified payment generates 12-digit membership ID.
     */
    public function test_successful_payment_generates_12_digit_membership_id(): void
    {
        $phone = '9876543210';
        $orderId = 'order_GEN_ID';
        $paymentId = 'pay_GEN_ID';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
            "https://api.razorpay.com/v1/orders/{$orderId}" => Http::response([
                'id'       => $orderId,
                'status'   => 'paid',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $member = Membership::where('phone', $phone)->first();
        $this->assertNotNull($member->membership_id);
        $this->assertEquals(12, strlen($member->membership_id));
        $this->assertMatchesRegularExpression('/^\d{12}$/', $member->membership_id);
    }

    /**
     * Test 21: Duplicate verification is idempotent and does not regenerate membership ID.
     */
    public function test_duplicate_verification_is_idempotent(): void
    {
        $phone = '9876543210';
        $orderId = 'order_IDEMPOTENT';
        $paymentId = 'pay_IDEMPOTENT';
        $existingMemId = '999888777666';

        Membership::create([
            'membership_id'       => $existingMemId,
            'phone'               => $phone,
            'payment_status'      => 'pending',
            'payment_order_id'    => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
            "https://api.razorpay.com/v1/orders/{$orderId}" => Http::response([
                'id'       => $orderId,
                'status'   => 'paid',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
            ]);

        $response->assertOk();
        $member = Membership::where('phone', $phone)->first();
        $this->assertEquals($existingMemId, $member->membership_id);
    }

    /**
     * Test 22: Same Razorpay payment ID cannot pay two different memberships.
     */
    public function test_same_payment_id_cannot_pay_two_memberships(): void
    {
        $paymentId = 'pay_REUSED_123';

        // Member 1 paid
        Membership::create([
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => $paymentId,
            'payment_order_id'    => 'order_MEMBER1',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        // Member 2 trying to verify using same paymentId
        $phone2 = '8765432109';
        $orderId2 = 'order_MEMBER2';

        Membership::create([
            'phone'            => $phone2,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId2,
        ]);

        $signature2 = hash_hmac('sha256', $orderId2 . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 10000,
                'currency' => 'INR',
                'order_id' => $orderId2,
            ], 200),
            "https://api.razorpay.com/v1/orders/{$orderId2}" => Http::response([
                'id'       => $orderId2,
                'status'   => 'paid',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone2])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature2,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 23: Legacy /membership/process-payment route is removed.
     */
    public function test_legacy_process_payment_route_removed(): void
    {
        $phone = '9876543210';

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->post('/membership/process-payment');

        $response->assertNotFound();
    }

    /**
     * Test 24: Paid user can reach membership application.
     */
    public function test_paid_user_can_reach_membership_application(): void
    {
        $phone = '9876543210';
        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_VERIFIED_99',
            'payment_order_id'    => 'order_VERIFIED_99',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/identity');

        $response->assertOk();
    }

    /**
     * Test 25: Paid user can start DigiLocker Aadhaar verification.
     */
    public function test_paid_user_can_start_digilocker_aadhaar_verification(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_VERIFIED_99',
            'payment_order_id'    => 'order_VERIFIED_99',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/aadhaar/start', [
                'user_flow' => 'web',
            ]);

        // Returns either redirect (if configured) or gateway status
        $this->assertTrue(in_array($response->status(), [200, 422], true));
    }

    /**
     * Test 26: Final application still requires identity verification in database.
     */
    public function test_final_application_still_requires_aadhaar_verification(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_VERIFIED_99',
            'payment_order_id'    => 'order_VERIFIED_99',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'is_aadhaar_verified' => false,
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/submit-membership', [
                'aadhaar_number' => '234567890123',
                'full_name'      => 'Test User',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Identity verification is required before submitting your membership application.',
        ]);
    }

    /**
     * Test 27: Existing completed historical member data is not destructively rewritten.
     */
    public function test_existing_completed_historical_member_data_preserved(): void
    {
        $phone = '9876543210';
        Membership::create([
            'membership_id'       => '111222333444',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_id'          => 'TXN-LEGACY-HISTORICAL',
            'is_completed'        => true,
            'is_aadhaar_verified' => true,
            'full_name'           => 'Historical Member',
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/application');

        $response->assertOk();
    }

    /**
     * Test 28: Incomplete legacy simulated-success record cannot bypass new real payment gate.
     */
    public function test_incomplete_legacy_simulated_success_cannot_bypass_payment_gate(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'          => $phone,
            'payment_status' => 'success',
            'payment_id'     => 'TXN-SIMULATED-OLD',
            'is_completed'   => false, // Incomplete legacy simulated record!
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/application');

        $response->assertRedirect('/membership/payment');
    }

    /**
     * Test 29: Historical completed member without new payment audit cannot start fresh DigiLocker verification.
     */
    public function test_historical_completed_member_cannot_start_digilocker_verification(): void
    {
        $phone = '9876543210';
        Membership::create([
            'membership_id'  => '111222333444',
            'phone'          => $phone,
            'payment_status' => 'success',
            'payment_id'     => 'TXN-HISTORICAL-OLD',
            'is_completed'   => true,
            // Null new Razorpay payment audit fields!
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Membership payment is required before Aadhaar verification.',
        ]);
    }

    /**
     * Test 30: Historical completed member cannot obtain already_paid=true from Razorpay initiation.
     */
    public function test_historical_completed_member_cannot_obtain_already_paid_from_initiation(): void
    {
        $phone = '9876543210';
        Membership::create([
            'membership_id'  => '111222333444',
            'phone'          => $phone,
            'payment_status' => 'success',
            'payment_id'     => 'TXN-HISTORICAL-OLD',
            'is_completed'   => true,
            // Null new Razorpay payment audit fields!
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id'       => 'order_HIST_INIT',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/initiate');

        $response->assertOk();
        $response->assertJsonMissing(['already_paid' => true]);
        $response->assertJson(['success' => true, 'order_id' => 'order_HIST_INIT']);
    }

    /**
     * Test 31: Old verifyAadhaar method cannot authorize using request phone fallback.
     */
    public function test_old_verify_aadhaar_cannot_authorize_using_request_phone(): void
    {
        $response = $this->postJson('/membership/verify-aadhaar', [
            'phone'          => '9876543210', // Request phone parameter without session
            'aadhaar_number' => '234567890123',
            'full_name'      => 'Test User',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Active membership session not found. Please verify your phone number first.',
        ]);
    }

    /**
     * Test 32: Razorpay order mismatch log masks the membership phone number.
     */
    public function test_razorpay_order_mismatch_log_masks_phone_number(): void
    {
        $phone = '9876543210';
        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => 'order_EXPECTED_123',
        ]);

        \Illuminate\Support\Facades\Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'XXXXXX3210') && !str_contains($message, '9876543210');
            });

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => 'pay_MISMATCH_999',
                'razorpay_signature'  => 'fake_sig',
                'razorpay_order_id'    => 'order_MISMATCH_456',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test 33: 100 paise (old ₹1 test fee) fails current payment verification.
     */
    public function test_100_paise_fails_current_payment_verification(): void
    {
        $phone = '9876543210';
        $orderId = 'order_RZP100PAISE';
        $paymentId = 'pay_100PAISE';

        Membership::create([
            'phone'            => $phone,
            'payment_status'   => 'pending',
            'payment_order_id' => $orderId,
        ]);

        $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

        Http::fake([
            "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                'id'       => $paymentId,
                'status'   => 'captured',
                'amount'   => 100, // Old ₹1 testing amount
                'currency' => 'INR',
                'order_id' => $orderId,
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->postJson('/membership/payment/razorpay/verify', [
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature'  => $signature,
                'razorpay_order_id'    => $orderId,
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Payment verification failed: payment status or amount mismatch.',
        ]);
    }

    /**
     * Test 34: 9900 paise (₹99) and 10100 paise (₹101) fail payment verification.
     */
    public function test_99_rupees_and_101_rupees_fail_payment_verification(): void
    {
        foreach ([9900, 10100] as $testAmount) {
            $phone = '98765432' . ($testAmount / 100);
            $orderId = 'order_RZP_' . $testAmount;
            $paymentId = 'pay_' . $testAmount;

            Membership::create([
                'phone'            => $phone,
                'payment_status'   => 'pending',
                'payment_order_id' => $orderId,
            ]);

            $signature = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);

            Http::fake([
                "https://api.razorpay.com/v1/payments/{$paymentId}" => Http::response([
                    'id'       => $paymentId,
                    'status'   => 'captured',
                    'amount'   => $testAmount,
                    'currency' => 'INR',
                    'order_id' => $orderId,
                ], 200),
            ]);

            $response = $this->withSession(['verified_membership_phone' => $phone])
                ->postJson('/membership/payment/razorpay/verify', [
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature'  => $signature,
                    'razorpay_order_id'    => $orderId,
                ]);

            $response->assertStatus(422);
        }
    }

    /**
     * Test 35: Genuine historical COMPLETED ₹1 verified record is accepted.
     */
    public function test_genuine_one_rupee_verified_record_accepted(): void
    {
        $phone = '9876543210';
        $member = Membership::create([
            'membership_id'       => '100020003000',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_VERIFIED_1RUPEE',
            'payment_order_id'    => 'order_VERIFIED_1RUPEE',
            'payment_amount'      => 1.00,
            'payment_verified_at' => now(),
            'is_completed'        => true, // Explicitly represents historical completed record
        ]);

        $this->assertTrue(\App\Http\Controllers\MembershipController::hasVerifiedMembershipPayment($member));

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/identity');

        $response->assertOk();
    }

    /**
     * Test 36: Genuine current ₹100 verified record is accepted.
     */
    public function test_genuine_current_100_rupee_verified_record_accepted(): void
    {
        $phone = '9876543210';
        $member = Membership::create([
            'membership_id'       => '100020003001',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_VERIFIED_100RUPEE',
            'payment_order_id'    => 'order_VERIFIED_100RUPEE',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'is_completed'        => false, // Unlocks ongoing flow even before completion
        ]);

        $this->assertTrue(\App\Http\Controllers\MembershipController::hasVerifiedMembershipPayment($member));

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/identity');

        $response->assertOk();
    }

    /**
     * Test 37: Arbitrary stored payment amount (e.g. ₹50) is rejected.
     */
    public function test_arbitrary_stored_payment_amount_rejected(): void
    {
        $phone = '9876543210';
        $member = Membership::create([
            'membership_id'       => '100020003002',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_ARBITRARY_50',
            'payment_order_id'    => 'order_ARBITRARY_50',
            'payment_amount'      => 50.00,
            'payment_verified_at' => now(),
        ]);

        $this->assertFalse(\App\Http\Controllers\MembershipController::hasVerifiedMembershipPayment($member));

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/application');

        $response->assertRedirect('/membership/payment');
    }

    /**
     * Test 38: Membership payment page displays ₹100.00 and does not display ₹1.00 payment CTA.
     */
    public function test_membership_payment_page_displays_100_rupee_and_not_1_rupee_cta(): void
    {
        $phone = '9876543210';

        $response = $this->withSession(['verified_membership_phone' => $phone])
            ->get('/membership/payment');

        $response->assertOk();
        $response->assertSee('₹100.00');
        $response->assertSee('₹100 membership fee');
        $response->assertSee('Pay ₹100 Securely Now');
        $response->assertDontSee('Pay ₹1 Securely Now');
        $response->assertDontSee('₹1.00');
    }

    /**
     * Test 39: Incomplete audited ₹1 record (is_completed=false or 0) is REJECTED from payment gate.
     */
    public function test_incomplete_audited_one_rupee_record_rejected_from_payment_gate(): void
    {
        // Case A: is_completed = false
        $phoneA = '9876543210';
        $memberA = Membership::create([
            'membership_id'       => '100020003099',
            'phone'               => $phoneA,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_AUDITED_1RUPEE_INCOMPLETE',
            'payment_order_id'    => 'order_AUDITED_1RUPEE_INCOMPLETE',
            'payment_amount'      => 1.00,
            'payment_verified_at' => now(),
            'is_completed'        => false,
        ]);

        $this->assertFalse(\App\Http\Controllers\MembershipController::hasVerifiedMembershipPayment($memberA));

        $responseA = $this->withSession(['verified_membership_phone' => $phoneA])
            ->get('/membership/application');
        $responseA->assertRedirect('/membership/payment');

        // Case B: default created record without completion
        $phoneB = '9876543211';
        $memberB = Membership::create([
            'membership_id'       => '100020003098',
            'phone'               => $phoneB,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_AUDITED_1RUPEE_DEFAULT',
            'payment_order_id'    => 'order_AUDITED_1RUPEE_DEFAULT',
            'payment_amount'      => 1.00,
            'payment_verified_at' => now(),
        ]);

        $this->assertFalse(\App\Http\Controllers\MembershipController::hasVerifiedMembershipPayment($memberB));

        $responseB = $this->withSession(['verified_membership_phone' => $phoneB])
            ->get('/membership/application');
        $responseB->assertRedirect('/membership/payment');
    }
}
