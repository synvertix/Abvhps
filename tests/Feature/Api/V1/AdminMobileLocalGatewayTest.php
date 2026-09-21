<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class AdminMobileLocalGatewayTest extends TestCase
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

    public function test_admin_can_fetch_local_gateways_roster(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        DB::table('kala_brundams')->insert([
            'team_registration_id' => 'KB1001',
            'team_name'            => 'RUDRA CULTURAL TROUPE',
            'team_type'            => 'Folk Arts',
            'location'             => 'Central GP',
            'status'               => 'pending',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/local-gateways');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.total_groups', 1)
            ->assertJsonPath('data.0.name', 'RUDRA CULTURAL TROUPE');
    }

    public function test_admin_can_approve_local_gateway_group(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        $id = DB::table('kala_brundams')->insertGetId([
            'team_registration_id' => 'KB1002',
            'team_name'            => 'SANGHAM CULTURAL TEAM',
            'team_type'            => 'Folk Arts',
            'location'             => 'Central GP',
            'status'               => 'pending',
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        $response = $this->postJson("/api/v1/admin/local-gateways/approve/kala_brundam/{$id}");
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('kala_brundams', [
            'id'     => $id,
            'status' => 'approved',
        ]);
    }
}
