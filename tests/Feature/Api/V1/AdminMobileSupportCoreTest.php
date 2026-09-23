<?php

namespace Tests\Feature\Api\V1;

use App\Models\OurSupport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileSupportCoreTest extends TestCase
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

    public function test_admin_can_manage_support_cores()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $response = $this->postJson('/api/v1/admin/support-cores', [
            'name'       => 'GRAMA SEVA DAL',
            'sort_order' => 1,
            'short_info' => 'Youth empowerment and rural seva.',
            'status'     => 'show',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('our_supports', ['name' => 'GRAMA SEVA DAL']);
    }
}
