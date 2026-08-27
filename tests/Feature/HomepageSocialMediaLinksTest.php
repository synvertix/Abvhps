<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageSocialMediaLinksTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@abvhps.org',
            'password' => bcrypt('password123'),
        ]);
    }

    private function clearAllSocialSettings(): void
    {
        SiteSetting::set('social_facebook_url', null);
        SiteSetting::set('social_instagram_url', null);
        SiteSetting::set('social_youtube_url', null);
        SiteSetting::set('social_x_url', null);
        SiteSetting::set('social_linkedin_url', null);
        SiteSetting::set('social_whatsapp_url', null);
        SiteSetting::set('social_telegram_url', null);
        SiteSetting::set('facebook_url', null);
        SiteSetting::set('twitter_url', null);
        SiteSetting::set('youtube_url', null);
    }

    // =========================================================================
    // 1. SECTION HIDDEN WHEN DISABLED
    // =========================================================================
    public function test_section_hidden_when_disabled(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps_official');
        SiteSetting::set('homepage_social_enabled', '0');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('homepage-social-media-strip', false);
        $response->assertDontSee('https://facebook.com/abvhps_official', false);
    }

    // =========================================================================
    // 2. SECTION HIDDEN WHEN ALL URLS EMPTY
    // =========================================================================
    public function test_section_hidden_when_all_urls_empty(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('homepage-social-media-strip', false);
        $response->assertDontSee('aria-label="ABVHPS on Facebook"', false);
        $response->assertDontSee('aria-label="ABVHPS on Instagram"', false);
    }

    // =========================================================================
    // 3. CONFIGURED FACEBOOK LINK RENDERS
    // =========================================================================
    public function test_configured_facebook_link_renders(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps_devotees');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('homepage-social-media-strip', false);
        $response->assertSee('https://facebook.com/abvhps_devotees', false);
        $response->assertSee('aria-label="ABVHPS on Facebook"', false);
        $response->assertSee('Facebook', false);
    }

    // =========================================================================
    // 4. CONFIGURED INSTAGRAM LINK RENDERS
    // =========================================================================
    public function test_configured_instagram_link_renders(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_instagram_url', 'https://instagram.com/abvhps_trust');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('homepage-social-media-strip', false);
        $response->assertSee('https://instagram.com/abvhps_trust', false);
        $response->assertSee('aria-label="ABVHPS on Instagram"', false);
        $response->assertSee('Instagram', false);
    }

    // =========================================================================
    // 5. YOUTUBE, X, LINKEDIN, WHATSAPP, TELEGRAM RENDER WHEN CONFIGURED
    // =========================================================================
    public function test_youtube_x_linkedin_whatsapp_telegram_render_when_configured(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_youtube_url', 'https://youtube.com/@abvhps_official');
        SiteSetting::set('social_x_url', 'https://x.com/abvhps_org');
        SiteSetting::set('social_linkedin_url', 'https://linkedin.com/company/abvhps-trust');
        SiteSetting::set('social_whatsapp_url', 'https://wa.me/919989980055');
        SiteSetting::set('social_telegram_url', 'https://t.me/abvhps_updates');

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('homepage-social-media-strip', false);

        // Check each platform URL and label
        $response->assertSee('https://youtube.com/@abvhps_official', false);
        $response->assertSee('aria-label="ABVHPS on YouTube"', false);

        $response->assertSee('https://x.com/abvhps_org', false);
        $response->assertSee('aria-label="ABVHPS on X"', false);

        $response->assertSee('https://linkedin.com/company/abvhps-trust', false);
        $response->assertSee('aria-label="ABVHPS on LinkedIn"', false);

        $response->assertSee('https://wa.me/919989980055', false);
        $response->assertSee('aria-label="ABVHPS on WhatsApp"', false);

        $response->assertSee('https://t.me/abvhps_updates', false);
        $response->assertSee('aria-label="ABVHPS on Telegram"', false);
    }

    // =========================================================================
    // 6. EMPTY PLATFORMS DO NOT RENDER
    // =========================================================================
    public function test_empty_platforms_do_not_render(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');
        SiteSetting::set('social_instagram_url', null);
        SiteSetting::set('social_youtube_url', '');
        SiteSetting::set('social_telegram_url', null);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Facebook is present
        $response->assertSee('aria-label="ABVHPS on Facebook"', false);

        // Others are not rendered
        $response->assertDontSee('aria-label="ABVHPS on Instagram"', false);
        $response->assertDontSee('aria-label="ABVHPS on YouTube"', false);
        $response->assertDontSee('aria-label="ABVHPS on Telegram"', false);
    }

    // =========================================================================
    // 7. LINKS CONTAIN TARGET="_BLANK"
    // =========================================================================
    public function test_links_contain_target_blank(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $stripStart = strpos($content, 'id="homepage-social-media-strip"');
        $this->assertNotFalse($stripStart, 'Social media strip not found in homepage content');

        $stripEnd = strpos($content, '</section>', $stripStart);
        $stripHtml = substr($content, $stripStart, $stripEnd - $stripStart);

        $this->assertStringContainsString('target="_blank"', $stripHtml);
    }

    // =========================================================================
    // 8. LINKS CONTAIN REL="NOOPENER NOREFERRER"
    // =========================================================================
    public function test_links_contain_rel_noopener_noreferrer(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_youtube_url', 'https://youtube.com/@abvhps');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $stripStart = strpos($content, 'id="homepage-social-media-strip"');
        $this->assertNotFalse($stripStart, 'Social media strip not found in homepage content');

        $stripEnd = strpos($content, '</section>', $stripStart);
        $stripHtml = substr($content, $stripStart, $stripEnd - $stripStart);

        $this->assertStringContainsString('rel="noopener noreferrer"', $stripHtml);
    }

    // =========================================================================
    // 9. UNSAFE/NON-HTTPS URLS ARE REJECTED
    // =========================================================================
    public function test_unsafe_and_non_https_urls_are_rejected(): void
    {
        $admin = $this->createAdmin();

        // 9a. Non-https HTTP rejected
        $resHttp = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'social_facebook_url' => 'http://facebook.com/abvhps',
        ]);
        $resHttp->assertSessionHasErrors('social_facebook_url');

        // 9b. javascript: scheme rejected
        $resJs = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'social_instagram_url' => 'javascript:alert(1)',
        ]);
        $resJs->assertSessionHasErrors('social_instagram_url');

        // 9c. data: scheme rejected
        $resData = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'social_youtube_url' => 'data:text/html,<script>alert(1)</script>',
        ]);
        $resData->assertSessionHasErrors('social_youtube_url');

        // 9d. Invalid WhatsApp domain rejected (must be wa.me or api.whatsapp.com)
        $resWaBad = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'social_whatsapp_url' => 'https://malicious-site.com/wa',
        ]);
        $resWaBad->assertSessionHasErrors('social_whatsapp_url');

        // 9e. Valid WhatsApp URLs accepted
        $resWaGood = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'social_whatsapp_url' => 'https://wa.me/919989980055',
        ]);
        $resWaGood->assertSessionHasNoErrors();
        $this->assertEquals('https://wa.me/919989980055', SiteSetting::get('social_whatsapp_url'));
    }

    // =========================================================================
    // 10. ADMIN CAN SAVE/UPDATE SOCIAL URLS
    // =========================================================================
    public function test_admin_can_save_and_update_social_urls(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'homepage_social_enabled' => '1',
            'homepage_social_heading' => 'JOIN OUR SOCIAL COMMUNITY',
            'homepage_social_subtext' => 'Stay connected with all ongoing temple and Seva projects across India.',
            'social_facebook_url'     => 'https://facebook.com/abvhps_official',
            'social_instagram_url'    => 'https://instagram.com/abvhps_official',
            'social_youtube_url'      => 'https://youtube.com/@abvhps_official',
            'social_x_url'            => 'https://x.com/abvhps_official',
            'social_linkedin_url'     => 'https://linkedin.com/company/abvhps',
            'social_whatsapp_url'     => 'https://wa.me/919989980055',
            'social_telegram_url'     => 'https://t.me/abvhps_official',
        ];

        $response = $this->actingAs($admin)->post(route('admin.settings.update'), $payload);
        $response->assertRedirect(route('admin.settings.index'));

        $this->assertEquals('1', SiteSetting::get('homepage_social_enabled'));
        $this->assertEquals('JOIN OUR SOCIAL COMMUNITY', SiteSetting::get('homepage_social_heading'));
        $this->assertEquals('https://facebook.com/abvhps_official', SiteSetting::get('social_facebook_url'));
        $this->assertEquals('https://instagram.com/abvhps_official', SiteSetting::get('social_instagram_url'));
        $this->assertEquals('https://youtube.com/@abvhps_official', SiteSetting::get('social_youtube_url'));
        $this->assertEquals('https://x.com/abvhps_official', SiteSetting::get('social_x_url'));
        $this->assertEquals('https://linkedin.com/company/abvhps', SiteSetting::get('social_linkedin_url'));
        $this->assertEquals('https://wa.me/919989980055', SiteSetting::get('social_whatsapp_url'));
        $this->assertEquals('https://t.me/abvhps_official', SiteSetting::get('social_telegram_url'));

        // Public page reflects updated heading and all 7 links
        $publicRes = $this->get('/');
        $publicRes->assertStatus(200);
        $publicRes->assertSee('JOIN OUR SOCIAL COMMUNITY', false);
        $publicRes->assertSee('Stay connected with all ongoing temple', false);
        $publicRes->assertSee('https://facebook.com/abvhps_official', false);
        $publicRes->assertSee('https://instagram.com/abvhps_official', false);
        $publicRes->assertSee('https://youtube.com/@abvhps_official', false);
        $publicRes->assertSee('https://x.com/abvhps_official', false);
        $publicRes->assertSee('https://linkedin.com/company/abvhps', false);
        $publicRes->assertSee('https://wa.me/919989980055', false);
        $publicRes->assertSee('https://t.me/abvhps_official', false);
    }

    // =========================================================================
    // 11. EXISTING HOMEPAGE SECTIONS STILL RENDER (SECTION PLACEMENT & INTEGRITY)
    // =========================================================================
    public function test_existing_homepage_sections_still_render_in_correct_order(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps_official');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Supporting Partners strip is present
        $posPartners = strpos($content, 'homepage-sponsors-strip');
        $this->assertNotFalse($posPartners, 'Supporting Partners strip not found');

        // 2. Social Media strip is present
        $posSocial = strpos($content, 'homepage-social-media-strip');
        $this->assertNotFalse($posSocial, 'Social Media strip not found');

        // 3. Footer is present
        $posFooter = strpos($content, '<footer');
        $this->assertNotFalse($posFooter, 'Footer not found');

        // 4. Exact order: Supporting Partners -> Social Media -> Footer
        $this->assertTrue($posPartners < $posSocial, 'Supporting Partners must appear before Social Media section');
        $this->assertTrue($posSocial < $posFooter, 'Social Media must appear before Footer');

        // 5. Check core service projects and statistics strips still exist
        $this->assertStringContainsString('Our Core Service Projects', $content);
        $this->assertStringContainsString('homepage-statistics-strip', $content);
    }

    // =========================================================================
    // 12. NO FAKE DEFAULT SOCIAL URLS EXIST
    // =========================================================================
    public function test_no_fake_default_social_urls_exist(): void
    {
        $this->clearAllSocialSettings();

        // When nothing is set in the database, active social links must be strictly empty
        $activeLinks = SiteSetting::getActiveSocialLinks();
        $this->assertEmpty($activeLinks, 'getActiveSocialLinks() must return empty array when no URLs configured');

        // When nothing is configured, homepage response must not contain any fake platform URLs
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('https://facebook.com/fake', false);
        $response->assertDontSee('https://instagram.com/fake', false);
        $response->assertDontSee('https://x.com/fake', false);
        $response->assertDontSee('homepage-social-media-strip', false);
        $response->assertDontSee('top-bar-social-links', false);
    }

    // =========================================================================
    // 13. TOP BAR: CONFIGURED FACEBOOK APPEARS IN TOP BAR
    // =========================================================================
    public function test_configured_facebook_appears_in_top_bar(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps_top');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="top-bar-social-links"', $content);
        $this->assertStringContainsString('https://facebook.com/abvhps_top', $content);
        $this->assertStringContainsString('aria-label="ABVHPS on Facebook"', $content);
    }

    // =========================================================================
    // 14. TOP BAR: CONFIGURED INSTAGRAM APPEARS IN TOP BAR
    // =========================================================================
    public function test_configured_instagram_appears_in_top_bar(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_instagram_url', 'https://instagram.com/abvhps_top');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('id="top-bar-social-links"', $content);
        $this->assertStringContainsString('https://instagram.com/abvhps_top', $content);
        $this->assertStringContainsString('aria-label="ABVHPS on Instagram"', $content);
    }

    // =========================================================================
    // 15. TOP BAR: ALL CONFIGURED PLATFORMS RENDER IN TOP BAR
    // =========================================================================
    public function test_all_configured_platforms_render_in_top_bar(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');
        SiteSetting::set('social_instagram_url', 'https://instagram.com/abvhps');
        SiteSetting::set('social_youtube_url', 'https://youtube.com/@abvhps');
        SiteSetting::set('social_x_url', 'https://x.com/abvhps');
        SiteSetting::set('social_linkedin_url', 'https://linkedin.com/company/abvhps');
        SiteSetting::set('social_whatsapp_url', 'https://wa.me/919989980055');
        SiteSetting::set('social_telegram_url', 'https://t.me/abvhps');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $topBarStart = strpos($content, 'id="top-bar-social-links"');
        $this->assertNotFalse($topBarStart, 'Top bar social links container not found');

        $topBarEnd = strpos($content, '</header>', $topBarStart);
        $topBarHtml = substr($content, $topBarStart, $topBarEnd - $topBarStart);

        $this->assertStringContainsString('aria-label="ABVHPS on Facebook"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on Instagram"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on YouTube"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on X"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on LinkedIn"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on WhatsApp"', $topBarHtml);
        $this->assertStringContainsString('aria-label="ABVHPS on Telegram"', $topBarHtml);
    }

    // =========================================================================
    // 16. TOP BAR: EMPTY PLATFORMS ARE OMITTED
    // =========================================================================
    public function test_empty_platform_is_omitted_from_top_bar(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');
        SiteSetting::set('social_telegram_url', null);

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $topBarStart = strpos($content, 'id="top-bar-social-links"');
        $this->assertNotFalse($topBarStart);

        $topBarEnd = strpos($content, '</header>', $topBarStart);
        $topBarHtml = substr($content, $topBarStart, $topBarEnd - $topBarStart);

        $this->assertStringContainsString('aria-label="ABVHPS on Facebook"', $topBarHtml);
        $this->assertStringNotContainsString('aria-label="ABVHPS on Telegram"', $topBarHtml);
    }

    // =========================================================================
    // 17. TOP BAR: SOCIAL_ENABLED=FALSE HIDES TOP-BAR SOCIAL ICONS
    // =========================================================================
    public function test_social_enabled_false_hides_top_bar_social_icons(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');
        SiteSetting::set('homepage_social_enabled', '0');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringNotContainsString('id="top-bar-social-links"', $content);
    }

    // =========================================================================
    // 18. TOP BAR: TARGET="_BLANK" AND REL="NOOPENER NOREFERRER"
    // =========================================================================
    public function test_top_bar_links_have_security_attributes(): void
    {
        $this->clearAllSocialSettings();
        SiteSetting::set('homepage_social_enabled', '1');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $topBarStart = strpos($content, 'id="top-bar-social-links"');
        $this->assertNotFalse($topBarStart);

        $topBarEnd = strpos($content, '</header>', $topBarStart);
        $topBarHtml = substr($content, $topBarStart, $topBarEnd - $topBarStart);

        $this->assertStringContainsString('target="_blank"', $topBarHtml);
        $this->assertStringContainsString('rel="noopener noreferrer"', $topBarHtml);
    }

    // =========================================================================
    // 19. TOP BAR: PHONE AND EMAIL REMAIN VISIBLE
    // =========================================================================
    public function test_existing_phone_and_info_email_remain_visible(): void
    {
        SiteSetting::set('contact_phone', '+91 8884933379');
        SiteSetting::set('contact_email', 'info@abvhps.org');
        SiteSetting::set('social_facebook_url', 'https://facebook.com/abvhps');

        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();
        $headerStart = strpos($content, '<header');
        $headerEnd = strpos($content, '</header>', $headerStart);
        $headerHtml = substr($content, $headerStart, $headerEnd - $headerStart);

        $this->assertStringContainsString('+91 8884933379', $headerHtml);
        $this->assertStringContainsString('info@abvhps.org', $headerHtml);
    }
}
