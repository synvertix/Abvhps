<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Volunteer;
use App\Models\Membership;

class VolunteerMemberSearchTest extends TestCase
{
    use RefreshDatabase;

    protected Volunteer $volunteer;
    protected Membership $completedMember;
    protected Membership $incompletePaidMember;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Fully completed & paid active member
        $this->completedMember = Membership::create([
            'membership_id'                  => '888899990000',
            'phone'                          => '9123456780',
            'full_name'                      => 'RAMA KRISHNA RAJU',
            'email'                          => 'ramakrishna@example.com',
            'aadhaar_number'                 => '987654321098',
            'dob'                            => '1985-05-15',
            'gender'                         => 'Male',
            'father_or_husband_name'         => 'VENKATA RAJU',
            'permanent_address'              => 'Secret Private Address Plot 101',
            'present_address'                => 'Secret Private Address Plot 101',
            'pincode'                        => '516193',
            'district'                       => 'YSR Kadapa',
            'state'                          => 'Andhra Pradesh',
            'payment_status'                 => 'success',
            'payment_id'                     => 'PAY_SECRET_9999',
            'payment_gateway'                => 'razorpay',
            'payment_amount'                 => 100.00,
            'is_completed'                   => 1,
            'identity_verified'              => true,
            'identity_verified_name'         => 'RAMA KRISHNA RAJU',
            'identity_verification_method'   => 'pan',
            'identity_document_last4'        => '4321',
        ]);

        // 2. Paid but incomplete member (payment success but form details not submitted)
        $this->incompletePaidMember = Membership::create([
            'membership_id'  => '777788889999',
            'phone'          => '9123456781',
            'full_name'      => 'INCOMPLETE DEVOTEE',
            'payment_status' => 'success',
            'payment_id'     => 'PAY_INCOMPLETE_123',
            'payment_amount' => 100.00,
            'is_completed'   => 0, // NOT completed
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
        ]);

        // 3. Approved active volunteer
        $this->volunteer = Volunteer::create([
            'membership_id'             => '888899990000',
            'phone'                     => '9123456780',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'VOT1234567',
            'email'                     => 'vol@abvhps.org',
            'password'                  => Hash::make('password123'),
            'must_change_password'      => false,
            'status'                    => 'approved',
            'is_active'                 => true,
            'volunteer_id'              => '654321',
            'volunteer_login_id'        => '654321',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'RAMA KRISHNA RAJU',
            'account_number'            => '1122334455',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Badvel',
            'nominee_name'              => 'Lakshmi',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543219',
            'document_declaration_path' => 'v_decl.pdf',
            'document_voter_path'       => 'v_voter.pdf',
            'document_bank_path'        => 'v_bank.pdf',
        ]);
    }

    public function test_exact_12_digit_completed_member_returns_minimal_privacy_safe_info()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '888899990000',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'member'  => [
                'membership_id' => '888899990000',
                'full_name'     => 'RAMA KRISHNA RAJU',
                'status'        => 'Active',
                'district'      => 'YSR Kadapa',
                'state'         => 'Andhra Pradesh',
            ],
        ]);

        // STRICT PRIVACY AUDIT: Ensure sensitive fields are NEVER present in response
        $json = $response->json();
        $this->assertArrayNotHasKey('phone', $json['member']);
        $this->assertArrayNotHasKey('email', $json['member']);
        $this->assertArrayNotHasKey('aadhaar_number', $json['member']);
        $this->assertArrayNotHasKey('dob', $json['member']);
        $this->assertArrayNotHasKey('father_or_husband_name', $json['member']);
        $this->assertArrayNotHasKey('permanent_address', $json['member']);
        $this->assertArrayNotHasKey('present_address', $json['member']);
        $this->assertArrayNotHasKey('payment_id', $json['member']);
        $this->assertArrayNotHasKey('payment_gateway', $json['member']);
        $this->assertArrayNotHasKey('identity_document_last4', $json['member']);
    }

    public function test_paid_but_incomplete_member_is_not_searchable()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '777788889999',
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Member not found.',
        ]);
    }

    public function test_11_digits_membership_id_is_rejected_by_validation()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '12345678901', // 11 digits
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['membership_id']);
    }

    public function test_13_digits_membership_id_is_rejected_by_validation()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '1234567890123', // 13 digits
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['membership_id']);
    }

    public function test_alphabetic_membership_id_is_rejected_by_validation()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => 'ABCDEFGHIJKL',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['membership_id']);
    }

    public function test_alphanumeric_mixed_membership_id_is_rejected_by_validation()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '12345678901A',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['membership_id']);
    }

    public function test_spaces_inside_membership_id_is_rejected_by_validation()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '8888 9999 000',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['membership_id']);
    }

    public function test_unknown_valid_12_digit_id_returns_not_found()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '999999999999',
            ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Member not found.',
        ]);
    }

    public function test_search_rate_limiting_enforced_at_20_per_minute()
    {
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($this->volunteer, 'volunteer')
                ->postJson('/volunteer/member-search/lookup', [
                    'membership_id' => '888899990000',
                ]);
            $response->assertStatus(200);
        }

        // 21st attempt in the same minute should receive 429
        $throttledResponse = $this->actingAs($this->volunteer, 'volunteer')
            ->postJson('/volunteer/member-search/lookup', [
                'membership_id' => '888899990000',
            ]);

        $throttledResponse->assertStatus(429);
        $this->assertFalse($throttledResponse->json('success'));
    }
}
