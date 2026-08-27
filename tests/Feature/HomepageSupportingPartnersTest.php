<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageSupportingPartnersTest extends TestCase
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

    private function createVolunteer(): User
    {
        $user = User::create([
            'name'     => 'Volunteer Devotee',
            'email'    => 'volunteer@abvhps.org',
            'password' => bcrypt('password123'),
        ]);

        $member = \App\Models\Membership::create([
            'membership_id' => '123456789012',
            'phone'         => '9876543210',
            'payment_status'=> 'success',
            'full_name'     => 'Volunteer Devotee',
            'country'       => 'India',
            'state'         => 'Andhra Pradesh',
            'district'      => 'Kadapa',
            'is_completed'  => 1,
        ]);

        Volunteer::create([
            'user_id'             => $user->id,
            'membership_id'       => $member->membership_id,
            'phone'               => $member->phone,
            'qualification'       => 'Graduate',
            'voter_id_number'     => 'VTR12345',
            'email'               => 'volunteer@abvhps.org',
            'bank_name'           => 'SBI',
            'account_holder_name' => 'Volunteer Devotee',
            'account_number'      => '1234567890',
            'ifsc_code'           => 'SBIN0001234',
            'branch_name'         => 'Kadapa',
            'nominee_name'        => 'Mother',
            'nominee_relation'    => 'Mother',
            'nominee_phone'       => '9876543210',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'status'              => 'approved',
            'is_active'           => true,
            'volunteer_login_id'  => '100001',
            'password'            => bcrypt('password123'),
            'must_change_password'=> false,
        ]);

        return $user;
    }

    // =========================================================================
    // 1–7. PUBLIC DISPLAY, COMPACT LAYOUT & INITIAL PARTNERS
    // =========================================================================

    public function test_supporting_partners_appears_after_projects_with_compact_layout_and_default_names(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Appears after Core Service Projects
        $posProjects = strpos($content, 'Our Core Service Projects');
        $posSponsors = strpos($content, 'homepage-sponsors-strip');
        $this->assertNotFalse($posProjects, 'Core Service Projects section not found');
        $this->assertNotFalse($posSponsors, 'Supporting Partners strip not found');
        $this->assertTrue($posProjects < $posSponsors, 'Supporting Partners must appear AFTER Core Service Projects');

        // 2. Compact styling markers & no oversized padding
        $response->assertSee('py-5 sm:py-6', false);
        $response->assertSee('text-base sm:text-lg font-extrabold', false);
        $response->assertDontSee('py-12 bg-white border-t border-gray-200 overflow-hidden', false);

        // 3–7. Default 5 partner names render
        $response->assertSee('Synvertix Technologies', false);
        $response->assertSee('MMP', false);
        $response->assertSee('MMS', false);
        $response->assertSee('MMA', false);
        $response->assertSee('Taskly', false);

        // Default text-only rendering without broken img tags
        $stripStart = strpos($content, 'id="homepage-sponsors-strip"');
        $stripEnd = strpos($content, '</section>', $stripStart);
        $stripHtml = substr($content, $stripStart, $stripEnd - $stripStart);
        $this->assertStringNotContainsString('<img', $stripHtml);
    }

    // =========================================================================
    // 8–11. ADMIN MANAGEMENT (ADD, RENAME, REMOVE, REORDER)
    // =========================================================================

    public function test_admin_can_add_rename_remove_and_reorder_partners(): void
    {
        $admin = $this->createAdmin();

        // 8. Add Partner
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name'  => 'Prerana Seva Foundation',
            'new_partner_order' => 1,
        ]);

        $partners = SiteSetting::getSupportingPartners();
        $this->assertCount(6, $partners);
        $this->assertEquals('Prerana Seva Foundation', $partners[0]['name']);

        $addedPartner = $partners[0];

        // 9. Rename Partner & 11. Reorder
        $partnersPayload = [];
        foreach ($partners as $p) {
            $partnersPayload[$p['id']] = [
                'name'               => $p['id'] === $addedPartner['id'] ? 'Prerana Global Seva' : $p['name'],
                'existing_logo_path' => $p['logo_path'],
                'order'              => $p['id'] === $addedPartner['id'] ? 99 : $p['order'],
            ];
        }

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'partners' => $partnersPayload,
        ]);

        $updatedPartners = SiteSetting::getSupportingPartners();
        $renamed = collect($updatedPartners)->firstWhere('id', $addedPartner['id']);
        $this->assertNotNull($renamed);
        $this->assertEquals('Prerana Global Seva', $renamed['name']);
        $this->assertEquals(6, $renamed['order']); // Normalized to last position

        // 10. Remove Partner
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'partners'           => $partnersPayload,
            'remove_partner_ids' => [$addedPartner['id']],
        ]);

        $finalPartners = SiteSetting::getSupportingPartners();
        $this->assertCount(5, $finalPartners);
        $this->assertNull(collect($finalPartners)->firstWhere('id', $addedPartner['id']));
    }

    // =========================================================================
    // 12–24. LOGO UPLOADS, VALIDATION, REPLACEMENT, AND REMOVAL
    // =========================================================================

    public function test_logo_uploads_validation_replacement_and_removal(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        // 12. Upload PNG
        $pngLogo = UploadedFile::fake()->image('partner1.png', 120, 60)->size(500);
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Tech Partner PNG',
            'new_partner_logo' => $pngLogo,
        ]);

        $partners = SiteSetting::getSupportingPartners();
        $pngPartner = collect($partners)->firstWhere('name', 'Tech Partner PNG');
        $this->assertNotNull($pngPartner);
        $this->assertNotNull($pngPartner['logo_path']);
        Storage::disk('public')->assertExists($pngPartner['logo_path']);

        // 13. Upload JPG
        $jpgLogo = UploadedFile::fake()->image('partner2.jpg', 120, 60)->size(500);
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Tech Partner JPG',
            'new_partner_logo' => $jpgLogo,
        ]);

        // 14. Upload JPEG
        $jpegLogo = UploadedFile::fake()->image('partner3.jpeg', 120, 60)->size(500);
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Tech Partner JPEG',
            'new_partner_logo' => $jpegLogo,
        ]);

        // 15. Upload WEBP
        $webpLogo = UploadedFile::fake()->image('partner4.webp', 120, 60)->size(500);
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Tech Partner WEBP',
            'new_partner_logo' => $webpLogo,
        ]);

        // 16. Invalid mime rejected (.txt)
        $invalidFile = UploadedFile::fake()->create('hacked.txt', 100, 'text/plain');
        $invalidResponse = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Hacker Org',
            'new_partner_logo' => $invalidFile,
        ]);
        $invalidResponse->assertSessionHasErrors('new_partner_logo');

        // 17. Oversized file (>2MB) rejected
        $oversizedFile = UploadedFile::fake()->image('giant.png', 4000, 4000)->size(3000);
        $oversizedResponse = $this->actingAs($admin)->post(route('admin.settings.update'), [
            'new_partner_name' => 'Giant Org',
            'new_partner_logo' => $oversizedFile,
        ]);
        $oversizedResponse->assertSessionHasErrors('new_partner_logo');

        // 18, 19, 20. Logo appears publicly with constrained dimensions and partner name
        $publicResponse = $this->get('/');
        $publicResponse->assertSee($pngPartner['logo_path'], false);
        $publicResponse->assertSee('Tech Partner PNG', false);
        $publicResponse->assertSee('h-7 sm:h-8', false);
        $publicResponse->assertSee('object-contain', false);

        // 22. Logo Replacement removes old managed file
        $oldLogoPath = $pngPartner['logo_path'];
        $replacementLogo = UploadedFile::fake()->image('partner1_new.png', 150, 75)->size(400);

        $allPartners = SiteSetting::getSupportingPartners();
        $partnersPayload = [];
        foreach ($allPartners as $p) {
            $partnersPayload[$p['id']] = [
                'name'               => $p['name'],
                'existing_logo_path' => $p['logo_path'],
                'order'              => $p['order'],
            ];
        }

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'partners'      => $partnersPayload,
            'partner_logos' => [
                $pngPartner['id'] => $replacementLogo,
            ],
        ]);

        Storage::disk('public')->assertMissing($oldLogoPath);
        $updatedPngPartner = collect(SiteSetting::getSupportingPartners())->firstWhere('id', $pngPartner['id']);
        $this->assertNotEquals($oldLogoPath, $updatedPngPartner['logo_path']);
        Storage::disk('public')->assertExists($updatedPngPartner['logo_path']);

        // 23. Logo Removal preserves partner and removes file from disk
        $currentLogoPath = $updatedPngPartner['logo_path'];
        $allPartners = SiteSetting::getSupportingPartners();
        $partnersPayload = [];
        foreach ($allPartners as $p) {
            $partnersPayload[$p['id']] = [
                'name'               => $p['name'],
                'existing_logo_path' => $p['logo_path'],
                'order'              => $p['order'],
            ];
        }

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'partners'        => $partnersPayload,
            'remove_logo_ids' => [$pngPartner['id']],
        ]);

        Storage::disk('public')->assertMissing($currentLogoPath);
        $textOnlyPartner = collect(SiteSetting::getSupportingPartners())->firstWhere('id', $pngPartner['id']);
        $this->assertNotNull($textOnlyPartner);
        $this->assertNull($textOnlyPartner['logo_path']);

        // 24. Partner removal deletes managed logo safely
        $webpPartner = collect(SiteSetting::getSupportingPartners())->firstWhere('name', 'Tech Partner WEBP');
        $this->assertNotNull($webpPartner);
        $webpLogoPath = $webpPartner['logo_path'];
        Storage::disk('public')->assertExists($webpLogoPath);

        $allPartners = SiteSetting::getSupportingPartners();
        $partnersPayload = [];
        foreach ($allPartners as $p) {
            $partnersPayload[$p['id']] = [
                'name'               => $p['name'],
                'existing_logo_path' => $p['logo_path'],
                'order'              => $p['order'],
            ];
        }

        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'partners'           => $partnersPayload,
            'remove_partner_ids' => [$webpPartner['id']],
        ]);

        Storage::disk('public')->assertMissing($webpLogoPath);
    }

    // =========================================================================
    // 25–27. AUTHORIZATION
    // =========================================================================

    public function test_authorization_for_partner_settings(): void
    {
        // 25. Guest rejected
        $guestResponse = $this->post(route('admin.settings.update'), [
            'new_partner_name' => 'Unauthorized Guest Org',
        ]);
        $guestResponse->assertRedirect(route('login'));

        // 26. Volunteer rejected
        $volunteer = $this->createVolunteer();
        $volunteerResponse = $this->actingAs($volunteer, 'volunteer')->post(route('admin.settings.update'), [
            'new_partner_name' => 'Unauthorized Volunteer Org',
        ]);
        $volunteerResponse->assertRedirect(route('login'));

        // 27. Admin allowed
        $admin = $this->createAdmin();
        $adminResponse = $this->actingAs($admin, 'web')->post(route('admin.settings.update'), [
            'new_partner_name' => 'Authorized Admin Org',
        ]);
        $adminResponse->assertRedirect(route('admin.settings.index'));
    }

    // =========================================================================
    // 28–34. DISPLAY TOGGLES, ORDERING, AND ACCESSIBILITY
    // =========================================================================

    public function test_enable_disable_ordering_and_accessibility(): void
    {
        $admin = $this->createAdmin();

        // 28. Disable
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'homepage_sponsors_enabled' => '0',
        ]);
        $disabledResponse = $this->get('/');
        $disabledResponse->assertDontSee('homepage-sponsors-strip', false);

        // 29. Re-enable
        $this->actingAs($admin)->post(route('admin.settings.update'), [
            'homepage_sponsors_enabled' => '1',
            'homepage_sponsors_heading' => 'OUR CORPORATE SUPPORTERS',
        ]);
        $enabledResponse = $this->get('/');
        $enabledResponse->assertSee('homepage-sponsors-strip', false);
        $enabledResponse->assertSee('OUR CORPORATE SUPPORTERS', false);

        // 31–34. Accessibility, continuous marquee & reduced motion
        $enabledResponse->assertSee('aria-label="OUR CORPORATE SUPPORTERS"', false);
        $enabledResponse->assertSee('aria-hidden="true"', false);
        $enabledResponse->assertSee('overflow-hidden', false);
        $enabledResponse->assertSee('prefers-reduced-motion', false);
        $enabledResponse->assertSee('sponsorMarquee', false);
    }

    // =========================================================================
    // 35. BACKWARD COMPATIBILITY
    // =========================================================================

    public function test_backward_compatibility_with_legacy_sponsors_list(): void
    {
        // Clear structured JSON and set legacy list
        SiteSetting::set('homepage_sponsors_structured', null);
        SiteSetting::set('homepage_sponsors_list', "Legacy Partner One\nLegacy Partner Two");

        $partners = SiteSetting::getSupportingPartners();
        $this->assertCount(2, $partners);
        $this->assertEquals('Legacy Partner One', $partners[0]['name']);
        $this->assertEquals('Legacy Partner Two', $partners[1]['name']);
        $this->assertNull($partners[0]['logo_path']);

        $response = $this->get('/');
        $response->assertSee('Legacy Partner One', false);
        $response->assertSee('Legacy Partner Two', false);
    }
}
