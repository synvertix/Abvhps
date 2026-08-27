<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageJoinStripTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create an admin user.
     */
    private function createAdmin(): User
    {
        return User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@abvhps.org',
            'password' => bcrypt('password123'),
        ]);
    }

    // =========================================================================
    // MEMBERSHIP CTA IN FLOATING STRIP
    // =========================================================================

    /**
     * 1, 2, 3, 4, 5, 6. Floating strip renders Why Join on left, Membership CTA on right,
     * uses canonical route('membership.form') (/membership), and contains no Volunteer CTA in this strip.
     */
    public function test_homepage_floating_strip_renders_membership_cta_and_canonical_route(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('homepage-floating-join-strip', false);

        // Left Side: Why Join ABVHPS remains present
        $response->assertSee('WHY JOIN ABVHPS?', false);
        $response->assertSee('Serve the Community', false);
        $response->assertSee('Support Dharma Activities', false);
        $response->assertSee('Participate in Seva Programs', false);
        $response->assertSee('Build Local Leadership', false);

        // Right Side: Membership Content
        $response->assertSee('ABVHPS MEMBERSHIP', false);
        $response->assertSee('BECOME AN ABVHPS MEMBER', false);
        $response->assertSee('BECOME A MEMBER', false);
        $response->assertSee(route('membership.form'), false);

        // Volunteer CTA is removed from this floating strip
        $content = $response->getContent();
        $stripStart = strpos($content, 'id="homepage-floating-join-strip"');
        $stripEnd = strpos($content, 'id="homepage-statistics-strip"');
        $this->assertNotFalse($stripStart);
        $this->assertNotFalse($stripEnd);
        $stripHtml = substr($content, $stripStart, $stripEnd - $stripStart);

        $this->assertStringNotContainsString('JOIN AS A VOLUNTEER', $stripHtml);
        $this->assertStringNotContainsString('JOIN AS VOLUNTEER', $stripHtml);
        $this->assertStringNotContainsString(route('volunteer.check'), $stripHtml);
    }

    /**
     * 7. Existing Volunteer application flow remains available at /volunteer.
     */
    public function test_volunteer_eligibility_flow_remains_available_elsewhere(): void
    {
        $response = $this->get(route('volunteer.check'));
        $response->assertStatus(200);
        $response->assertSee('Volunteer Identity Check', false);

        $unverifiedApp = $this->get(route('volunteer.application'));
        $unverifiedApp->assertRedirect(route('volunteer.check'));
    }

    /**
     * 8. Canonical named membership route matches /membership and no duplicate system is created.
     */
    public function test_canonical_membership_route_matches_expected_endpoint(): void
    {
        $this->assertEquals(url('/membership'), route('membership.form'));
    }

    /**
     * 9. Admin can update Membership CTA content in Site Settings.
     */
    public function test_admin_can_update_membership_join_strip_content(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_title'                   => 'ABVHPS Portal',
            'contact_phone'                => '+91 8884933379',
            'contact_email'                => 'info@abvhps.org',
            'contact_address'              => 'HQ Address',
            'footer_about'                 => 'About ABVHPS',
            'homepage_join_enabled'        => '1',
            'homepage_join_why_heading'    => 'WHY EMBRACE SEVA WITH ABVHPS?',
            'homepage_join_why_text'       => 'Custom why join description for devotees.',
            'homepage_join_member_heading' => 'ENROL AS A LIFE MEMBER',
            'homepage_join_member_text'    => 'Custom membership description for devotees.',
            'homepage_join_cta_text'       => 'JOIN ABVHPS NOW',
        ]);

        $response->assertRedirect(route('admin.settings.index'));

        // Public homepage reflects updated membership content
        $publicResponse = $this->get('/');
        $publicResponse->assertSee('WHY EMBRACE SEVA WITH ABVHPS?', false);
        $publicResponse->assertSee('Custom why join description for devotees.', false);
        $publicResponse->assertSee('ENROL AS A LIFE MEMBER', false);
        $publicResponse->assertSee('Custom membership description for devotees.', false);
        $publicResponse->assertSee('JOIN ABVHPS NOW', false);
        $publicResponse->assertSee(route('membership.form'), false);
    }

    /**
     * Admin can disable Join strip, and disabled strip does not render.
     */
    public function test_admin_can_disable_join_strip(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_title'            => 'ABVHPS Portal',
            'contact_phone'         => '+91 8884933379',
            'contact_email'         => 'info@abvhps.org',
            'contact_address'       => 'HQ Address',
            'footer_about'          => 'About ABVHPS',
            'homepage_join_enabled' => '0',
        ]);

        $publicResponse = $this->get('/');
        $publicResponse->assertDontSee('homepage-floating-join-strip', false);
        $publicResponse->assertDontSee('WHY JOIN ABVHPS?', false);
    }

    // =========================================================================
    // SPONSORS / PARTNERS SCROLLING STRIP
    // =========================================================================

    /**
     * 10, 11, 12, 13, 14, 15, 16. Sponsor strip appears AFTER Core Service Projects,
     * contains default sponsor names, and renders text-only cards without fake logo URLs.
     */
    public function test_sponsor_strip_renders_after_projects_with_clean_text_wordmarks(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        $posProjects = strpos($content, 'Our Core Service Projects');
        $posSponsors = strpos($content, 'homepage-sponsors-strip');

        $this->assertNotFalse($posProjects, 'Our Core Service Projects section not found');
        $this->assertNotFalse($posSponsors, 'Sponsors strip not found');
        $this->assertTrue($posProjects < $posSponsors, 'Sponsors strip must appear AFTER Our Core Service Projects');

        // Heading
        $response->assertSee('OUR SUPPORTING PARTNERS', false);

        // Required initial sponsors
        $response->assertSee('Synvertix Technologies', false);
        $response->assertSee('MMP', false);
        $response->assertSee('MMS', false);
        $response->assertSee('MMA', false);
        $response->assertSee('Taskly', false);

        // Ensure no fake logo URLs or images are rendered inside sponsor strip
        $stripStart = strpos($content, 'id="homepage-sponsors-strip"');
        $stripEnd = strpos($content, '</section>', $stripStart);
        $stripHtml = substr($content, $stripStart, $stripEnd - $stripStart);

        $this->assertStringNotContainsString('<img', $stripHtml);
        $this->assertStringNotContainsString('http://', $stripHtml);
        $this->assertStringNotContainsString('https://', $stripHtml);
    }

    /**
     * 17 & 18. Admin can update or disable sponsor strip.
     */
    public function test_admin_can_manage_and_disable_sponsor_strip(): void
    {
        $admin = $this->createAdmin();

        // 1. Admin customizes sponsor heading and list
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_title'                => 'ABVHPS Portal',
            'contact_phone'             => '+91 8884933379',
            'contact_email'             => 'info@abvhps.org',
            'contact_address'           => 'HQ Address',
            'footer_about'              => 'About ABVHPS',
            'homepage_sponsors_enabled' => '1',
            'homepage_sponsors_heading' => 'OUR VALUED SUPPORTERS',
            'homepage_sponsors_list'    => "Company Alpha\nCompany Beta\nCompany Gamma",
        ]);

        $publicResponse = $this->get('/');
        $publicResponse->assertSee('OUR VALUED SUPPORTERS', false);
        $publicResponse->assertSee('Company Alpha', false);
        $publicResponse->assertSee('Company Beta', false);
        $publicResponse->assertSee('Company Gamma', false);

        // 2. Admin disables sponsor strip
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'site_title'                => 'ABVHPS Portal',
            'contact_phone'             => '+91 8884933379',
            'contact_email'             => 'info@abvhps.org',
            'contact_address'           => 'HQ Address',
            'footer_about'              => 'About ABVHPS',
            'homepage_sponsors_enabled' => '0',
        ]);

        $disabledResponse = $this->get('/');
        $disabledResponse->assertDontSee('homepage-sponsors-strip', false);
        $disabledResponse->assertDontSee('OUR VALUED SUPPORTERS', false);
    }

    /**
     * 19, 20, 21. Sponsor strip markup contains accessibility labels, marquee classes, and reduced-motion styles.
     */
    public function test_sponsor_strip_accessibility_and_reduced_motion(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        $response->assertSee('aria-label="OUR SUPPORTING PARTNERS"', false);
        $response->assertSee('aria-hidden="true"', false);
        $response->assertSee('overflow-hidden', false);
        $response->assertSee('prefers-reduced-motion', false);
        $response->assertSee('sponsorMarquee', false);
    }

    // =========================================================================
    // SECTION SEQUENCE
    // =========================================================================

    /**
     * Full sequence check:
     * Vision -> Floating Join Strip -> Statistics Strip -> Fundraising Campaigns -> Core Service Projects -> Sponsors
     */
    public function test_full_homepage_section_sequence(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        $posVision = strpos($content, 'Our Vision');
        $posJoinStrip = strpos($content, 'homepage-floating-join-strip');
        $posStats = strpos($content, 'homepage-statistics-strip');
        $posFundraising = strpos($content, 'Fundraising Campaigns');
        $posProjects = strpos($content, 'Our Core Service Projects');
        $posSponsors = strpos($content, 'homepage-sponsors-strip');

        $this->assertNotFalse($posVision, 'Vision not found');
        $this->assertNotFalse($posJoinStrip, 'Join Strip not found');
        $this->assertNotFalse($posStats, 'Stats Strip not found');
        $this->assertNotFalse($posFundraising, 'Fundraising Campaigns not found');
        $this->assertNotFalse($posProjects, 'Projects not found');
        $this->assertNotFalse($posSponsors, 'Sponsors Strip not found');

        $this->assertTrue($posVision < $posJoinStrip, 'Vision -> Join Strip');
        $this->assertTrue($posJoinStrip < $posStats, 'Join Strip -> Stats Strip');
        $this->assertTrue($posStats < $posFundraising, 'Stats Strip -> Fundraising Campaigns');
        $this->assertTrue($posFundraising < $posProjects, 'Fundraising Campaigns -> Core Service Projects');
        $this->assertTrue($posProjects < $posSponsors, 'Core Service Projects -> Sponsors Strip');
    }
}
