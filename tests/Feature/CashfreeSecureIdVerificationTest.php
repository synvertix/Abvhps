<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;
use App\Models\Membership;
use App\Services\CashfreeSecureIdService;

class CashfreeSecureIdVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.cashfree.verify_client_id', 'CF_TEST_VERIFY_CLIENT_ID_12345');
        Config::set('services.cashfree.verify_client_secret', 'cfsk_test_verify_secret_key_67890');
        Config::set('services.cashfree.verification_base_url', 'https://sandbox.cashfree.com/verification');
    }

    /**
     * Test 1: start rejects request phone when verified session absent.
     */
    public function test_start_rejects_request_phone_without_verified_session(): void
    {
        Membership::create([
            'membership_id'  => '123456789012',
            'phone'          => '9876543210',
            'payment_status' => 'success',
        ]);

        // Attempting to pass phone parameter without session('verified_membership_phone')
        $response = $this->postJson('/membership/aadhaar/start', [
            'aadhaar_number' => '234567890123',
            'phone'          => '9876543210',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Active membership session not found. Please verify your phone number first.',
        ]);
    }

    /**
     * Test 2: status rejects request phone when verified session absent.
     */
    public function test_status_rejects_request_phone_without_verified_session(): void
    {
        Membership::create([
            'membership_id'  => '123456789012',
            'phone'          => '9876543210',
            'payment_status' => 'success',
        ]);

        $response = $this->getJson('/membership/aadhaar/status?phone=9876543210');

        $response->assertStatus(401);
        $response->assertJson([
            'is_verified' => false,
            'message'     => 'No active membership phone session found.',
        ]);
    }

    /**
     * Test 3: submission rejects request phone when verified session absent.
     */
    public function test_submission_rejects_request_phone_without_verified_session(): void
    {
        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'is_aadhaar_verified' => true,
        ]);

        $response = $this->postJson('/submit-membership', [
            'phone'          => '9876543210',
            'aadhaar_number' => '234567890123',
            'full_name'      => 'Konda Reddy',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 4: account-check transport/provider failure does NOT create DigiLocker URL.
     */
    public function test_account_check_failure_does_not_create_digilocker_url(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status'  => 'FAILED',
                'message' => 'Cashfree gateway account check failed.',
            ], 400),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST4',
            'payment_order_id'    => 'order_TEST4',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Cashfree gateway account check failed.',
        ]);

        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/digilocker') && !str_contains($request->url(), '/verify-account');
        });
    }

    /**
     * Test 5: unknown account status fails closed.
     */
    public function test_unknown_account_status_fails_closed(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status' => 'UNEXPECTED_STATUS',
            ], 200),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST5',
            'payment_order_id'    => 'order_TEST5',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'DigiLocker account verification returned unrecognized status.',
        ]);
    }

    /**
     * Test 6: ACCOUNT_EXISTS => signin.
     */
    public function test_account_exists_selects_signin_flow(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status'       => 'ACCOUNT_EXISTS',
                'reference_id' => 'CF_REF_SIGNIN',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => Http::response([
                'status'       => 'PENDING',
                'reference_id' => 'CF_REF_SIGNIN',
                'url'          => 'https://digilocker.cashfree.com/signin?token=123',
            ], 200),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST6',
            'payment_order_id'    => 'order_TEST6',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/signin?token=123',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/digilocker')
                && str_contains($request->body(), '"user_flow":"signin"');
        });
    }

    /**
     * Test 7: ACCOUNT_NOT_FOUND => signup.
     */
    public function test_account_not_found_selects_signup_flow(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status'       => 'ACCOUNT_NOT_FOUND',
                'reference_id' => 'CF_REF_SIGNUP',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => Http::response([
                'status'       => 'PENDING',
                'reference_id' => 'CF_REF_SIGNUP',
                'url'          => 'https://digilocker.cashfree.com/signup?token=456',
            ], 200),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST7',
            'payment_order_id'    => 'order_TEST7',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/signup?token=456',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/digilocker')
                && str_contains($request->body(), '"user_flow":"signup"');
        });
    }

    /**
     * Test 8: verification ID does not contain membership ID, phone or Aadhaar.
     */
    public function test_verification_id_is_unpredictable_uuid_and_not_predictable(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status' => 'ACCOUNT_NOT_FOUND',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => Http::response([
                'status' => 'PENDING',
                'url'    => 'https://digilocker.cashfree.com/auth',
            ], 200),
        ]);

        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST8',
            'payment_order_id'    => 'order_TEST8',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/auth',
        ]);

        $verifId = session('digilocker_verification_id');
        $this->assertNotNull($verifId);
        $this->assertLessThanOrEqual(50, strlen($verifId));
        $this->assertStringStartsWith('ABV_', $verifId);
        $uuidPart = substr($verifId, strlen('ABV_'));
        $this->assertTrue(\Illuminate\Support\Str::isUuid($uuidPart));
        $this->assertStringNotContainsString('9876543210', $verifId);
        $this->assertStringNotContainsString('234567890123', $verifId);
        $this->assertStringNotContainsString('123456789012', $verifId);

        Http::assertSent(function ($request) use ($verifId) {
            if (str_contains($request->url(), '/verification/digilocker')) {
                $data = $request->data();
                return isset($data['verification_id'])
                    && $data['verification_id'] === $verifId
                    && strlen($data['verification_id']) <= 50
                    && str_starts_with($data['verification_id'], 'ABV_');
            }
            return false;
        });
    }

    /**
     * Test 9: raw Aadhaar is not stored directly in session (encrypted).
     */
    public function test_raw_aadhaar_is_not_stored_unencrypted_in_session(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status' => 'ACCOUNT_EXISTS',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => Http::response([
                'status' => 'PENDING',
                'url'    => 'https://digilocker.cashfree.com/auth',
            ], 200),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST9',
            'payment_order_id'    => 'order_TEST9',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/aadhaar/start', [
                'aadhaar_number' => '234567890123',
            ]);

        $this->assertNull(session('digilocker_aadhaar_number'));
        $encrypted = session('digilocker_aadhaar_encrypted');
        $this->assertNotNull($encrypted);
        $this->assertNotEquals('234567890123', $encrypted);
        $this->assertEquals('234567890123', Crypt::decryptString($encrypted));
    }

    /**
     * Test 10: callback cannot select another membership.
     */
    public function test_callback_cannot_select_another_membership(): void
    {
        $member1 = Membership::create([
            'membership_id'       => '111111111111',
            'phone'               => '9111111111',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_MEMBER1',
            'payment_order_id'    => 'order_MEMBER1',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $member2 = Membership::create([
            'membership_id'       => '222222222222',
            'phone'               => '9222222222',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_MEMBER2',
            'payment_order_id'    => 'order_MEMBER2',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        // Session phone belongs to member2, but digilocker_member_id in session belongs to member1
        $response = $this->withSession([
            'verified_membership_phone'  => '9222222222',
            'digilocker_verification_id' => 'ABVHPS_DIGILOCKER_TEST',
            'digilocker_member_id'       => $member1->id,
            'digilocker_aadhaar_encrypted' => Crypt::encryptString('234567890123'),
            'digilocker_started_at'      => time(),
        ])->get('/membership/aadhaar/callback');

        $response->assertRedirect('/membership/application');
        $response->assertSessionHas('error');

        $member1->refresh();
        $member2->refresh();
        $this->assertFalse($member1->is_aadhaar_verified);
        $this->assertFalse($member2->is_aadhaar_verified);
    }

    /**
     * Test 11: expired server-side DigiLocker session cannot verify.
     */
    public function test_expired_digilocker_session_cannot_verify(): void
    {
        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST11',
            'payment_order_id'    => 'order_TEST11',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        // Started 16 minutes ago (960 seconds > 900 seconds limit)
        $expiredTime = time() - 960;

        $response = $this->withSession([
            'verified_membership_phone'  => '9876543210',
            'digilocker_verification_id' => 'ABVHPS_DIGILOCKER_EXPIRED',
            'digilocker_member_id'       => $member->id,
            'digilocker_aadhaar_encrypted' => Crypt::encryptString('234567890123'),
            'digilocker_started_at'      => $expiredTime,
        ])->get('/membership/aadhaar/callback');

        $response->assertRedirect('/membership/application');
        $response->assertSessionHas('error');

        $member->refresh();
        $this->assertFalse($member->is_aadhaar_verified);
    }

    /**
     * Test 12: callback query verification_id/reference_id cannot override server session values.
     */
    public function test_callback_query_params_cannot_override_server_session(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/document/AADHAAR*' => Http::response([
                'status' => 'SUCCESS',
                'name'   => 'AUTHENTICATED USER',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker*' => Http::response([
                'status' => 'AUTHENTICATED',
            ], 200),
        ]);

        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST12',
            'payment_order_id'    => 'order_TEST12',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        // Query param has verification_id=MALICIOUS_OVERRIDE
        $this->withSession([
            'verified_membership_phone'    => '9876543210',
            'digilocker_verification_id'   => 'SERVER_SESSION_ID_123',
            'digilocker_member_id'         => $member->id,
            'digilocker_reference_id'       => 'SERVER_REF_456',
            'digilocker_aadhaar_encrypted' => Crypt::encryptString('234567890123'),
            'digilocker_started_at'        => time(),
        ])->get('/membership/aadhaar/callback?verification_id=MALICIOUS_OVERRIDE&reference_id=MALICIOUS_REF');

        // Verify Http request used SERVER_SESSION_ID_123, not MALICIOUS_OVERRIDE
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'verification_id=SERVER_SESSION_ID_123');
        });
    }

    /**
     * Test 13: callback alone cannot mark verified.
     */
    public function test_callback_alone_cannot_mark_verified(): void
    {
        $response = $this->get('/membership/aadhaar/callback?verification_id=FAKED_ID');

        $response->assertRedirect('/membership');
    }

    /**
     * Test 14: AUTHENTICATED + document SUCCESS can mark verified.
     */
    public function test_authenticated_plus_document_success_marks_verified(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/document/AADHAAR*' => Http::response([
                'status'        => 'SUCCESS',
                'name'          => 'SUBBA REDDY',
                'dob'           => '1988-11-22',
                'gender'        => 'M',
                'split_address' => [
                    'pincode'  => '516193',
                    'district' => 'YSR Kadapa',
                    'state'    => 'Andhra Pradesh',
                ],
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker*' => Http::response([
                'status' => 'AUTHENTICATED',
            ], 200),
        ]);

        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST14',
            'payment_order_id'    => 'order_TEST14',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $this->withSession([
            'verified_membership_phone'    => '9876543210',
            'digilocker_verification_id'   => 'SERVER_VERIF_888',
            'digilocker_member_id'         => $member->id,
            'digilocker_aadhaar_encrypted' => Crypt::encryptString('234567890123'),
            'digilocker_started_at'        => time(),
        ])->get('/membership/aadhaar/callback');

        $member->refresh();
        $this->assertTrue($member->is_aadhaar_verified);
        $this->assertEquals('SUBBA REDDY', $member->full_name);
        $this->assertEquals('234567890123', $member->aadhaar_number);
    }

    /**
     * Test 15: legacy /membership/verify-aadhaar cannot invoke old offline Aadhaar verification.
     */
    public function test_legacy_verify_aadhaar_route_invokes_start_flow_not_offline_verify(): void
    {
        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => Http::response([
                'status' => 'ACCOUNT_NOT_FOUND',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => Http::response([
                'status' => 'PENDING',
                'url'    => 'https://digilocker.cashfree.com/signup',
            ], 200),
        ]);

        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_LEGACY_VERIF',
            'payment_order_id'    => 'order_LEGACY_VERIF',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/verify-aadhaar', [
                'aadhaar_number' => '234567890123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/signup',
        ]);

        // Assert obsolete /offline-aadhaar/verify endpoint was NOT called
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/offline-aadhaar/verify');
        });
    }

    /**
     * Test 16: browser cannot overwrite verified full_name.
     */
    public function test_browser_cannot_overwrite_verified_full_name(): void
    {
        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'full_name'           => 'AUTHORITATIVE VERIFIED NAME',
            'is_aadhaar_verified' => true,
        ]);

        $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'aadhaar_number'         => '234567890123',
                'full_name'              => 'TAMPERED NAME FROM BROWSER',
                'gender'                 => 'Male',
                'dob'                    => '1990-01-01',
                'father_or_husband_name' => 'Father Name',
                'permanent_address'      => 'Address',
                'gotram'                 => 'Kashyapa',
                'occupation'             => 'Business',
                'blood_group'            => 'O+',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Porumamilla',
                'mandal'                 => 'Porumamilla',
                'district'               => 'YSR Kadapa',
                'state'                  => 'Andhra Pradesh',
                'photo'                  => \Illuminate\Http\UploadedFile::fake()->image('photo.jpg'),
            ]);

        $member->refresh();
        $this->assertEquals('AUTHORITATIVE VERIFIED NAME', $member->full_name);
    }

    /**
     * Test 17: browser cannot overwrite verified aadhaar_number.
     */
    public function test_browser_cannot_overwrite_verified_aadhaar_number(): void
    {
        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'aadhaar_number'      => '234567890123',
            'full_name'           => 'VERIFIED USER',
            'is_aadhaar_verified' => true,
        ]);

        $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'aadhaar_number'         => '999999999999', // Tampered Aadhaar number
                'full_name'              => 'VERIFIED USER',
                'gender'                 => 'Male',
                'dob'                    => '1990-01-01',
                'father_or_husband_name' => 'Father Name',
                'permanent_address'      => 'Address',
                'gotram'                 => 'Kashyapa',
                'occupation'             => 'Business',
                'blood_group'            => 'O+',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Porumamilla',
                'mandal'                 => 'Porumamilla',
                'district'               => 'YSR Kadapa',
                'state'                  => 'Andhra Pradesh',
                'photo'                  => \Illuminate\Http\UploadedFile::fake()->image('photo.jpg'),
            ]);

        $member->refresh();
        $this->assertEquals('234567890123', $member->aadhaar_number);
    }

    /**
     * Test 18: browser cannot overwrite non-empty verified DOB/gender/address identity fields.
     */
    public function test_browser_cannot_overwrite_verified_demographic_fields(): void
    {
        $member = Membership::create([
            'membership_id'          => '123456789012',
            'phone'                  => '9876543210',
            'payment_status'         => 'success',
            'full_name'              => 'VERIFIED USER',
            'aadhaar_number'         => '234567890123',
            'dob'                    => '1990-05-15',
            'gender'                 => 'Female',
            'permanent_address'      => 'Original Verified Address',
            'is_aadhaar_verified'    => true,
        ]);

        $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'aadhaar_number'         => '234567890123',
                'full_name'              => 'VERIFIED USER',
                'gender'                 => 'Male', // Tampered gender
                'dob'                    => '2000-01-01', // Tampered DOB
                'father_or_husband_name' => 'Father Name',
                'permanent_address'      => 'Fake Address', // Tampered Address
                'gotram'                 => 'Kashyapa',
                'occupation'             => 'Business',
                'blood_group'            => 'O+',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Porumamilla',
                'mandal'                 => 'Porumamilla',
                'district'               => 'YSR Kadapa',
                'state'                  => 'Andhra Pradesh',
                'photo'                  => \Illuminate\Http\UploadedFile::fake()->image('photo.jpg'),
            ]);

        $member->refresh();
        $this->assertEquals('1990-05-15', $member->dob);
        $this->assertEquals('Female', $member->gender);
        $this->assertEquals('Original Verified Address', $member->permanent_address);
    }

    /**
     * Test 19: no full Aadhaar is returned in status JSON.
     */
    public function test_no_full_aadhaar_returned_in_status_json(): void
    {
        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => '9876543210',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_STATUS_JSON',
            'payment_order_id'    => 'order_STATUS_JSON',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'full_name'           => 'VERIFIED USER',
            'aadhaar_number'      => '234567890123',
            'is_aadhaar_verified' => true,
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->getJson('/membership/aadhaar/status');

        $response->assertStatus(200);
        $response->assertJson([
            'is_verified'    => true,
            'masked_aadhaar' => 'XXXX-XXXX-0123',
        ]);
        $response->assertJsonMissing(['aadhaar_number' => '234567890123']);
        $this->assertStringNotContainsString('234567890123', $response->getContent());
    }

    /**
     * Test 20: status endpoint returns only the currently verified session member.
     */
    public function test_status_endpoint_returns_only_verified_session_member(): void
    {
        $member1 = Membership::create([
            'membership_id'       => '111111111111',
            'phone'               => '9111111111',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST20_1',
            'payment_order_id'    => 'order_TEST20_1',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'full_name'           => 'MEMBER ONE VERIFIED',
            'is_aadhaar_verified' => true,
        ]);

        $member2 = Membership::create([
            'membership_id'       => '222222222222',
            'phone'               => '9222222222',
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_TEST20_2',
            'payment_order_id'    => 'order_TEST20_2',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'full_name'           => 'MEMBER TWO UNVERIFIED',
            'is_aadhaar_verified' => false,
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9222222222'])
            ->getJson('/membership/aadhaar/status');

        $response->assertStatus(200);
        $response->assertJson([
            'is_verified'   => false,
            'verified_name' => null,
        ]);
        $response->assertJsonMissing(['verified_name' => 'MEMBER ONE VERIFIED']);
    }
}
