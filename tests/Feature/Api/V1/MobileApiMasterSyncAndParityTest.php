<?php

namespace Tests\Feature\Api\V1;

use App\Models\Membership;
use App\Models\Volunteer;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MobileApiMasterSyncAndParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_state_endpoint_returns_signatures()
    {
        $response = $this->getJson('/api/v1/sync-state');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'timestamp',
                'signatures' => [
                    'site_settings',
                    'banners',
                    'blogs',
                    'gallery',
                    'support_cores',
                    'campaigns',
                    'volunteers',
                    'memberships',
                    'exams',
                    'certificates',
                ],
            ]);
    }

    public function test_donation_initiation_and_status_check()
    {
        $initResponse = $this->postJson('/api/v1/donations/initiate', [
            'name'            => 'Devotee Donor',
            'contact'         => '9876543210',
            'email'           => 'donor@example.com',
            'amount'          => 1000,
            'payment_gateway' => 'razorpay',
            'cause'           => 'Annadanam Fund',
        ]);

        $initResponse->assertStatus(201)
            ->assertJsonPath('success', true);

        $donationId = $initResponse->json('donation_id');

        $statusResponse = $this->getJson("/api/v1/donations/{$donationId}/status");

        $statusResponse->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.payment_status', 'PENDING');
    }

    public function test_membership_application_api_parity()
    {
        $response = $this->postJson('/api/v1/membership/apply', [
            'phone'                  => '9988776655',
            'full_name'              => 'SANATHANA MEMBER',
            'email'                  => 'member@example.com',
            'dob'                    => '1995-05-15',
            'gender'                 => 'male',
            'father_or_husband_name' => 'FATHER NAME',
            'address'                => 'MAIN MANDAL ROAD',
            'district'               => 'HYDERABAD',
            'mandal'                 => 'CHARMINAR',
            'grama_panchayat'        => 'GP 1',
            'pincode'                => '500002',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('memberships', ['phone' => '9988776655']);
    }

    public function test_volunteer_application_api_parity()
    {
        $member = Membership::create([
            'membership_id'          => '998877665544',
            'phone'                  => '9988776655',
            'full_name'              => 'VOLUNTEER APPLICANT',
            'dob'                    => '1992-04-10',
            'gender'                 => 'male',
            'father_or_husband_name' => 'FATHER NAME',
            'address'                => 'MANDAL HQ',
            'district'               => 'HYDERABAD',
            'mandal'                 => 'CHARMINAR',
            'grama_panchayat'        => 'GP 1',
            'pincode'                => '500002',
            'status'                 => 'approved',
            'payment_status'         => 'PAID',
        ]);

        $response = $this->postJson('/api/v1/volunteer/apply', [
            'membership_id'   => '998877665544',
            'full_name'       => 'VOLUNTEER APPLICANT',
            'phone'           => '9988776655',
            'email'           => 'volunteer@example.com',
            'dob'             => '1992-04-10',
            'district'        => 'HYDERABAD',
            'mandal'          => 'CHARMINAR',
            'grama_panchayat' => 'GP 1',
            'preferred_wing'  => 'Rudra Sena',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('volunteers', ['membership_id' => '998877665544']);
    }
}
