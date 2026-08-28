<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\SiteSetting;

class MobileApiHealthAndBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_success_and_no_debug_leaks(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 'ok',
            ]);

        // Ensure no environment or sensitive variables leaked
        $json = $response->json();
        $this->assertArrayNotHasKey('env', $json);
        $this->assertArrayNotHasKey('database', $json);
        $this->assertArrayNotHasKey('secrets', $json);
    }

    public function test_bootstrap_endpoint_returns_only_safe_allowlisted_settings(): void
    {
        SiteSetting::set('site_name', 'Akhanda Bharata Viswa Hindu Parirakshana Samiti');
        SiteSetting::set('contact_email', 'info@abvhps.org');
        SiteSetting::set('contact_phone', '+91 9989980055');
        SiteSetting::set('whatsapp_number', '+91 9989980055');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');

        $response = $this->getJson('/api/v1/bootstrap');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'organization' => [
                        'name',
                        'short_name',
                        'tagline',
                        'contact_email',
                        'contact_phone',
                        'whatsapp',
                    ],
                    'social_links',
                    'app_config' => [
                        'min_supported_version',
                        'latest_version',
                        'features',
                    ],
                ],
            ]);

        $json = $response->json();
        $this->assertTrue($json['success']);
        $this->assertEquals('Akhanda Bharata Viswa Hindu Parirakshana Samiti', $json['data']['organization']['name']);
        $this->assertEquals('ABVHPS', $json['data']['organization']['short_name']);
        $this->assertEquals('info@abvhps.org', $json['data']['organization']['contact_email']);
        $this->assertNotEmpty($json['data']['social_links']);

        // Assert no sensitive settings leaked
        $this->assertArrayNotHasKey('payment_gateways', $json['data']);
        $this->assertArrayNotHasKey('smtp_password', $json['data']);
    }
}
