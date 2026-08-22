<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CashfreePaymentService;
use App\Models\Membership;
use App\Models\Donation;
use App\Models\ExamApplication;
use App\Models\ExamSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class CashfreeSandboxVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Configuration Presence & Sandbox URL Routing
     */
    public function test_cashfree_configuration_and_sandbox_mode(): void
    {
        $env = config('services.cashfree.environment', 'sandbox');
        $apiVersion = config('services.cashfree.api_version', '2023-08-01');

        $this->assertEquals('sandbox', strtolower((string)$env), 'CASHFREE_ENV must be sandbox');
        $this->assertEquals('2023-08-01', $apiVersion, 'CASHFREE_API_VERSION must be 2023-08-01');

        $service = new CashfreePaymentService();
        $this->assertInstanceOf(CashfreePaymentService::class, $service);
    }

    /**
     * Test 2: Cashfree Sandbox API Connectivity
     */
    public function test_cashfree_sandbox_api_connectivity(): void
    {
        $res = Http::withHeaders([
                'x-client-id' => config('services.cashfree.app_id', 'TEST_CLIENT_ID'),
                'x-client-secret' => config('services.cashfree.secret_key', 'TEST_SECRET_KEY'),
                'x-api-version' => config('services.cashfree.api_version', '2023-08-01'),
            ])
            ->get('https://sandbox.cashfree.com/pg/orders/PING_CHECK_' . time());

        $this->assertNotNull($res, 'Cashfree Sandbox endpoint must be reachable');
        // Sandbox API responds with HTTP 401 if credentials invalid/missing, or 404/200 if authenticated
        $this->assertContains($res->status(), [200, 401, 404], 'Sandbox endpoint responded with valid HTTP status code');
    }

    /**
     * Test 3: Existing Service Order Creation
     */
    public function test_existing_cashfree_service_order_creation(): void
    {
        $service = new CashfreePaymentService();
        $testOrderId = 'ABVHPS_TEST_' . time() . '_' . rand(100, 999);
        $testAmount = 100.00;
        $customerDetails = [
            'customer_id' => 'DEVOTEE_' . time(),
            'customer_name' => 'ABVHPS Devotee',
            'customer_email' => 'devotee@abvhps.org',
            'customer_phone' => '9876543210',
        ];
        $returnUrl = 'https://abvhps.org/membership/payment';

        $result = $service->createOrder($testOrderId, $testAmount, $customerDetails, $returnUrl);

        $this->assertTrue($result['success'], 'Order creation must return success=true');
        $this->assertEquals($testOrderId, $result['order_id']);
        $this->assertNotEmpty($result['payment_session_id']);
    }

    /**
     * Test 4: Existing Service Get Order Status
     */
    public function test_existing_cashfree_service_get_order_status(): void
    {
        $service = new CashfreePaymentService();
        $statusResult = $service->getOrderStatus('ORD_TEST_STATUS_CHECK');

        $this->assertIsArray($statusResult);
        $this->assertArrayHasKey('success', $statusResult);
    }

    /**
     * Test 5: Membership Payment Flow State Management
     */
    public function test_membership_payment_flow_record_integrity(): void
    {
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', 'rzp_test_123');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', 'rzp_secret_123');

        $testPhone = '9876543210';

        DB::table('phone_verifications')->insert([
            'phone' => $testPhone,
            'otp' => '123456',
            'is_verified' => true,
            'expired_at' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        Http::fake([
            'https://api.razorpay.com/v1/orders' => Http::response([
                'id'       => 'order_SB_123',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => $testPhone])
            ->postJson('/membership/payment/razorpay/initiate');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $member = Membership::where('phone', $testPhone)->first();
        $this->assertNotNull($member);
        $this->assertEquals('pending', $member->payment_status);
        $this->assertEquals('order_SB_123', $member->payment_order_id);
    }

    /**
     * Test 6: Donation Flow Record Integrity & Receipt Verification
     */
    public function test_donation_flow_record_integrity(): void
    {
        $testDonation = Donation::create([
            'name' => 'SRI RAMA BHAKTA DEVOTEE',
            'guardian' => 'DASARATHA',
            'amount' => 1116.00,
            'pan_number' => 'ABCDE1234F',
            'contact' => '9876543210',
            'about' => 'GOSALA SAMRAKSHANA SEVA'
        ]);

        $this->assertNotNull($testDonation->id);
        $this->assertEquals(1116.00, (float)$testDonation->amount);

        $receiptResponse = $this->get('/admin/donations/' . $testDonation->id . '/receipt');
        $receiptResponse->assertStatus(200);
        $this->assertStringContainsString('ABVHPS-TXN-', $receiptResponse->getContent());
        $this->assertStringContainsString('SRI RAMA BHAKTA DEVOTEE', $receiptResponse->getContent());
        $this->assertStringContainsString('1,116.00', $receiptResponse->getContent());
    }

    /**
     * Test 7: Exam Payment Verification Gate & Amount Handling
     */
    public function test_exam_payment_verification_gate_and_process(): void
    {
        // Unverified parent IDs rejected
        $response = $this->postJson('/exam-application/process-payment', [
            'guardian_type' => 'parents',
            'father_membership_id' => '000000000000',
            'mother_membership_id' => '000000000001'
        ]);

        $response->assertStatus(422);
        $this->assertFalse($response->json('success'));

        // Verified authority / bypass node accepted
        $responseSuccess = $this->postJson('/exam-application/process-payment', [
            'guardian_type' => 'parents',
            'father_membership_id' => '662424000000',
            'mother_membership_id' => '773434000000'
        ]);

        $responseSuccess->assertStatus(200);
        $this->assertTrue($responseSuccess->json('success'));
        $this->assertNotNull($responseSuccess->json('transaction_id'));
    }
}
