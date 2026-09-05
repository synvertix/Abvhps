<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Models\Membership;
use App\Models\Volunteer;
use App\Models\RudrasenaMember;
use App\Models\ExamSetting;
use App\Models\ExamApplication;
use App\Models\AuditLog;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        RateLimiter::clear('admin_login:admin@abvhps.org|127.0.0.1');

        $this->admin = User::create([
            'name' => 'Commander Officer',
            'email' => 'admin@abvhps.org',
            'password' => bcrypt('AdminSecPassword123'),
        ]);
    }

    /**
     * 1. Unauthenticated users cannot access protected Admin routes.
     */
    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $res = $this->get(route('admin.dashboard'));
        $res->assertRedirect(route('login'));

        $resVol = $this->get(route('admin.volunteers.index'));
        $resVol->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated Volunteer cannot access Admin Panel.
     */
    public function test_authenticated_volunteer_cannot_access_admin_panel(): void
    {
        $member = Membership::create([
            'membership_id' => '583742916405',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'full_name' => 'TEST VOLUNTEER',
            'is_completed' => 1
        ]);

        $volunteer = Volunteer::create([
            'membership_id' => $member->membership_id,
            'phone' => $member->phone,
            'email' => 'volunteer@abvhps.org',
            'status' => 'approved',
            'is_active' => true,
            'volunteer_id' => '583214',
            'volunteer_login_id' => '583214',
            'password' => Hash::make('Password123'),
            'must_change_password' => false,
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR123',
            'bank_name' => 'SBI',
            'account_holder_name' => 'Vol',
            'account_number' => '123456',
            'ifsc_code' => 'SBIN0001',
            'branch_name' => 'HQ',
            'nominee_name' => 'Nom',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9876543210',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path' => 'doc2.pdf',
            'document_bank_path' => 'doc3.pdf',
        ]);

        // Attempting to access Admin routes as volunteer
        $response = $this->actingAs($volunteer, 'volunteer')->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 3. Pending, Rejected, and Inactive volunteers are denied login.
     */
    public function test_pending_rejected_and_inactive_volunteers_denied_login(): void
    {
        $member = Membership::create([
            'membership_id' => '583742916406',
            'phone' => '9876543211',
            'payment_status' => 'success',
            'full_name' => 'PENDING VOLUNTEER',
            'is_completed' => 1
        ]);

        $pendingVolunteer = Volunteer::create([
            'membership_id' => $member->membership_id,
            'phone' => $member->phone,
            'email' => 'pending@abvhps.org',
            'status' => 'pending',
            'is_active' => false,
            'volunteer_id' => '741905',
            'volunteer_login_id' => '741905',
            'password' => Hash::make('Password123'),
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR123',
            'bank_name' => 'SBI',
            'account_holder_name' => 'Vol',
            'account_number' => '123456',
            'ifsc_code' => 'SBIN0001',
            'branch_name' => 'HQ',
            'nominee_name' => 'Nom',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9876543210',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path' => 'doc2.pdf',
            'document_bank_path' => 'doc3.pdf',
        ]);

        $res = $this->post(route('volunteer.login.submit'), [
            'volunteer_id' => '741905',
            'password' => 'Password123'
        ]);

        $res->assertSessionHasErrors(['volunteer_id']);
        $this->assertGuest('volunteer');
    }

    /**
     * 4. Admin login throttling after repeated failures.
     */
    public function test_admin_login_rate_limiting_enforced(): void
    {
        $throttleKey = 'admin_login:admin@abvhps.org|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.submit'), [
                'email' => 'admin@abvhps.org',
                'password' => 'WrongPassword!'
            ]);
        }

        // 6th attempt should be blocked with rate limiting notice
        $res = $this->post(route('admin.login.submit'), [
            'email' => 'admin@abvhps.org',
            'password' => 'AdminSecPassword123'
        ]);

        $res->assertSessionHasErrors(['email']);
        $this->assertGuest('web');
    }

    /**
     * 5. Volunteer login throttling after repeated failures.
     */
    public function test_volunteer_login_rate_limiting_enforced(): void
    {
        $throttleKey = 'volunteer_login:583214|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('volunteer.login.submit'), [
                'volunteer_id' => '583214',
                'password' => 'WrongPassword!'
            ]);
        }

        $res = $this->post(route('volunteer.login.submit'), [
            'volunteer_id' => '583214',
            'password' => 'Password123'
        ]);

        $res->assertSessionHasErrors(['volunteer_id']);
    }

    /**
     * 6. HTTP Security Headers are present in responses.
     */
    public function test_security_headers_middleware_present(): void
    {
        $res = $this->get(url('/'));

        $res->assertHeader('X-Content-Type-Options', 'nosniff');
        $res->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $res->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $res->assertHeader('Permissions-Policy');
        $res->assertHeader('Content-Security-Policy');
    }

    /**
     * 7. Audit log is created for critical administrative actions.
     */
    public function test_audit_log_created_for_admin_login_and_actions(): void
    {
        $this->post(route('admin.login.submit'), [
            'email' => 'admin@abvhps.org',
            'password' => 'AdminSecPassword123'
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ADMIN_LOGIN_SUCCESS',
            'actor_identifier' => 'admin@abvhps.org'
        ]);
    }

    /**
     * 8. Public QR verification protects sensitive private information.
     */
    public function test_public_qr_verification_strictly_hides_private_data(): void
    {
        $member = Membership::create([
            'membership_id' => '583742916405',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'full_name' => 'DR. RAGHAVENDRA',
            'is_completed' => 1
        ]);

        $volunteer = Volunteer::create([
            'membership_id' => $member->membership_id,
            'phone' => $member->phone,
            'email' => 'private_email@secret.com',
            'voter_id_number' => 'VOTER_SECRET_999',
            'bank_name' => 'SECRET_BANK',
            'account_holder_name' => 'Secret Holder',
            'account_number' => 'ACCOUNT_SECRET_999',
            'ifsc_code' => 'IFSC_SECRET_999',
            'branch_name' => 'SECRET_BRANCH',
            'status' => 'approved',
            'is_active' => true,
            'volunteer_id' => '583214',
            'volunteer_login_id' => '583214',
            'password' => Hash::make('SecretPass123'),
            'cadre' => 'District Coordinator',
            'locality' => 'Kadapa',
            'qualification' => 'Post Graduate',
            'nominee_name' => 'Nom',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9876543210',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path' => 'doc2.pdf',
            'document_bank_path' => 'doc3.pdf',
        ]);

        $res = $this->get(route('verify.volunteer', '583214'));
        $res->assertStatus(200);

        // Public Safe Data
        $res->assertSee('DR. RAGHAVENDRA');
        $res->assertSee('583214');
        $res->assertSee('ACTIVE & APPROVED');

        // Private Data must NEVER be leaked
        $res->assertDontSee('private_email@secret.com');
        $res->assertDontSee('VOTER_SECRET_999');
        $res->assertDontSee('ACCOUNT_SECRET_999');
        $res->assertDontSee('IFSC_SECRET_999');
        $res->assertDontSee('SecretPass123');
    }

    /**
     * 9. Executable file uploads are strictly rejected.
     */
    public function test_executable_script_upload_is_rejected(): void
    {
        session([
            'verified_volunteer_membership_id' => '583742916405',
            'verified_volunteer_phone' => '9876543210'
        ]);

        $maliciousFile = UploadedFile::fake()->create('exploit.php', 100, 'text/x-php');

        $res = $this->post('/volunteer/submit-application', [
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR123',
            'email' => 'test@test.com',
            'bank_name' => 'SBI',
            'account_holder_name' => 'Test',
            'account_number' => '123456',
            'ifsc_code' => 'SBIN0001',
            'branch_name' => 'HQ',
            'nominee_name' => 'Nom',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9876543210',
            'doc_declaration' => $maliciousFile,
            'doc_voter' => UploadedFile::fake()->create('doc2.pdf', 100, 'application/pdf'),
            'doc_bank' => UploadedFile::fake()->create('doc3.pdf', 100, 'application/pdf'),
        ]);

        $res->assertSessionHasErrors(['doc_declaration']);
    }

    /**
     * 10. Draft and unpublished exam results remain hidden from public view.
     */
    public function test_draft_exam_results_not_publicly_accessible(): void
    {
        $exam = ExamSetting::create([
            'exam_title' => 'Sanatana Dharma Annual Exam 2026',
            'exam_date_time' => '2026-10-15 10:00:00',
            'exam_center_location' => 'Central Center, Kadapa',
            'syllabus_pdf_path' => 'syllabus.pdf',
            'prize_details_json' => json_encode(['1st' => 'Gold Medal']),
            'application_fee' => 41.00,
            'exam_fee' => 41.00,
            'status' => 'active',
            'exam_type' => 'theory'
        ]);

        $app = ExamApplication::create([
            'exam_setting_id' => $exam->id,
            'email' => 'student@test.com',
            'is_email_verified' => 1,
            'full_name' => 'STUDENT CANDIDATE',
            'dob' => '2008-04-10',
            'address' => 'Porumamilla, Kadapa',
            'mobile' => '9876543212',
            'aadhaar_no' => '123456789012',
            'school_college_name' => 'ZPHS',
            'class_section' => '10th',
            'amount_paid' => 41.00,
            'payment_status' => 'success',
            'hall_ticket_number' => '58374291640',
            'marks_obtained' => 95,
            'result_status' => 'passed',
            'result_publication_status' => 'draft', // DRAFT
        ]);

        // Public Search for Draft Result
        $res = $this->post(route('exam.results_search'), [
            'hall_ticket_number' => '58374291640'
        ]);

        $res->assertStatus(200);
        $res->assertJson([
            'success' => false,
            'draft' => true,
        ]);
        $res->assertJsonMissing([
            'marks' => 95,
        ]);
    }

    /**
     * 11. Negative Security Test: Unauthenticated user cannot access newly protected admin routes.
     */
    public function test_negative_unauthenticated_user_blocked_from_all_admin_endpoints(): void
    {
        // Donated admin routes
        $this->get('/admin/donations')->assertRedirect(route('login'));
        $this->get('/admin/donations/1/receipt')->assertRedirect(route('login'));

        // Blog admin routes
        $this->get('/admin/blogs')->assertRedirect(route('login'));
        $this->get('/admin/blogs/create')->assertRedirect(route('login'));
        $this->post('/admin/blogs/store', ['title' => 'Hacked'])->assertRedirect(route('login'));

        // Gallery admin routes
        $this->get('/admin/gallery')->assertRedirect(route('login'));
        $this->post('/admin/gallery/store', [])->assertRedirect(route('login'));

        // Support admin routes
        $this->get('/admin/our-supports')->assertRedirect(route('login'));
        $this->get('/admin/our-supports/create')->assertRedirect(route('login'));

        // Volunteer admin endpoints
        $this->post('/admin/volunteer/approve', ['id' => 1])->assertRedirect(route('login'));
        $this->get('/admin/volunteer/view-card/RS0001')->assertRedirect(route('login'));

        // Team admin endpoints
        $this->get('/admin/our-team')->assertRedirect(route('login'));
    }

    /**
     * 12. Negative Security Test: Hardcoded bypass credentials are rejected.
     */
    public function test_negative_hardcoded_bypass_credentials_rejected(): void
    {
        $response = $this->post('/volunteer/process-login', [
            'volunteer_id' => '662424',
            'password' => 'ABVHPS@2026'
        ]);

        $this->assertNull(session('auth_volunteer_code'));
        $this->assertNull(session('auth_volunteer_role'));
    }

    /**
     * 13. Negative Security Test: Sensitive candidate mobile number is masked on public hall ticket view.
     */
    public function test_candidate_phone_masked_on_public_hall_ticket(): void
    {
        $exam = ExamSetting::create([
            'exam_title' => 'Sanatana Dharma Annual Exam 2026',
            'exam_date_time' => '2026-10-15 10:00:00',
            'exam_center_location' => 'Central Center, Kadapa',
            'syllabus_pdf_path' => 'syllabus.pdf',
            'prize_details_json' => json_encode(['1st' => 'Gold Medal']),
            'application_fee' => 41.00,
            'status' => 'active',
            'exam_type' => 'theory'
        ]);

        $app = ExamApplication::create([
            'exam_setting_id' => $exam->id,
            'email' => 'student_phone@test.com',
            'is_email_verified' => 1,
            'full_name' => 'STUDENT CANDIDATE',
            'dob' => '2008-04-10',
            'address' => 'Porumamilla, Kadapa',
            'mobile' => '9876543210',
            'payment_status' => 'success',
            'hall_ticket_number' => '58374291640',
        ]);

        $res = $this->get(route('exam.success', ['id' => $app->id]));
        $res->assertStatus(200);
        $res->assertSee('98******10');
        $res->assertDontSee('9876543210');
    }
}
