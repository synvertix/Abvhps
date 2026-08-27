<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Membership;
use App\Models\SiteSetting;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomepageLoginAndStatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Homepage contains LOGIN button and modal with Admin and Volunteer login routes.
     */
    public function test_homepage_contains_login_modal_and_correct_routes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('openLoginModal', false);
        $response->assertSee('Select Login Portal', false);
        $response->assertSee('Admin Login', false);
        $response->assertSee('LOGIN AS ADMIN', false);
        $response->assertSee(route('login'), false);
        $response->assertSee('Volunteer Login', false);
        $response->assertSee('LOGIN AS VOLUNTEER', false);
        $response->assertSee(route('volunteer.login'), false);
    }

    /**
     * Test: Homepage sections are ordered correctly:
     * Vision/Mission/Goal -> Statistics Strip -> Fundraising Campaigns -> Our Core Service Projects
     */
    public function test_homepage_sections_ordered_properly(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        $posVision = strpos($content, 'Our Vision');
        $posStats = strpos($content, 'homepage-statistics-strip');
        $posFundraising = strpos($content, 'Fundraising Campaigns');
        $posProjects = strpos($content, 'Our Core Service Projects');

        $this->assertNotFalse($posVision, 'Vision section not found');
        $this->assertNotFalse($posStats, 'Statistics strip not found');
        $this->assertNotFalse($posFundraising, 'Fundraising Campaigns section not found');
        $this->assertNotFalse($posProjects, 'Our Core Service Projects section not found');

        $this->assertTrue($posVision < $posStats, 'Vision must appear BEFORE statistics strip');
        $this->assertTrue($posStats < $posFundraising, 'Statistics strip must appear BEFORE Fundraising Campaigns');
        $this->assertTrue($posFundraising < $posProjects, 'Fundraising Campaigns must appear BEFORE Our Core Service Projects');
    }

    /**
     * Test: Homepage statistics counter calculates real qualifying counts and does not hardcode static data.
     */
    public function test_homepage_statistics_renders_dynamic_qualifying_counts(): void
    {
        Cache::flush();

        // Create completed members
        Membership::create([
            'membership_id' => '100000000001',
            'phone' => '9999990001',
            'payment_status' => 'success',
            'is_completed' => true,
        ]);
        Membership::create([
            'membership_id' => '100000000002',
            'phone' => '9999990002',
            'payment_status' => 'success',
            'is_completed' => true,
        ]);

        // Create approved volunteer
        Volunteer::create([
            'membership_id' => '100000000001',
            'phone' => '9999990001',
            'email' => 'volunteer1@example.com',
            'volunteer_id' => '100001',
            'qualification' => 'Graduate',
            'voter_id_number' => 'ABC1234567',
            'bank_name' => 'State Bank of India',
            'account_holder_name' => 'Member One',
            'account_number' => '1234567890',
            'ifsc_code' => 'SBIN0001234',
            'branch_name' => 'Main Branch',
            'nominee_name' => 'Nominee One',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9999990003',
            'document_declaration_path' => 'volunteer_docs/decl.jpg',
            'document_voter_path' => 'volunteer_docs/voter.jpg',
            'document_bank_path' => 'volunteer_docs/bank.jpg',
            'status' => 'approved',
            'is_active' => true,
        ]);

        // Create paid donations
        Donation::create([
            'receipt_number' => 'REC101',
            'name' => 'Donor Alpha',
            'phone' => '9999990001',
            'contact' => '9999990001',
            'amount' => 500,
            'payment_status' => 'paid',
        ]);
        Donation::create([
            'receipt_number' => 'REC102',
            'name' => 'Donor Beta',
            'phone' => '9999990002',
            'contact' => '9999990002',
            'amount' => 1000,
            'payment_status' => 'paid',
        ]);

        SiteSetting::set('organization_founded_year', 2020);

        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify counts passed to view
        $liveCounts = $response->viewData('liveCounts');
        $this->assertEquals(2, $liveCounts['members']);
        $this->assertEquals(1, $liveCounts['volunteers']);
        $this->assertEquals(2, $liveCounts['donors']);
        $this->assertGreaterThanOrEqual(1, $liveCounts['years']);
    }
}
