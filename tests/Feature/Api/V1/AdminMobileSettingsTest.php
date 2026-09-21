<?php

namespace Tests\Feature\Api\V1;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileSettingsTest extends TestCase
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

    public function test_admin_can_fetch_and_update_site_settings()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $response = $this->postJson('/api/v1/admin/settings', [
            'contact_phone' => '+91 9988776655',
            'contact_email' => 'admin@abvhps.org',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals('+91 9988776655', SiteSetting::get('contact_phone'));
    }
}
