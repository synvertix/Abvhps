<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Membership;
use App\Services\RazorpayPaymentService;

class MembershipIdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.cashfree.verify_client_id', 'CF_TEST_VERIFY_CLIENT_ID_12345');
        Config::set('services.cashfree.verify_client_secret', 'cfsk_test_verify_secret_key_67890');
        Config::set('services.cashfree.verification_base_url', 'https://sandbox.cashfree.com/verification');
        Storage::fake('public');
    }

    private function createPaidMember(string $phone = '9876543210'): Membership
    {
        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_test_' . $phone,
            'payment_order_id'    => 'order_test_' . $phone,
            'payment_amount'      => RazorpayPaymentService::MEMBERSHIP_AMOUNT_RUPEES,
            'payment_verified_at' => now(),
        ]);

        return $member;
    }

    /**
     * Test: Unpaid user cannot access GET /membership/identity
     */
    public function test_unpaid_user_cannot_access_identity_page(): void
    {
        // 1. Without session
        $response = $this->get('/membership/identity');
        $response->assertRedirect('/membership');

        // 2. With session but unpaid member
        Membership::create([
            'membership_id'  => '123456789012',
            'phone'          => '9876543210',
            'payment_status' => 'pending',
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->get('/membership/identity');

        $response->assertRedirect('/membership/payment');
    }

    /**
     * Test: Paid user can access GET /membership/identity
     */
    public function test_paid_user_can_access_identity_page(): void
    {
        $this->createPaidMember('9876543210');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->get('/membership/identity');

        $response->assertStatus(200);
        $response->assertSee('Verify Official Identity Document');
    }

    /**
     * Test: Already verified user GET /membership/identity redirects to /membership/application
     */
    public function test_already_verified_user_is_redirected_to_application(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified           = true;
        $member->identity_verification_method = 'pan';
        $member->identity_verified_name      = 'SRI RAMA BHAKTA';
        $member->identity_verified_at        = now();
        $member->save();

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->get('/membership/identity');

        $response->assertRedirect('/membership/application');
    }

    /**
     * Test: Unpaid user cannot POST to identity verification endpoints
     */
    public function test_unpaid_user_cannot_post_identity_verification(): void
    {
        Membership::create([
            'membership_id'  => '123456789012',
            'phone'          => '9876543210',
            'payment_status' => 'pending',
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'ABCDE1234F',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test: PAN input pre-validation rejects invalid format
     */
    public function test_pan_pre_validation_rejects_invalid_format(): void
    {
        $this->createPaidMember('9876543210');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'INVALID_PAN',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['pan_number']);
    }

    /**
     * Test: Successful PAN verification (asserts Cashfree POST /pan payload and headers)
     */
    public function test_pan_verification_success(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => function ($request) {
                // Assert request headers
                $this->assertEquals('CF_TEST_VERIFY_CLIENT_ID_12345', $request->header('x-client-id')[0]);
                $this->assertEquals('cfsk_test_verify_secret_key_67890', $request->header('x-client-secret')[0]);

                // Assert payload contains only documented PAN field (no local UUID as verification_id)
                $data = $request->data();
                $this->assertEquals('ABCDE1234F', $data['pan']);
                $this->assertArrayNotHasKey('verification_id', $data);

                return Http::response([
                    'valid'           => true,
                    'pan'             => 'ABCDE1234F',
                    'registered_name' => 'SRI RAMA BHAKTA',
                    'reference_id'    => 'CF_PAN_REF_998877',
                    'status'          => 'VALID',
                ], 200);
            },
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'abcde1234f', // lowercase test
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'success',
            'verified_name' => 'SRI RAMA BHAKTA',
        ]);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertTrue($member->identity_verified);
        $this->assertEquals('pan', $member->identity_verification_method);
        $this->assertEquals('cashfree', $member->identity_verification_provider);
        $this->assertEquals('SRI RAMA BHAKTA', $member->identity_verified_name);
        $this->assertEquals('CF_PAN_REF_998877', $member->identity_verification_reference_id);
        $this->assertEquals('234F', $member->identity_document_last4);
        $this->assertNotNull($member->identity_verified_at);
        $this->assertTrue($member->hasVerifiedIdentity());
    }

    /**
     * Test: Successful Voter ID verification
     */
    public function test_voter_id_verification_success(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/voter-id' => function ($request) {
                $this->assertEquals('CF_TEST_VERIFY_CLIENT_ID_12345', $request->header('x-client-id')[0]);
                $data = $request->data();
                $this->assertEquals('ABC1234567', $data['epic_number']);
                $this->assertStringStartsWith('ABV_', $data['verification_id']);

                return Http::response([
                    'status'          => 'VALID',
                    'name'            => 'KRISHNA REDDY',
                    'reference_id'    => 'CF_VOTER_REF_112233',
                    'verification_id' => $data['verification_id'],
                ], 200);
            },
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/voter-id/verify', [
                'voter_id' => 'abc1234567',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'success',
            'verified_name' => 'KRISHNA REDDY',
        ]);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertTrue($member->identity_verified);
        $this->assertEquals('voter_id', $member->identity_verification_method);
        $this->assertEquals('KRISHNA REDDY', $member->identity_verified_name);
        $this->assertEquals('4567', $member->identity_document_last4);
    }

    /**
     * Test: Successful Driving Licence verification
     */
    public function test_driving_licence_verification_success(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/driving-license' => function ($request) {
                $this->assertEquals('CF_TEST_VERIFY_CLIENT_ID_12345', $request->header('x-client-id')[0]);
                $data = $request->data();
                $this->assertEquals('DL-0120110012345', $data['dl_number']);
                $this->assertEquals('1990-05-15', $data['dob']);

                return Http::response([
                    'status'                      => 'VALID',
                    'details_of_driving_licence' => [
                        'name' => 'VENKATESHWARLU',
                    ],
                    'reference_id'                => 'CF_DL_REF_445566',
                ], 200);
            },
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/driving-license/verify', [
                'dl_number' => 'DL-0120110012345',
                'dob'       => '1990-05-15',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'success',
            'verified_name' => 'VENKATESHWARLU',
        ]);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertTrue($member->identity_verified);
        $this->assertEquals('driving_licence', $member->identity_verification_method);
        $this->assertEquals('VENKATESHWARLU', $member->identity_verified_name);
        $this->assertEquals('2345', $member->identity_document_last4);
    }

    /**
     * Test: Successful Passport verification
     */
    public function test_passport_verification_success(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/passport' => function ($request) {
                $this->assertEquals('CF_TEST_VERIFY_CLIENT_ID_12345', $request->header('x-client-id')[0]);
                $data = $request->data();
                $this->assertEquals('HY1234567890123', $data['file_number']);
                $this->assertEquals('1988-10-20', $data['dob']);

                return Http::response([
                    'status'       => 'VALID',
                    'name'         => 'NARASIMHA SWAMY',
                    'reference_id' => 'CF_PASS_REF_778899',
                ], 200);
            },
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/passport/verify', [
                'file_number' => 'HY1234567890123',
                'dob'         => '1988-10-20',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'success',
            'verified_name' => 'NARASIMHA SWAMY',
        ]);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertTrue($member->identity_verified);
        $this->assertEquals('passport', $member->identity_verification_method);
        $this->assertEquals('NARASIMHA SWAMY', $member->identity_verified_name);
        $this->assertEquals('0123', $member->identity_document_last4);
    }

    /**
     * Test: Provider VALID but empty verified name fails closed
     */
    public function test_valid_response_with_empty_name_fails_closed(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => Http::response([
                'valid'           => true,
                'pan'             => 'ABCDE1234F',
                'registered_name' => '   ', // whitespace only
                'status'          => 'VALID',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'ABCDE1234F',
            ]);

        $response->assertStatus(422);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertFalse((bool) $member->identity_verified);
        $this->assertFalse($member->hasVerifiedIdentity());
    }

    /**
     * Test: Cashfree 403 / disabled product fails closed with friendly unavailable message
     */
    public function test_cashfree_403_returns_method_unavailable_message(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/passport' => Http::response([
                'status'  => 'FORBIDDEN',
                'message' => 'Product not enabled on merchant account',
            ], 403),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/passport/verify', [
                'file_number' => 'HY1234567890123',
                'dob'         => '1988-10-20',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'This verification method is currently unavailable. Please choose another verification method.',
        ]);

        $member = Membership::where('phone', '9876543210')->first();
        $this->assertFalse((bool) $member->identity_verified);
    }

    /**
     * Test: First successful identity cannot be overwritten by subsequent verification attempts
     */
    public function test_first_successful_identity_cannot_be_overwritten(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'pan';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verification_reference_id = 'ORIGINAL_PAN_REF';
        $member->identity_verified_name             = 'ORIGINAL PAN NAME';
        $member->identity_document_last4            = '1234';
        $member->identity_verified_at              = now();
        $member->save();

        // Attempting to verify Voter ID after PAN was already verified
        Http::fake([
            'https://sandbox.cashfree.com/verification/voter-id' => Http::response([
                'status' => 'VALID',
                'name'   => 'NEW VOTER NAME',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/voter-id/verify', [
                'voter_id' => 'ABC1234567',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'already_verified',
            'verified_name' => 'ORIGINAL PAN NAME',
        ]);

        // Provider was NOT called because endpoint short-circuits
        Http::assertNothingSent();

        $member->refresh();
        $this->assertEquals('pan', $member->identity_verification_method);
        $this->assertEquals('ORIGINAL PAN NAME', $member->identity_verified_name);
        $this->assertEquals('ORIGINAL_PAN_REF', $member->identity_verification_reference_id);
    }

    /**
     * Test: Membership application submission succeeds with PAN verification (no aadhaar_number required)
     */
    public function test_application_submission_succeeds_with_pan_and_uses_provider_verified_name(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'pan';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verified_name             = 'AUTHORIZED PAN HOLDER';
        $member->identity_verified_at              = now();
        $member->save();

        $photo = UploadedFile::fake()->image('photo.jpg');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'full_name'              => 'BROWSER FORGED NAME', // must be ignored
                'gender'                 => 'Male',
                'dob'                    => '1995-01-01',
                'father_or_husband_name' => 'Father Name',
                'permanent_address'      => 'House No 123, Akkalareddy Palli',
                'gotram'                 => 'Kashyapa',
                'occupation'             => 'Software Engineer',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Akkalareddy Palli',
                'mandal'                 => 'Porumamilla',
                'district'               => 'Kadapa',
                'state'                  => 'Andhra Pradesh',
                'email'                  => 'devotee@example.com',
                'photo'                  => $photo,
            ]);

        $response->assertRedirect('/membership/view-card');

        $member->refresh();
        $this->assertTrue((bool) $member->is_completed);
        $this->assertEquals('AUTHORIZED PAN HOLDER', $member->full_name); // Provider name stored, not browser forged
    }

    /**
     * Test: Migration backfill provenance safety
     */
    public function test_migration_backfill_and_provenance_rules(): void
    {
        // 1. Unverified row
        $unverified = Membership::create([
            'phone'     => '9999900001',
            'full_name' => 'Unverified Name',
        ]);
        $unverified->aadhaar_number = '123456789012';
        $unverified->is_aadhaar_verified = false;
        $unverified->save();

        $this->assertFalse((bool) $unverified->identity_verified);
        $this->assertFalse($unverified->hasVerifiedIdentity());

        // 2. Generic verified row requires all 4 fields to satisfy hasVerifiedIdentity
        $generic = Membership::create([
            'phone' => '9999900002',
        ]);
        $generic->identity_verified = true;
        // Missing method, name, verified_at -> must return false
        $this->assertFalse($generic->hasVerifiedIdentity());

        $generic->identity_verification_method = 'pan';
        $generic->identity_verified_name       = 'Valid Name';
        $generic->identity_verified_at         = now();
        $this->assertTrue($generic->hasVerifiedIdentity());

        // 3. Legacy Aadhaar verified row with non-empty full_name satisfies hasVerifiedIdentity
        $legacy = Membership::create([
            'phone'     => '9999900003',
            'full_name' => 'Legacy Name',
        ]);
        $legacy->is_aadhaar_verified = true;
        $legacy->save();

        $this->assertTrue($legacy->hasVerifiedIdentity());

        // 4. Legacy row without full_name fails closed
        $legacyNoName = Membership::create([
            'phone'     => '9999900004',
            'full_name' => null,
        ]);
        $legacyNoName->is_aadhaar_verified = true;
        $legacyNoName->save();

        $this->assertFalse($legacyNoName->hasVerifiedIdentity());
    }

    /**
     * Regression Test: Raw Cashfree body message is never returned to browser
     */
    public function test_raw_cashfree_body_message_is_never_returned_to_browser(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => Http::response([
                'status'  => 'INVALID',
                'message' => 'INTERNAL_DB_STACK_TRACE_LEAK_SECRET_42',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'ABCDE1234F',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'PAN verification failed. Please check the PAN number and try again.',
        ]);
        $response->assertDontSee('INTERNAL_DB_STACK_TRACE_LEAK_SECRET_42');
    }

    /**
     * Regression Test: Provider exception does not expose exception message
     */
    public function test_provider_exception_does_not_expose_exception_message(): void
    {
        $this->createPaidMember('9876543210');

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => function () {
                throw new \Exception('Failed to connect to internal proxy 10.0.0.137 with secret header Authorization: Bearer XYZ');
            },
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'ABCDE1234F',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status'  => 'error',
            'message' => 'Unable to communicate with the verification service. Please try again later.',
        ]);
        $response->assertDontSee('10.0.0.137');
        $response->assertDontSee('Bearer XYZ');
    }

    /**
     * Regression Test: PAN first success cannot be overwritten by delayed DigiLocker Aadhaar callback
     */
    public function test_pan_first_success_cannot_be_overwritten_by_delayed_aadhaar_callback(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'pan';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verification_reference_id = 'ORIGINAL_PAN_REF_99';
        $member->identity_verified_name             = 'PERMANENT PAN NAME';
        $member->identity_document_last4            = '1234';
        $member->identity_verified_at              = now();
        $member->save();

        Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/document/AADHAAR*' => Http::response([
                'status'         => 'SUCCESS',
                'reference_id'   => 'LATE_AADHAAR_REF',
                'name'           => 'LATE AADHAAR PERSON',
                'aadhaar_number' => '234567890123',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker*' => Http::response([
                'status'       => 'AUTHENTICATED',
                'reference_id' => 'LATE_AADHAAR_REF',
            ], 200),
        ]);

        $sessionData = [
            'verified_membership_phone'    => '9876543210',
            'digilocker_verification_id'   => 'ABV_UUID_123',
            'digilocker_member_id'         => $member->id,
            'digilocker_reference_id'      => 'LATE_AADHAAR_REF',
            'digilocker_aadhaar_encrypted' => Crypt::encryptString('234567890123'),
            'digilocker_started_at'        => time(),
        ];

        $response = $this->withSession($sessionData)
            ->get('/membership/aadhaar/callback?verification_id=ABV_UUID_123&status=AUTHENTICATED');

        $response->assertRedirect('/membership/application');
        $response->assertSessionHas('warning', 'Identity was already verified with another document.');

        $member->refresh();
        $this->assertEquals('pan', $member->identity_verification_method);
        $this->assertEquals('PERMANENT PAN NAME', $member->identity_verified_name);
        $this->assertEquals('ORIGINAL_PAN_REF_99', $member->identity_verification_reference_id);
    }

    /**
     * Regression Test: Aadhaar first success cannot be overwritten by PAN
     */
    public function test_aadhaar_first_success_cannot_be_overwritten_by_pan(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'aadhaar';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verification_reference_id = 'ORIGINAL_AADHAAR_REF_88';
        $member->identity_verified_name             = 'PERMANENT AADHAAR NAME';
        $member->identity_document_last4            = '5678';
        $member->identity_verified_at              = now();
        $member->is_aadhaar_verified                = true;
        $member->full_name                          = 'PERMANENT AADHAAR NAME';
        $member->save();

        Http::fake([
            'https://sandbox.cashfree.com/verification/pan' => Http::response([
                'valid'           => true,
                'registered_name' => 'LATE PAN NAME',
                'reference_id'    => 'LATE_PAN_REF',
                'status'          => 'VALID',
            ], 200),
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->postJson('/membership/identity/pan/verify', [
                'pan_number' => 'ABCDE1234F',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'        => 'already_verified',
            'verified_name' => 'PERMANENT AADHAAR NAME',
        ]);

        $member->refresh();
        $this->assertEquals('aadhaar', $member->identity_verification_method);
        $this->assertEquals('PERMANENT AADHAAR NAME', $member->identity_verified_name);
    }

    /**
     * Regression Test: PAN verified member cannot persist browser-submitted Aadhaar
     */
    public function test_pan_verified_member_cannot_persist_browser_submitted_aadhaar(): void
    {
        $member = $this->createPaidMember('9876543210');
        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'pan';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verified_name             = 'VERIFIED PAN HOLDER';
        $member->identity_document_last4            = '1234';
        $member->identity_verified_at              = now();
        $member->aadhaar_number                     = null;
        $member->is_aadhaar_verified                = false;
        $member->save();

        $photo = UploadedFile::fake()->image('photo.jpg');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'aadhaar_number'         => '234567890123', // Browser forged Aadhaar input
                'full_name'              => 'VERIFIED PAN HOLDER',
                'gender'                 => 'Male',
                'dob'                    => '1995-01-01',
                'father_or_husband_name' => 'Father Name',
                'permanent_address'      => 'House No 123, Akkalareddy Palli',
                'gotram'                 => 'Kashyapa',
                'occupation'             => 'Engineer',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Akkalareddy Palli',
                'mandal'                 => 'Porumamilla',
                'district'               => 'Kadapa',
                'state'                  => 'Andhra Pradesh',
                'email'                  => 'devotee@example.com',
                'photo'                  => $photo,
            ]);

        $response->assertRedirect('/membership/view-card');

        $member->refresh();
        $this->assertTrue((bool) $member->is_completed);
        $this->assertNull($member->aadhaar_number); // MUST remain null
        $this->assertFalse((bool) $member->is_aadhaar_verified); // MUST remain false
        $this->assertEquals('pan', $member->identity_verification_method);
        $this->assertEquals('VERIFIED PAN HOLDER', $member->full_name);
    }
}
