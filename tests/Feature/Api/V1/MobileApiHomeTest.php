<?php

namespace Tests\Feature\Api\V1;

use App\Models\Banner;
use App\Models\Donation;
use App\Models\FundraisingCampaign;
use App\Models\Membership;
use App\Models\SiteSetting;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MobileApiHomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_endpoint_returns_http_200_and_expected_safe_structure(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'banner',
                    'sliders',
                    'announcements',
                    'stats' => [
                        'donors',
                        'members',
                        'volunteers',
                        'years',
                    ],
                    'join_strip' => [
                        'enabled',
                        'why_heading',
                        'why_text',
                        'member_heading',
                        'member_text',
                        'cta_text',
                    ],
                    'campaigns',
                    'projects',
                    'sponsors_strip' => [
                        'enabled',
                        'heading',
                        'partners',
                    ],
                    'social_strip' => [
                        'enabled',
                        'heading',
                        'subtext',
                        'platforms',
                    ],
                    'contact' => [
                        'phone',
                        'email',
                        'address',
                        'whatsapp_number',
                        'whatsapp_url',
                    ],
                ],
                'message',
            ]);

        $this->assertTrue($response->json('success'));
    }

    public function test_home_endpoint_handles_empty_campaigns_and_empty_projects_safely(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data.campaigns'));
        $this->assertEmpty($response->json('data.campaigns'));
        $this->assertIsArray($response->json('data.projects'));
        $this->assertEmpty($response->json('data.projects'));
    }

    public function test_home_endpoint_returns_safe_live_statistics(): void
    {
        // Create 2 completed members
        Membership::create([
            'membership_id' => '123456789001',
            'phone'         => '9876543201',
            'full_name'     => 'Member One',
            'is_completed'  => true,
        ]);
        Membership::create([
            'membership_id' => '123456789002',
            'phone'         => '9876543202',
            'full_name'     => 'Member Two',
            'is_completed'  => true,
        ]);
        Membership::create([
            'membership_id' => '123456789003',
            'phone'         => '9876543203',
            'full_name'     => 'Member Three Pending',
            'is_completed'  => false,
        ]);

        // Create 1 approved volunteer
        Volunteer::create([
            'membership_id'             => '123456789001',
            'volunteer_id'              => '100001',
            'volunteer_login_id'        => '100001',
            'phone'                     => '9876543201',
            'email'                     => 'vol1@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Member One',
            'account_number'            => '1234567890',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Guntur',
            'nominee_name'              => 'Sita',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => bcrypt('password123'),
            'status'                    => 'approved',
            'is_active'                 => true,
        ]);

        // Create 1 paid donation
        Donation::create([
            'name' => 'Ramesh Kumar',
            'contact' => '9876543210',
            'phone' => '9876543210',
            'email' => 'ramesh@example.com',
            'amount' => 1000,
            'payment_status' => 'paid',
        ]);

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.stats.members'));
        $this->assertEquals(1, $response->json('data.stats.volunteers'));
        $this->assertEquals(1, $response->json('data.stats.donors'));
        $this->assertGreaterThanOrEqual(1, $response->json('data.stats.years'));
    }

    public function test_home_endpoint_returns_dynamic_banner_and_public_contact(): void
    {
        SiteSetting::set('contact_phone', '+91 8884933379');
        SiteSetting::set('contact_email', 'info@abvhps.org');
        SiteSetting::set('contact_whatsapp', '+918884933379');

        Banner::create([
            'page_key' => 'home',
            'page_name' => 'Home',
            'title' => 'Official Test Banner Title',
            'subtitle' => 'Official Test Subtitle',
            'desktop_banner' => 'banners/desktop_test.jpg',
            'mobile_banner' => 'banners/mobile_test.jpg',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $this->assertEquals('Official Test Banner Title', $response->json('data.banner.title'));
        $this->assertEquals('Official Test Subtitle', $response->json('data.banner.subtitle'));
        $this->assertStringContainsString('banners/desktop_test.jpg', $response->json('data.banner.desktop_banner'));
        $this->assertStringContainsString('banners/mobile_test.jpg', $response->json('data.banner.mobile_banner'));
        $this->assertEquals('+91 8884933379', $response->json('data.contact.phone'));
        $this->assertEquals('info@abvhps.org', $response->json('data.contact.email'));
    }

    public function test_home_endpoint_never_exposes_private_data_or_secrets(): void
    {
        // Seed private data in database
        Membership::create([
            'membership_id' => '123456789999',
            'phone'         => '9999988888',
            'full_name'     => 'Secret Member Person',
            'is_completed'  => true,
        ]);

        Volunteer::create([
            'membership_id'             => '123456789999',
            'volunteer_id'              => '100099',
            'volunteer_login_id'        => 'VOL_SECRET_101',
            'phone'                     => '9999988888',
            'email'                     => 'secretvol@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'SEC1234567',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Secret Volunteer Person',
            'account_number'            => '1234567899',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Guntur',
            'nominee_name'              => 'Secret Nominee',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9999988887',
            'document_declaration_path' => 'sec1.pdf',
            'document_voter_path'       => 'sec2.pdf',
            'document_bank_path'        => 'sec3.pdf',
            'password'                  => bcrypt('super_secret_password'),
            'status'                    => 'approved',
            'is_active'                 => true,
        ]);

        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200);
        $jsonString = $response->getContent();

        // Verify no private identity leaks
        $this->assertStringNotContainsString('Secret Member Person', $jsonString);
        $this->assertStringNotContainsString('123456789999', $jsonString);
        $this->assertStringNotContainsString('9999988888', $jsonString);
        $this->assertStringNotContainsString('Secret Volunteer Person', $jsonString);
        $this->assertStringNotContainsString('VOL_SECRET_101', $jsonString);
        $this->assertStringNotContainsString('super_secret_password', $jsonString);

        // Verify no filesystem paths leakage
        $this->assertStringNotContainsString('C:\\', $jsonString);
        $this->assertStringNotContainsString('/xampp/', $jsonString);
        $this->assertStringNotContainsString('/var/www/', $jsonString);
        $this->assertStringNotContainsString('.env', $jsonString);
    }
}
