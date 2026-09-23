<?php

namespace Tests\Feature\Api\V1;

use App\Models\FundraisingCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileFundraisingTest extends TestCase
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

    public function test_admin_can_manage_campaigns()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $response = $this->postJson('/api/v1/admin/fundraising', [
            'title'         => 'TEMPLE ANNADANAM CAMPAIGN',
            'description'   => 'Providing daily annadanam to devotees.',
            'target_amount' => 500000,
            'status'        => 'active',
            'end_date'      => '2026-12-31',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('fundraising_campaigns', ['title' => 'TEMPLE ANNADANAM CAMPAIGN']);
    }
}
