<?php

namespace Tests\Feature\Api\V1;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileDonationTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'name'     => 'Commander Admin',
            'email'    => 'admin@abvhps.org',
            'password' => \Illuminate\Support\Facades\Hash::make('AdminSecret123!'),
        ]);
    }

    public function test_admin_can_fetch_donations_ledger()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        Donation::create([
            'name'             => 'DEVOTEE DONOR',
            'contact'          => '9988776655',
            'email'            => 'donor@example.com',
            'amount'           => 1008,
            'payment_gateway'  => 'razorpay',
            'gateway_order_id' => 'order_123',
            'payment_status'   => 'PAID',
        ]);

        $response = $this->getJson('/api/v1/admin/donations');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.paid_count', 1);
    }
}
