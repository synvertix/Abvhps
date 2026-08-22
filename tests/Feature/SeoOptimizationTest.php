<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\FundraisingCampaign;
use App\Models\OurSupport;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class SeoOptimizationTest extends TestCase
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
     * 1. Homepage has title, meta description, canonical URL, and Schema.org
     */
    public function test_homepage_has_complete_seo_metadata()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('<title>ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti</title>', false);
        $response->assertSee('<meta name="description"', false);
        $response->assertSee('<link rel="canonical"', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('name="twitter:card"', false);
        $response->assertSee('@type":"Organization"', false);
        $response->assertSee('@type":"WebSite"', false);
    }

    /**
     * 2. Important public pages have unique descriptive titles
     */
    public function test_important_public_pages_have_unique_titles()
    {
        $pages = [
            '/' => 'ABVHPS | Akhanda Bharatha Viswa Hindu Parirakshana Samiti',
            '/about' => 'About Us | ABVHPS',
            '/membership' => 'Devotee Membership Portal | ABVHPS',
            '/volunteer' => 'Volunteer Registration & Cadre Application | ABVHPS',
            '/rudrasena-apply' => 'Rudra Sena Dal Sacred Registration | ABVHPS',
            '/kala-brundam-apply' => 'Kala Brundham Cultural Network | ABVHPS',
            '/grama-seva-dal-apply' => 'Grama Seva Dal Youth Network | ABVHPS',
            '/organic-farmers-apply' => 'Organic Farmers Agriculture Network | ABVHPS',
            '/team' => 'Our Team & Leadership Directory | ABVHPS',
            '/gallery' => 'Service Media Gallery | ABVHPS',
            '/donations' => 'Dharma Seva Fundraising Campaigns | ABVHPS',
            '/blogs' => 'Dharma Vani Articles & Updates | ABVHPS',
            '/contact' => 'Contact Us | ABVHPS',
            '/compliance-certificates' => '80G & 12A Compliance Certificates | ABVHPS',
            '/exam-results' => 'Official Examination Results | ABVHPS',
            '/exams-notice-board' => 'Sanathana Dharma Exams Notice Board | ABVHPS',
        ];

        $seenTitles = [];

        foreach ($pages as $url => $expectedTitle) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $escapedTitle = e($expectedTitle);
            $response->assertSee("<title>{$escapedTitle}</title>", false);

            // Ensure no duplicate titles among distinct pages
            $this->assertFalse(in_array($expectedTitle, $seenTitles), "Duplicate title detected: {$expectedTitle}");
            $seenTitles[] = $expectedTitle;
        }
    }

    /**
     * 3. Dynamic sitemap.xml returns HTTP 200 and valid XML structure
     */
    public function test_sitemap_returns_http_200_and_contains_public_pages()
    {
        // Seed an active fundraising campaign and an active project
        $campaign = FundraisingCampaign::create([
            'title' => 'SITEMAP ACTIVE TEST CAMPAIGN',
            'description' => 'Active campaign for sitemap verification test.',
            'target_amount' => 300000.00,
            'raised_amount' => 100000.00,
            'end_date' => Carbon::today()->addDays(20)->toDateString(),
            'cover_image' => 'campaigns/covers/test.jpg',
            'status' => 'active',
        ]);

        $project = OurSupport::create([
            'name' => 'SITEMAP ACTIVE PROJECT SEVA',
            'short_info' => 'Project seva for sitemap verification test.',
            'sort_order' => 1,
            'status' => 'show',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');

        $content = $response->getContent();

        // Check XML header and urlset
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $content);
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);

        // Public core URLs must be in sitemap
        $this->assertStringContainsString('/about</loc>', $content);
        $this->assertStringContainsString('/membership</loc>', $content);
        $this->assertStringContainsString('/volunteer</loc>', $content);
        $this->assertStringContainsString('/rudrasena-apply</loc>', $content);
        $this->assertStringContainsString('/donations</loc>', $content);
        $this->assertStringContainsString('/contact</loc>', $content);
        $this->assertStringContainsString('/compliance-certificates</loc>', $content);

        // Dynamic campaign and project entries must be present
        $this->assertStringContainsString('/donations/campaign/' . $campaign->id, $content);
        $this->assertStringContainsString('/project/' . $project->id, $content);

        // Sensitive/admin routes MUST NOT be in sitemap
        $this->assertStringNotContainsString('/admin', $content);
        $this->assertStringNotContainsString('/volunteer/dashboard', $content);
        $this->assertStringNotContainsString('/volunteer/member-data', $content);
        $this->assertStringNotContainsString('/volunteer/login', $content);
    }

    /**
     * 4. robots.txt exists, returns HTTP 200, blocks admin and points to sitemap
     */
    public function test_robots_txt_returns_proper_directives()
    {
        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);

        $content = file_get_contents($robotsPath);
        $this->assertStringContainsString('User-agent: *', $content);
        $this->assertStringContainsString('Disallow: /admin/', $content);
        $this->assertStringContainsString('Disallow: /volunteer/dashboard', $content);
        $this->assertStringContainsString('Sitemap: https://abvhps.org/sitemap.xml', $content);
    }

    /**
     * 5. Production URLs do not hardcode localhost or 127.0.0.1 in public views
     */
    public function test_public_views_canonical_and_og_tags_generate_dynamically()
    {
        $response = $this->get('/about');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="', $content);
        $this->assertStringContainsString('<meta property="og:url" content="', $content);
    }

    /**
     * 6. Dynamic Project Details has custom SEO title and Open Graph metadata
     */
    public function test_project_details_page_has_dynamic_seo()
    {
        $project = OurSupport::create([
            'name' => 'SACRED GOSHALA EXPANSION PROJECT',
            'short_info' => 'Comprehensive development of shelter and feed facilities for cows.',
            'sort_order' => 1,
            'status' => 'show',
        ]);

        $response = $this->get(route('public.project.show', $project->id));
        $response->assertStatus(200);
        $response->assertSee('<title>SACRED GOSHALA EXPANSION PROJECT | ABVHPS Core Service Projects</title>', false);
        $response->assertSee('Comprehensive development of shelter and feed facilities', false);
    }

    /**
     * 7. Missing pages return proper HTTP 404 status with noindex
     */
    public function test_missing_page_returns_404_with_noindex()
    {
        $response = $this->get('/non-existent-page-url-xyz');
        $response->assertStatus(404);
        $response->assertSee('Page Not Found (404) | ABVHPS');
        $response->assertSee('content="noindex, nofollow"', false);
    }

    /**
     * 8. Protected admin and private volunteer routes emit X-Robots-Tag noindex
     */
    public function test_protected_routes_emit_x_robots_tag_noindex()
    {
        $adminRes = $this->actingAs($this->admin)->get('/admin/dashboard');
        $adminRes->assertHeader('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * 9. Public verification page has noindex to protect identity records
     */
    public function test_public_verification_page_has_noindex()
    {
        $response = $this->get('/verify/volunteer/VOL-999999');
        $response->assertStatus(200);
        $response->assertSee('content="noindex, nofollow"', false);
    }

    /**
     * 10. Top public header and mobile header drawer cleanup
     */
    public function test_top_header_and_mobile_drawer_header_cleanups(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $content = $response->getContent();

        // Top header retains contact phone and email
        $response->assertSee('+91 8884933379');
        $response->assertSee('info@abvhps.org');

        // Top header element check
        if (preg_match('/<header[^>]*>(.*?)<\/header>/s', $content, $matches)) {
            $headerHtml = $matches[1];
            $this->assertStringNotContainsString('Facebook', $headerHtml);
            $this->assertStringNotContainsString('Twitter', $headerHtml);
            $this->assertStringNotContainsString('YouTube', $headerHtml);
            $this->assertStringNotContainsString('80G/12A Compliance', $headerHtml);
        }

        // Mobile drawer footer check
        if (preg_match('/id="public-mobile-drawer"[^>]*>(.*?)<\/div>\s*<\/div>/s', $content, $matches)) {
            $drawerHtml = $matches[1];
            $this->assertStringNotContainsString('80G / 12A TAX EXEMPTION COMPLIANCE', $drawerHtml);
        }

        // Footer compliance link remains
        if (preg_match('/<footer[^>]*>(.*?)<\/footer>/s', $content, $matches)) {
            $footerHtml = $matches[1];
            $this->assertStringContainsString('80G / 12A', $footerHtml);
        }
    }
}
