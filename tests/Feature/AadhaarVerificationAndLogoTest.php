<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AadhaarVerificationAndLogoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_id', 'CF_TEST_ID');
        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_secret', 'CF_TEST_SECRET');
        \Illuminate\Support\Facades\Config::set('services.cashfree.verification_base_url', 'https://sandbox.cashfree.com/verification');
    }

    /**
     * Test: Membership verification page uses official logo and no lotus emoji (🪷).
     */
    public function test_membership_verification_page_uses_official_logo_and_no_lotus_emoji(): void
    {
        $response = $this->get('/membership');
        $response->assertStatus(200);
        $response->assertDontSee('🪷');
        $response->assertSee('images/ABVHPS_LOGO.jpg');
    }

    /**
     * Test: Membership ID card view does not contain lotus emoji (🪷).
     */
    public function test_membership_card_view_uses_official_logo_and_no_lotus_emoji(): void
    {
        $member = Membership::create([
            'membership_id' => '123456789012',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'full_name' => 'ANANYA SHARMA',
            'country' => 'India',
            'state' => 'Andhra Pradesh',
            'district' => 'Kadapa',
            'is_completed' => 1
        ]);

        $response = $this->withSession([
            'verified_membership_phone' => '9876543210',
            'last_email_log' => [
                'recipient_email' => 'ananya@example.com',
                'assigned_language' => 'en',
                'status' => 'queued'
            ]
        ])->get('/membership/view-card');

        $response->assertStatus(200);
        $response->assertDontSee('🪷');
        $response->assertSee('images/ABVHPS_LOGO.jpg');
    }

    /**
     * Test CASE 1: Verify applicant A -> Triggers DigiLocker start flow.
     */
    public function test_case_1_verify_applicant_a_returns_actual_verified_data(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => \Illuminate\Support\Facades\Http::response([
                'status' => 'ACCOUNT_EXISTS',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => \Illuminate\Support\Facades\Http::response([
                'status' => 'PENDING',
                'url'    => 'https://digilocker.cashfree.com/signin',
            ], 200),
        ]);

        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_id', 'CF_TEST_ID');
        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_secret', 'CF_TEST_SECRET');

        $applicantA = Membership::create([
            'membership_id'  => '111122223333',
            'phone'          => '9111111111',
            'payment_status' => 'success',
            'is_completed'   => 1
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9111111111'])
            ->postJson('/membership/verify-aadhaar', [
                'aadhaar_number' => '444455556666',
                'full_name'      => 'RAHUL SHARMA',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/signin',
        ]);
    }

    /**
     * Test CASE 2: Verify applicant B -> Triggers DigiLocker start flow.
     */
    public function test_case_2_verify_applicant_b_returns_actual_verified_data(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/verify-account' => \Illuminate\Support\Facades\Http::response([
                'status' => 'ACCOUNT_NOT_FOUND',
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker' => \Illuminate\Support\Facades\Http::response([
                'status' => 'PENDING',
                'url'    => 'https://digilocker.cashfree.com/signup',
            ], 200),
        ]);

        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_id', 'CF_TEST_ID');
        \Illuminate\Support\Facades\Config::set('services.cashfree.verify_client_secret', 'CF_TEST_SECRET');

        $applicantB = Membership::create([
            'membership_id'  => '999988887777',
            'phone'          => '9222222222',
            'payment_status' => 'success',
            'is_completed'   => 1
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9222222222'])
            ->postJson('/membership/verify-aadhaar', [
                'aadhaar_number' => '777788889999',
                'full_name'      => 'Priya Venkatesh',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'redirect',
            'redirect_url' => 'https://digilocker.cashfree.com/signup',
        ]);
    }

    /**
     * Test CASE 3: Failed Aadhaar verification -> No applicant's unrelated data appears.
     */
    public function test_case_3_failed_aadhaar_verification_returns_error(): void
    {
        // Invalid Aadhaar (e.g. less than 12 digits or starts with 0)
        $response = $this->withSession(['verified_membership_phone' => '9333333333'])
            ->postJson('/membership/verify-aadhaar', [
                'aadhaar_number' => '012345678901',
                'full_name'      => 'Test User',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'status' => 'error'
        ]);
        $response->assertJsonMissing(['data' => ['full_name' => 'SRINIVASA RAO']]);
    }

    /**
     * Test CASE 4: Fresh application without prior verification -> No default fake name appears.
     */
    public function test_case_4_fresh_application_form_does_not_contain_hardcoded_srinivasa_rao(): void
    {
        $newApplicant = Membership::create([
            'membership_id' => '555566667777',
            'phone' => '9444444444',
            'payment_status' => 'success',
            'is_completed' => 0
        ]);

        $response = $this->withSession(['verified_membership_phone' => '9444444444'])
            ->get('/membership/application');

        $response->assertStatus(200);
        $response->assertDontSee('SRINIVASA RAO');
        $response->assertDontSee('🪷');
    }

    /**
     * Test CASE 5: Data isolation across multiple applicants.
     */
    public function test_case_5_applicant_a_data_does_not_leak_to_applicant_b(): void
    {
        $applicantA = Membership::create([
            'membership_id' => '111100001111',
            'phone' => '9555555555',
            'payment_status' => 'success',
            'full_name' => 'APPLICANT A PERSON',
            'is_completed' => 1
        ]);

        $applicantB = Membership::create([
            'membership_id' => '222200002222',
            'phone' => '9666666666',
            'payment_status' => 'success',
            'full_name' => 'APPLICANT B PERSON',
            'is_completed' => 1
        ]);

        // Session of applicant B should only get applicant B's data
        $responseB = $this->withSession(['verified_membership_phone' => '9666666666'])
            ->get('/membership/application');

        $responseB->assertStatus(200);
        $responseB->assertSee('APPLICANT B PERSON');
        $responseB->assertDontSee('APPLICANT A PERSON');
        $responseB->assertDontSee('SRINIVASA RAO');
    }

    /**
     * Test CASE 6: Successful Aadhaar verification updates and persists data to database via DigiLocker callback.
     */
    public function test_case_6_successful_aadhaar_verification_persists_data_to_database(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://sandbox.cashfree.com/verification/digilocker/document/AADHAAR*' => \Illuminate\Support\Facades\Http::response([
                'status'        => 'SUCCESS',
                'name'          => 'KAVITHA REDDY',
                'dob'           => '1992-08-20',
                'gender'        => 'Female',
                'split_address' => [
                    'pincode' => '516193',
                ],
            ], 200),
            'https://sandbox.cashfree.com/verification/digilocker*' => \Illuminate\Support\Facades\Http::response([
                'status' => 'AUTHENTICATED',
            ], 200),
        ]);

        $member = Membership::create([
            'membership_id'  => '333344445555',
            'phone'          => '9777777777',
            'payment_status' => 'success',
            'is_completed'   => 0
        ]);

        $this->withSession([
            'verified_membership_phone'    => '9777777777',
            'digilocker_verification_id'   => 'SERVER_VERIF_999',
            'digilocker_member_id'         => $member->id,
            'digilocker_aadhaar_encrypted' => \Illuminate\Support\Facades\Crypt::encryptString('555566667777'),
            'digilocker_started_at'        => time(),
        ])->get('/membership/aadhaar/callback');

        $member->refresh();
        $this->assertTrue($member->is_aadhaar_verified);
        $this->assertEquals('KAVITHA REDDY', $member->full_name);
        $this->assertEquals('555566667777', $member->aadhaar_number);
    }

    /**
     * Test CASE 7: Missing phone session returns error and does not persist unauthenticated data.
     */
    public function test_case_7_missing_phone_session_returns_error(): void
    {
        $response = $this->postJson('/membership/verify-aadhaar', [
            'aadhaar_number' => '555566667777',
            'full_name'      => 'Test Person',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error'
        ]);
    }
}
