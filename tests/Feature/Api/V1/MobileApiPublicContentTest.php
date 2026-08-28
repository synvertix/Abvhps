<?php

namespace Tests\Feature\Api\V1;

use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\FundraisingCampaign;
use App\Models\Gallery;
use App\Models\OurSupport;
use App\Models\SiteSetting;
use App\Models\TaxCertificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiPublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_endpoint_returns_safe_organization_and_values_structure(): void
    {
        SiteSetting::set('organization_founded_year', 2023);

        $response = $this->getJson(route('api.v1.about'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'banner',
                    'organization' => [
                        'name',
                        'short_name',
                        'tagline',
                        'founded_year',
                        'registration_no',
                        'founder_guru',
                        'logo_url',
                    ],
                    'mission' => [
                        'title',
                        'paragraphs',
                    ],
                    'core_values' => [
                        '*' => ['id', 'title', 'description', 'icon'],
                    ],
                    'pillars' => [
                        '*' => ['title', 'description'],
                    ],
                ],
            ]);

        $this->assertEquals('ABVHPS', $response->json('data.organization.short_name'));
        $this->assertCount(4, $response->json('data.core_values'));
    }

    public function test_projects_endpoints_return_active_projects_only(): void
    {
        $activeProject = OurSupport::create([
            'name'        => 'Goshala Protection Seva',
            'short_info'  => 'Daily care and shelter for indigenous cows.',
            'image_path'  => 'projects/goshala.jpg',
            'status'      => 'show',
            'sort_order'  => 1,
        ]);

        $hiddenProject = OurSupport::create([
            'name'        => 'Hidden Draft Project',
            'short_info'  => 'Draft notes',
            'image_path'  => null,
            'status'      => 'hide',
            'sort_order'  => 2,
        ]);

        $listRes = $this->getJson(route('api.v1.projects.index'));
        $listRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeProject->id)
            ->assertJsonPath('data.0.name', 'Goshala Protection Seva');

        $detailRes = $this->getJson(route('api.v1.projects.show', ['id' => $activeProject->id]));
        $detailRes->assertOk()
            ->assertJsonPath('data.id', $activeProject->id);

        $hiddenRes = $this->getJson(route('api.v1.projects.show', ['id' => $hiddenProject->id]));
        $hiddenRes->assertNotFound();
    }

    public function test_campaigns_endpoints_return_active_campaigns_with_formatted_amounts(): void
    {
        $activeCampaign = FundraisingCampaign::create([
            'title'         => 'Temple Renovation Seva',
            'description'   => 'Reviving ancient historical temples.',
            'target_amount' => 500000,
            'raised_amount' => 250000,
            'end_date'      => now()->addDays(30)->toDateString(),
            'cover_image'   => 'campaigns/temple.jpg',
            'status'        => 'active',
        ]);

        $expiredCampaign = FundraisingCampaign::create([
            'title'         => 'Concluded Flood Relief',
            'description'   => 'Relief work completed.',
            'target_amount' => 100000,
            'raised_amount' => 100000,
            'end_date'      => now()->subDays(5)->toDateString(),
            'cover_image'   => 'campaigns/flood.jpg',
            'status'        => 'active',
        ]);

        $listRes = $this->getJson(route('api.v1.campaigns.index'));
        $listRes->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeCampaign->id)
            ->assertJsonPath('data.0.target_amount', 500000)
            ->assertJsonPath('data.0.raised_amount', 250000)
            ->assertJsonPath('data.0.percent', 50)
            ->assertJsonPath('data.0.target_formatted', '₹5,00,000')
            ->assertJsonPath('data.0.raised_formatted', '₹2,50,000');

        $this->assertNotEmpty($listRes->json('data.0.whatsapp_share_url'));

        $detailRes = $this->getJson(route('api.v1.campaigns.show', ['id' => $activeCampaign->id]));
        $detailRes->assertOk()
            ->assertJsonPath('data.id', $activeCampaign->id);

        $expiredRes = $this->getJson(route('api.v1.campaigns.show', ['id' => $expiredCampaign->id]));
        $expiredRes->assertNotFound();
    }

    public function test_certificates_endpoint_returns_active_statutory_records(): void
    {
        $cert = TaxCertificate::create([
            'title'            => '80G Tax Exemption Certificate',
            'certificate_type' => 'Section 80G',
            'document_number'  => '80G-ABVHPS-2024',
            'valid_from'       => '2023-04-01',
            'valid_to'         => '2028-03-31',
            'file_path'        => 'certifications/80G.pdf',
            'description'      => '50% Tax deduction eligibility certificate.',
            'is_active'        => true,
        ]);

        TaxCertificate::create([
            'title'            => 'Inactive Certificate',
            'certificate_type' => 'Old Registration',
            'document_number'  => 'OLD-001',
            'file_path'        => 'certifications/old.pdf',
            'is_active'        => false,
        ]);

        $response = $this->getJson(route('api.v1.certificates'));
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', '80G Tax Exemption Certificate')
            ->assertJsonPath('data.0.document_number', '80G-ABVHPS-2024')
            ->assertJsonPath('data.0.download_url', asset('certifications/80G.pdf'));
    }

    public function test_blogs_endpoints_return_paginated_active_articles(): void
    {
        $blog = Blog::create([
            'title'          => 'Sanathana Dharma Awareness Youth Camp',
            'content'        => '<p>Over 500 youth gathered for cultural training and seva awareness in Kadapa.</p>',
            'thumbnail_path' => 'blogs/camp_thumb.jpg',
            'image_path'     => 'blogs/camp.jpg',
            'status'         => 'active',
        ]);

        Blog::create([
            'title'   => 'Draft Unpublished Blog',
            'content' => 'Internal draft content',
            'status'  => 'draft',
        ]);

        $listRes = $this->getJson(route('api.v1.blogs.index'));
        $listRes->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $blog->id)
            ->assertJsonPath('data.0.title', 'Sanathana Dharma Awareness Youth Camp');

        $detailRes = $this->getJson(route('api.v1.blogs.show', ['id' => $blog->id]));
        $detailRes->assertOk()
            ->assertJsonPath('data.id', $blog->id);
        $this->assertStringContainsString('Over 500 youth gathered', (string) $detailRes->json('data.content'));
    }

    public function test_gallery_endpoint_returns_paginated_media_with_type_filter(): void
    {
        Gallery::create([
            'image_path' => 'gallery/photo1.jpg',
            'media_type' => 'image',
        ]);

        Gallery::create([
            'video_url'  => 'https://www.youtube.com/watch?v=sample123',
            'media_type' => 'video',
        ]);

        $allRes = $this->getJson(route('api.v1.gallery'));
        $allRes->assertOk()
            ->assertJsonPath('meta.total', 2);

        $videoRes = $this->getJson(route('api.v1.gallery', ['type' => 'video']));
        $videoRes->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.media_type', 'video')
            ->assertJsonPath('data.0.video_url', 'https://www.youtube.com/watch?v=sample123');
    }

    public function test_contact_get_returns_official_contact_and_social_channels(): void
    {
        SiteSetting::set('contact_phone', '+91 8884933379');
        SiteSetting::set('contact_email', 'info@abvhps.org');
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_youtube_url', 'https://youtube.com/@abvhps');

        $response = $this->getJson(route('api.v1.contact.show'));
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.phone', '+91 8884933379')
            ->assertJsonPath('data.email', 'info@abvhps.org');
    }

    public function test_contact_post_validates_and_stores_clean_message(): void
    {
        $payload = [
            'name'    => 'Ramesh Sharma',
            'email'   => 'ramesh@example.com',
            'phone'   => '+91 9876543210',
            'subject' => 'Volunteering in Andhra Pradesh',
            'message' => 'Namaste! I would like to participate in village temple protection activities.',
        ];

        $response = $this->postJson(route('api.v1.contact.submit'), $payload);
        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_messages', [
            'name'   => 'Ramesh Sharma',
            'email'  => 'ramesh@example.com',
            'source' => 'MOBILE_APP',
            'status' => 'unread',
        ]);
    }

    public function test_contact_post_rejects_external_urls_anti_spam(): void
    {
        $payload = [
            'name'    => 'Spammer Bot',
            'email'   => 'spam@example.com',
            'message' => 'Check out this website https://spam-link.xyz/buy-now for cheap offers!',
        ];

        $response = $this->postJson(route('api.v1.contact.submit'), $payload);
        $response->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'spam@example.com',
        ]);
    }

    public function test_contact_post_silently_discards_honeypot_bot(): void
    {
        $payload = [
            'website_trap_honeypot' => 'bot-value',
            'name'                  => 'Bot Submission',
            'email'                 => 'bot@example.com',
            'message'               => 'Random automated text',
        ];

        $response = $this->postJson(route('api.v1.contact.submit'), $payload);
        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'bot@example.com',
        ]);
    }
}
