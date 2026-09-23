<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'ADMIN TEST',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789')
        ]);
    }

    /**
     * 1. Default WhatsApp number exists and is +91 9989980055
     */
    public function test_default_whatsapp_number_exists_and_normalizes()
    {
        $this->assertEquals('+91 9989980055', SiteSetting::getWhatsAppNumber());
        $this->assertEquals('919989980055', SiteSetting::getNormalizedWhatsAppNumber());
        $this->assertEquals('https://wa.me/919989980055', SiteSetting::getWhatsAppUrl());
    }

    /**
     * 2. Floating WhatsApp button is rendered on homepage using default number
     */
    public function test_floating_whatsapp_button_is_rendered_on_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('https://wa.me/919989980055', false);
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    /**
     * 3. Admin panel sidebar contains WhatsApp support option with canonical URL
     */
    public function test_admin_panel_sidebar_contains_whatsapp_support_option()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('https://wa.me/919989980055', false);
        $response->assertSee('WHATSAPP', false);
        $response->assertDontSee('17. WHATSAPP');
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    /**
     * 4. Admin settings page renders WhatsApp number configuration field
     */
    public function test_admin_settings_page_contains_whatsapp_configuration_field()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('name="whatsapp_number"', false);
        $response->assertSee('WHATSAPP CONTACT', false);
        $response->assertSee('WhatsApp Number', false);
    }

    /**
     * 5. Admin can update the WhatsApp number and cache invalidation updates public link
     */
    public function test_admin_can_update_whatsapp_number_and_reflects_in_public_views()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_title' => 'ABVHPS Updated',
            'contact_phone' => '+91 9989980055',
            'whatsapp_number' => '+91 9123456789',
            'contact_email' => 'info@abvhps.org',
            'contact_address' => 'Sample address',
            'footer_about' => 'Sample about',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('+91 9123456789', SiteSetting::getWhatsAppNumber());
        $this->assertEquals('919123456789', SiteSetting::getNormalizedWhatsAppNumber());
        $this->assertEquals('https://wa.me/919123456789', SiteSetting::getWhatsAppUrl());

        // Verify public homepage now reflects the new WhatsApp link
        $homeResponse = $this->get('/');
        $homeResponse->assertSee('https://wa.me/919123456789', false);
    }

    /**
     * 6. Unauthorized guest cannot update WhatsApp number
     */
    public function test_unauthorized_guest_cannot_update_whatsapp_number()
    {
        $response = $this->post(route('admin.settings.update'), [
            'whatsapp_number' => '+91 9111122223',
        ]);

        // Expect redirect to login
        $response->assertRedirect(route('login'));
        $this->assertEquals('+91 9989980055', SiteSetting::getWhatsAppNumber());
    }

    /**
     * 7. Invalid WhatsApp numbers are rejected by validation
     */
    public function test_invalid_whatsapp_number_is_rejected()
    {
        // Test non-digit letters
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_title' => 'ABVHPS Test',
            'contact_phone' => '+91 9989980055',
            'whatsapp_number' => 'INVALID_NUMBER_ABC',
            'contact_email' => 'info@abvhps.org',
            'contact_address' => 'Sample address',
            'footer_about' => 'Sample about',
        ]);

        $response->assertSessionHasErrors(['whatsapp_number']);

        // Test too short
        $responseShort = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_title' => 'ABVHPS Test',
            'contact_phone' => '+91 9989980055',
            'whatsapp_number' => '12345',
            'contact_email' => 'info@abvhps.org',
            'contact_address' => 'Sample address',
            'footer_about' => 'Sample about',
        ]);

        $responseShort->assertSessionHasErrors(['whatsapp_number']);
    }

    /**
     * 8. Various phone formats normalize properly to canonical WhatsApp URL
     */
    public function test_whatsapp_url_normalization_with_various_formats()
    {
        // 10 digits
        SiteSetting::set('whatsapp_number', '9876543210');
        $this->assertEquals('https://wa.me/919876543210', SiteSetting::getWhatsAppUrl());

        // +91 with spaces and hyphens
        SiteSetting::set('whatsapp_number', '+91 98765-43210');
        $this->assertEquals('https://wa.me/919876543210', SiteSetting::getWhatsAppUrl());

        // Leading 0 format (09876543210)
        SiteSetting::set('whatsapp_number', '09876543210');
        $this->assertEquals('https://wa.me/919876543210', SiteSetting::getWhatsAppUrl());

        // Optional prefilled message
        $this->assertEquals('https://wa.me/919876543210?text=Hello+ABVHPS', SiteSetting::getWhatsAppUrl('Hello ABVHPS'));
    }
}
