<?php

namespace Tests\Feature\Api\V1;

use App\Models\OurTeam;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileTeamTest extends TestCase
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

    public function test_guest_cannot_access_team_api()
    {
        $response = $this->getJson('/api/v1/admin/team');
        $response->assertStatus(401);
    }

    public function test_admin_can_fetch_team_list()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        OurTeam::create([
            'name'        => 'TEST LEADER',
            'cadre_level' => 'state_level',
            'designation' => 'PRESIDENT',
            'locality'    => 'TELANGANA',
        ]);

        $response = $this->getJson('/api/v1/admin/team');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_create_team_member()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $response = $this->postJson('/api/v1/admin/team', [
            'name'        => 'CHIEF COMMANDER',
            'cadre_level' => 'national_level',
            'designation' => 'NATIONAL PRESIDENT',
            'locality'    => 'NEW DELHI',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('our_teams', [
            'name' => 'CHIEF COMMANDER',
        ]);
    }
}
