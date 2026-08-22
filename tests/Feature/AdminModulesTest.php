<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ContactMessage;
use App\Models\TaxCertificate;
use App\Models\SiteSetting;
use App\Models\ExamSetting;
use App\Models\FundraisingCampaign;
use App\Models\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::create([
            'name' => 'ADMIN TEST',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789')
        ]);
    }

    /**
     * Module 10: Local GP Gateways
     */
    public function test_admin_can_access_local_gp_gateways_and_approve_group(): void
    {
        $kbId = DB::table('kala_brundams')->insertGetId([
            'team_registration_id' => 'ABVHPS-KB-999',
            'team_name' => 'SRI RAMA BHAJANA MANDALI',
            'team_type' => 'Bhajana',
            'location' => 'Porumamilla GP',
            'status' => 'pending',
            'disclaimer_accepted' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('kala_brundam_members')->insert([
            'kala_brundam_id' => $kbId,
            'membership_id' => '915000111222',
            'full_name' => 'Lead Singer',
            'age' => 32,
            'mobile' => '9876543210',
            'is_verified' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.local_gateways.index'));
        $response->assertStatus(200);
        $response->assertSee('SRI RAMA BHAJANA MANDALI');
        $response->assertSee('ABVHPS-KB-999');

        // Approve Group
        $approveRes = $this->actingAs($this->admin)->post(route('admin.local_gateways.approve', ['wing' => 'kala_brundam', 'id' => $kbId]));
        $approveRes->assertRedirect();

        $updated = DB::table('kala_brundams')->where('id', $kbId)->first();
        $this->assertEquals('approved', $updated->status);

        // View Roster
        $viewRes = $this->actingAs($this->admin)->get(route('admin.local_gateways.view', ['wing' => 'kala_brundam', 'id' => $kbId]));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Lead Singer');
        $viewRes->assertSee('915000111222');
    }

    /**
     * Module 11: Exams Info Board (Continuous Loop)
     */
    public function test_admin_can_create_and_manage_exam_cycles(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.exams.store'), [
            'exam_title' => 'Sanathana Dharma Youth Exam Cycle 3',
            'exam_type' => 'mcq',
            'exam_date_time' => '2026-11-20 10:00:00',
            'exam_center_location' => 'Kadapa Central Hall',
            'application_fee' => 51.00,
            'status' => 'active',
            'prize_details' => "1st: Gold Trophy\n2nd: Silver Award"
        ]);
        $response->assertRedirect(route('admin.exams.index'));

        $this->assertDatabaseHas('exam_settings', [
            'exam_title' => 'Sanathana Dharma Youth Exam Cycle 3',
            'exam_center_location' => 'Kadapa Central Hall',
            'status' => 'active',
        ]);

        // Admin exams index load check with 0 applicants count
        $adminExamsRes = $this->actingAs($this->admin)->get(route('admin.exams.index'));
        $adminExamsRes->assertStatus(200);
        $adminExamsRes->assertSee('Sanathana Dharma Youth Exam Cycle 3');
        $adminExamsRes->assertSee('0 Applicants');

        // Public notice board
        $publicRes = $this->get(route('public.exams_board'));
        $publicRes->assertStatus(200);
        $publicRes->assertSee('Sanathana Dharma Youth Exam Cycle 3');
        $publicRes->assertSee('Kadapa Central Hall');
    }

    /**
     * Module 12: Fundraising Matrices
     */
    public function test_admin_can_manage_fundraising_matrices(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.fundraising.store'), [
            'title' => 'GOSHALA SEVA CAMPAIGN',
            'description' => 'Support feeding 200 cows in Kadapa ashram',
            'target_amount' => 300000,
            'raised_amount' => 50000,
            'end_date' => '2026-12-31',
            'cover_image' => UploadedFile::fake()->create('cover.jpg', 200, 'image/jpeg'),
        ]);
        $response->assertRedirect(route('admin.fundraising.index'));

        $camp = FundraisingCampaign::where('title', 'GOSHALA SEVA CAMPAIGN')->first();
        $this->assertNotNull($camp);
        $this->assertEquals(300000, (float)$camp->target_amount);
        $this->assertEquals('active', $camp->status);

        // Admin index
        $indexRes = $this->actingAs($this->admin)->get(route('admin.fundraising.index'));
        $indexRes->assertStatus(200);
        $indexRes->assertSee('GOSHALA SEVA CAMPAIGN');

        // Public donations grid
        $publicDonationsRes = $this->get(route('donations.grid'));
        $publicDonationsRes->assertStatus(200);
        $publicDonationsRes->assertSee('GOSHALA SEVA CAMPAIGN');

        // Toggle status
        $toggleRes = $this->actingAs($this->admin)->post(route('admin.fundraising.toggle', $camp->id));
        $toggleRes->assertRedirect();
        $camp->refresh();
        $this->assertEquals('expired', $camp->status);
    }

    /**
     * Module 13: Contact Forms Audit & Anti-Spam
     */
    public function test_contact_form_submission_filters_spam_links_and_accepts_valid_message(): void
    {
        // 1. Bot Spam with URL should be rejected
        $spamRes = $this->postJson(route('public.contact.submit'), [
            'name' => 'Spam Bot',
            'email' => 'spambot@example.com',
            'phone' => '9876543210',
            'subject' => 'Buy cheap links',
            'message' => 'Check our website https://spam-casino.xyz for discounts',
        ]);
        $spamRes->assertStatus(422);
        $spamRes->assertJson(['success' => false]);
        $this->assertDatabaseMissing('contact_messages', ['email' => 'spambot@example.com']);

        // 2. Clean Devotee message should be stored
        $validRes = $this->postJson(route('public.contact.submit'), [
            'name' => 'Devotee Sharma',
            'email' => 'sharma@example.com',
            'phone' => '9876543210',
            'subject' => 'Volunteering in Porumamilla',
            'message' => 'Namaste. I would like to join the Grama Seva Dal in our village.',
        ]);
        $validRes->assertStatus(200);
        $validRes->assertJson(['success' => true]);

        $message = ContactMessage::where('email', 'sharma@example.com')->first();
        $this->assertNotNull($message);
        $this->assertEquals('unread', $message->status);

        // 3. Admin inbox view
        $adminRes = $this->actingAs($this->admin)->get(route('admin.contacts.index'));
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Devotee Sharma');

        // 4. Admin single message view
        $viewRes = $this->actingAs($this->admin)->get(route('admin.contacts.view', $message->id));
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Volunteering in Porumamilla');
        $message->refresh();
        $this->assertEquals('read', $message->status);
    }

    /**
     * Module 14: Tax & Compliance Certificates
     */
    public function test_admin_can_upload_and_publish_tax_certificates(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.certificates.store'), [
            'title' => '80G Income Tax Exemption Order',
            'certificate_type' => 'Section 80G',
            'document_number' => 'AABTA1234F20241',
            'valid_from' => '2024-04-01',
            'valid_to' => '2029-03-31',
            'certificate_pdf' => UploadedFile::fake()->create('80g.pdf', 500, 'application/pdf'),
            'description' => 'Eligible for 50% tax deduction under Indian IT Act',
        ]);
        $response->assertRedirect(route('admin.certificates.index'));

        $cert = TaxCertificate::where('title', '80G Income Tax Exemption Order')->first();
        $this->assertNotNull($cert);
        $this->assertTrue($cert->is_active);

        // Public Compliance Page
        $publicRes = $this->get(route('public.certificates'));
        $publicRes->assertStatus(200);
        $publicRes->assertSee('80G Income Tax Exemption Order');
        $publicRes->assertSee('AABTA1234F20241');
    }

    /**
     * Module 15: Global Site Settings
     */
    public function test_admin_can_update_site_settings_and_reflect_site_wide(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.settings.update'), [
            'site_title' => 'ABVHPS CENTRAL PORTAL',
            'contact_phone' => '+91 9998887776',
            'contact_email' => 'support@abvhps.org',
            'contact_address' => 'ABVHPS Bhavan, Porumamilla, Kadapa',
            'facebook_url' => 'https://facebook.com/abvhps_official',
            'footer_about' => 'Custom updated footer description for tests.'
        ]);
        $response->assertRedirect(route('admin.settings.index'));

        $this->assertEquals('+91 9998887776', SiteSetting::get('contact_phone'));
        $this->assertEquals('support@abvhps.org', SiteSetting::get('contact_email'));

        // Public Home shows new contact phone
        $homeRes = $this->get(route('public.home'));
        $homeRes->assertStatus(200);
        $homeRes->assertSee('+91 9998887776');
        $homeRes->assertSee('support@abvhps.org');
    }

    /**
     * Module 16: Admin Devotee Totals Paid Filter
     */
    public function test_admin_fundraising_totals_include_paid_donations_and_exclude_pending_failed_cancelled(): void
    {
        Donation::create([
            'name'            => 'PAID DONOR 1',
            'contact'         => '9876543210',
            'amount'          => 1500.00,
            'payment_gateway' => 'cashfree',
            'payment_status'  => 'paid',
        ]);

        Donation::create([
            'name'            => 'PAID DONOR 2',
            'contact'         => '9876543211',
            'amount'          => 2500.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'paid',
        ]);

        Donation::create([
            'name'            => 'PENDING DONOR',
            'contact'         => '9876543212',
            'amount'          => 5000.00,
            'payment_gateway' => 'cashfree',
            'payment_status'  => 'pending',
        ]);

        Donation::create([
            'name'            => 'FAILED DONOR',
            'contact'         => '9876543213',
            'amount'          => 10000.00,
            'payment_gateway' => 'razorpay',
            'payment_status'  => 'failed',
        ]);

        Donation::create([
            'name'            => 'CANCELLED DONOR',
            'contact'         => '9876543214',
            'amount'          => 20000.00,
            'payment_gateway' => 'cashfree',
            'payment_status'  => 'cancelled',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.fundraising.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');

        $this->assertEquals(4000.00, (float) $stats['total_devotee_donations']);
        $this->assertEquals(2, $stats['donor_count']);
    }
}
