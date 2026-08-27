<?php

namespace Tests\Feature;

use App\Mail\DonationReceiptMail;
use App\Mail\MembershipWelcomeMail;
use App\Mail\VolunteerApplicationReceivedMail;
use App\Mail\VolunteerAssignmentUpdatedMail;
use App\Mail\VolunteerPendingStatusMail;
use App\Mail\VolunteerRejectedStatusMail;
use App\Mail\VolunteerWelcomeMail;
use App\Models\Donation;
use App\Models\Membership;
use App\Models\NotificationLog;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OfficialAutomatedEmailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to create an active test membership.
     */
    private function createMember(array $overrides = []): Membership
    {
        return Membership::create(array_merge([
            'membership_id'            => '123456789012',
            'phone'                    => '9876543210',
            'email'                    => 'member@example.com',
            'full_name'                => 'Devotee Kumar',
            'identity_verified_name'   => 'Devotee Kumar',
            'payment_status'           => 'success',
            'is_completed'             => true,
            'blood_group'              => 'O+',
            'district'                 => 'Kadapa',
            'state'                    => 'Andhra Pradesh',
            'country'                  => 'India',
        ], $overrides));
    }

    /**
     * Helper to create a test volunteer.
     */
    private function createVolunteer(array $overrides = []): Volunteer
    {
        return Volunteer::create(array_merge([
            'membership_id'             => '123456789012',
            'phone'                     => '9876543210',
            'email'                     => 'volunteer@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'bank_name'                 => 'State Bank of India',
            'account_holder_name'       => 'Devotee Kumar',
            'account_number'            => '1234567890',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Main Branch',
            'nominee_name'              => 'Nominee Person',
            'nominee_relation'          => 'Brother',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'volunteer_docs/decl.jpg',
            'document_voter_path'       => 'volunteer_docs/voter.jpg',
            'document_bank_path'        => 'volunteer_docs/bank.jpg',
            'status'                    => 'pending',
            'is_active'                 => true,
        ], $overrides));
    }

    // =========================================================================
    // SENDER POLICY, HEADERS, & FOOTER
    // =========================================================================

    public function test_all_mailables_have_no_reply_from_and_no_reply_to(): void
    {
        $mailables = [
            new MembershipWelcomeMail(['full_name' => 'John Doe', 'membership_id' => '123456789012'], '%PDF-test'),
            new VolunteerApplicationReceivedMail(['volunteer_name' => 'Jane Doe', 'membership_id' => '123456789012']),
            new VolunteerPendingStatusMail(['volunteer_name' => 'Jane Doe', 'membership_id' => '123456789012']),
            new VolunteerRejectedStatusMail(['volunteer_name' => 'Jane Doe', 'membership_id' => '123456789012']),
            new VolunteerWelcomeMail(['volunteer_name' => 'Jane Doe', 'volunteer_id' => '100001', 'volunteer_login_id' => '100001'], '%PDF-test'),
            new VolunteerAssignmentUpdatedMail(['volunteer_name' => 'Jane Doe', 'volunteer_id' => '100001', 'volunteer_login_id' => '100001']),
            new DonationReceiptMail(['donor_name' => 'Ravi Kumar', 'receipt_number' => 'REC1001', 'amount' => 500], '%PDF-test'),
        ];

        foreach ($mailables as $mailable) {
            $envelope = $mailable->envelope();
            if (!empty($envelope->from)) {
                $this->assertEquals('no-reply@abvhps.org', $envelope->from->address);
                $this->assertEquals('ABVHPS', $envelope->from->name ?? config('mail.from.name'));
            } else {
                $this->assertEquals('no-reply@abvhps.org', config('mail.from.address'));
                $this->assertEquals('ABVHPS', config('mail.from.name'));
            }
            $this->assertEmpty($envelope->replyTo, 'Mailable ' . get_class($mailable) . ' must have NO Reply-To header configured');
        }
    }

    public function test_rendered_email_views_contain_common_footer_and_info_email(): void
    {
        $views = [
            'emails.membership_welcome' => ['memberData' => ['full_name' => 'Devotee', 'membership_id' => '123456789012']],
            'emails.volunteer_application_received' => ['volunteerData' => ['volunteer_name' => 'Devotee', 'membership_id' => '123456789012']],
            'emails.volunteer_pending_status' => ['volunteerData' => ['volunteer_name' => 'Devotee', 'membership_id' => '123456789012']],
            'emails.volunteer_rejected_status' => ['volunteerData' => ['volunteer_name' => 'Devotee', 'membership_id' => '123456789012']],
            'emails.volunteer_welcome' => ['volunteerData' => ['volunteer_name' => 'Devotee', 'volunteer_id' => '100001', 'volunteer_login_id' => '100001', 'temporary_password' => 'pass123']],
            'emails.volunteer_assignment_updated' => ['volunteerData' => ['volunteer_name' => 'Devotee', 'volunteer_id' => '100001', 'volunteer_login_id' => '100001']],
            'emails.donation_confirmation' => ['donationData' => ['donor_name' => 'Devotee', 'receipt_number' => 'REC001', 'amount' => 500]],
        ];

        foreach ($views as $viewName => $data) {
            $html = view($viewName, $data)->render();
            $this->assertStringContainsString('This is an automated notification from', $html);
            $this->assertStringContainsString('Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS)', $html);
            $this->assertStringContainsString('Replies to this email address are not monitored or answered.', $html);
            $this->assertStringContainsString('info@abvhps.org', $html);
            $this->assertStringContainsString('https://abvhps.org', $html);
        }
    }

    // =========================================================================
    // MEMBERSHIP EMAIL FLOW & IDEMPOTENCY
    // =========================================================================

    public function test_membership_welcome_claim_prevents_duplicate_sends(): void
    {
        Mail::fake();
        $member = $this->createMember();

        $key = "membership_welcome:{$member->id}";
        $claim1 = NotificationLog::claim($key, 'membership_welcome', Membership::class, $member->id, 'email', $member->email);
        $this->assertNotNull($claim1);
        $claim1->markSent('Welcome Subject', 'Welcome Message');

        // Second simultaneous claim must return null
        $claim2 = NotificationLog::claim($key, 'membership_welcome', Membership::class, $member->id, 'email', $member->email);
        $this->assertNull($claim2);
    }

    public function test_membership_smtp_failure_leaves_welcome_email_sent_at_null_and_is_retryable(): void
    {
        $member = $this->createMember(['welcome_email_sent_at' => null]);
        $key = "membership_welcome:{$member->id}";

        // Simulate failed claim attempt
        $claim = NotificationLog::claim($key, 'membership_welcome', Membership::class, $member->id, 'email', $member->email);
        $this->assertNotNull($claim);
        $claim->markFailed('Connection refused by SMTP server');

        $member->refresh();
        $this->assertNull($member->welcome_email_sent_at);
        $this->assertTrue($member->is_completed);

        // A retry claim must now succeed
        $retryClaim = NotificationLog::claim($key, 'membership_welcome', Membership::class, $member->id, 'email', $member->email);
        $this->assertNotNull($retryClaim);
        $this->assertEquals('pending', $retryClaim->status);
    }

    // =========================================================================
    // DONATION EMAIL FLOW & IDEMPOTENCY
    // =========================================================================

    public function test_donation_duplicate_callbacks_cannot_send_twice(): void
    {
        $donation = Donation::create([
            'receipt_number' => 'REC_TEST_001',
            'name'           => 'Ravi Varma',
            'email'          => 'donor@example.com',
            'phone'          => '9888877777',
            'contact'        => '9888877777',
            'amount'         => 1000,
            'payment_status' => 'paid',
        ]);

        $key = "donation_paid:{$donation->id}";
        $claim1 = NotificationLog::claim($key, 'donation_paid', Donation::class, $donation->id, 'email', $donation->email);
        $this->assertNotNull($claim1);
        $claim1->markSent('Receipt Subject', 'Receipt Message');

        // Repeated callback attempt
        $claim2 = NotificationLog::claim($key, 'donation_paid', Donation::class, $donation->id, 'email', $donation->email);
        $this->assertNull($claim2);
    }

    public function test_donation_failed_mail_leaves_payment_paid_and_is_retryable(): void
    {
        $donation = Donation::create([
            'receipt_number' => 'REC_TEST_002',
            'name'           => 'Ravi Varma',
            'email'          => 'donor@example.com',
            'phone'          => '9888877777',
            'contact'        => '9888877777',
            'amount'         => 500,
            'payment_status' => 'paid',
        ]);

        $key = "donation_paid:{$donation->id}";
        $claim = NotificationLog::claim($key, 'donation_paid', Donation::class, $donation->id, 'email', $donation->email);
        $claim->markFailed('SMTP timeout');

        $donation->refresh();
        $this->assertEquals('paid', $donation->payment_status);

        // Retry claim succeeds
        $retryClaim = NotificationLog::claim($key, 'donation_paid', Donation::class, $donation->id, 'email', $donation->email);
        $this->assertNotNull($retryClaim);
    }

    // =========================================================================
    // VOLUNTEER APPLICATION RECEIVED FLOW
    // =========================================================================

    public function test_volunteer_application_received_duplicate_claim_sends_once(): void
    {
        $volunteer = $this->createVolunteer();
        $key = "volunteer_application_received:{$volunteer->id}";

        $claim1 = NotificationLog::claim($key, 'volunteer_application_received', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNotNull($claim1);
        $claim1->markSent('Application Received', 'Application received');

        $claim2 = NotificationLog::claim($key, 'volunteer_application_received', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNull($claim2);
    }

    public function test_volunteer_application_mail_failure_is_retryable(): void
    {
        $volunteer = $this->createVolunteer();
        $key = "volunteer_application_received:{$volunteer->id}";

        $claim = NotificationLog::claim($key, 'volunteer_application_received', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $claim->markFailed('Transport exception');

        $this->assertDatabaseHas('volunteers', ['id' => $volunteer->id]);

        $retryClaim = NotificationLog::claim($key, 'volunteer_application_received', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNotNull($retryClaim);
    }

    // =========================================================================
    // VOLUNTEER APPROVAL, WELCOME, & PASSWORDS
    // =========================================================================

    public function test_volunteer_approval_generates_strong_random_password_and_sets_welcome_flags(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer(['membership_id' => $member->membership_id]);

        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status'      => 'approved',
            'cadre_level' => 'volunteer',
            'locality'    => 'Kadapa HQ',
        ]);

        $volunteer->refresh();
        $this->assertEquals('approved', $volunteer->status);
        $this->assertNotNull($volunteer->volunteer_id);
        $this->assertNotNull($volunteer->welcome_email_sent_at);
        $this->assertTrue((bool)$volunteer->must_change_password);

        // Verify password is NOT literal 'password'
        $this->assertFalse(Hash::check('password', $volunteer->password));

        // Verify welcome mail was sent
        Mail::assertSent(VolunteerWelcomeMail::class, function ($mail) use ($volunteer) {
            return $mail->hasTo($volunteer->email);
        });
    }

    public function test_volunteer_approval_smtp_failure_leaves_welcome_sent_at_null(): void
    {
        Mail::shouldReceive('to')->andThrow(new \Exception('SMTP Out of Service'));

        $member = $this->createMember();
        $volunteer = $this->createVolunteer(['membership_id' => $member->membership_id]);

        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status'      => 'approved',
            'cadre_level' => 'volunteer',
            'locality'    => 'Kadapa HQ',
        ]);

        $volunteer->refresh();
        // Volunteer remains successfully approved
        $this->assertEquals('approved', $volunteer->status);
        $this->assertNotNull($volunteer->volunteer_id);
        // But welcome_email_sent_at remains NULL
        $this->assertNull($volunteer->welcome_email_sent_at);
    }

    public function test_admin_resend_credentials_generates_new_secure_password(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer([
            'membership_id' => $member->membership_id,
            'status'        => 'approved',
            'volunteer_id'  => '100001',
            'password'      => Hash::make('old_secret_pwd'),
        ]);

        $this->withoutMiddleware()->post(route('admin.volunteers.resendCredentials', $volunteer->id));

        $volunteer->refresh();
        $this->assertNotNull($volunteer->welcome_email_sent_at);
        $this->assertTrue((bool)$volunteer->must_change_password);
        $this->assertFalse(Hash::check('password', $volunteer->password));
        $this->assertFalse(Hash::check('old_secret_pwd', $volunteer->password));

        Mail::assertSent(VolunteerWelcomeMail::class);
    }

    // =========================================================================
    // STATUS TRANSITIONS & ASSIGNMENT UPDATES
    // =========================================================================

    public function test_pending_to_pending_sends_no_email(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer(['membership_id' => $member->membership_id, 'status' => 'pending']);

        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status' => 'pending',
        ]);

        Mail::assertNothingSent();
    }

    public function test_pending_to_rejected_sends_one_rejected_email(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer(['membership_id' => $member->membership_id, 'status' => 'pending']);

        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status' => 'rejected',
        ]);

        Mail::assertSent(VolunteerRejectedStatusMail::class, 1);
    }

    public function test_rejected_to_pending_sends_one_pending_email(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer(['membership_id' => $member->membership_id, 'status' => 'rejected']);

        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status' => 'pending',
        ]);

        Mail::assertSent(VolunteerPendingStatusMail::class, 1);
    }

    public function test_approved_volunteer_save_with_no_assignment_change_sends_no_email(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer([
            'membership_id'         => $member->membership_id,
            'status'                => 'approved',
            'volunteer_id'          => '100001',
            'volunteer_login_id'    => '100001',
            'cadre_level'           => 'volunteer',
            'state_id'              => null,
            'district_id'           => null,
            'assembly_segment_id'   => null,
            'mandal_id'             => null,
            'panchayat_id'          => null,
            'welcome_email_sent_at' => now(),
        ]);

        // Save with exact same assignment
        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status'      => 'approved',
            'cadre_level' => 'volunteer',
        ]);

        Mail::assertNothingSent();
    }

    public function test_real_assignment_change_sends_assignment_updated_email_without_password(): void
    {
        Mail::fake();
        $member = $this->createMember();
        $volunteer = $this->createVolunteer([
            'membership_id'         => $member->membership_id,
            'status'                => 'approved',
            'volunteer_id'          => '100001',
            'volunteer_login_id'    => '100001',
            'cadre_level'           => 'volunteer',
            'locality'              => 'Old Base',
            'welcome_email_sent_at' => now(),
        ]);

        $state = \App\Models\GeoState::create([
            'country'   => 'India',
            'name'      => 'Andhra Pradesh',
            'code'      => 'AP',
            'is_active' => true,
        ]);

        // Reassign to state_president
        $this->withoutMiddleware()->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status'      => 'approved',
            'cadre_level' => 'state_president',
            'state_id'    => $state->id,
        ]);

        Mail::assertSent(VolunteerAssignmentUpdatedMail::class, 1);
    }

    // =========================================================================
    // UI RESTRICTIONS & ROUTE LOCKS
    // =========================================================================

    public function test_public_join_cta_is_locked_to_membership_form_route(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee(route('membership.form'), false);
    }

    public function test_public_login_selector_not_rendered_in_authenticated_admin_view(): void
    {
        $admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@abvhps.org',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('openLoginModal', false);
        $response->assertDontSee('Select Login Portal', false);
    }

    // =========================================================================
    // ATOMIC IDEMPOTENCY & STALE CLAIM RECOVERY TESTS
    // =========================================================================

    public function test_first_idempotency_claim_succeeds_and_duplicate_claim_is_blocked(): void
    {
        $key = 'test_claim_key_001';
        $claim1 = NotificationLog::claim($key, 'test_event', Membership::class, 999, 'email', 'test@example.com');
        $this->assertNotNull($claim1);
        $this->assertEquals('pending', $claim1->status);

        // Immediate second claim with same key returns null
        $claim2 = NotificationLog::claim($key, 'test_event', Membership::class, 999, 'email', 'test@example.com');
        $this->assertNull($claim2);
    }

    public function test_recent_pending_claim_cannot_be_reclaimed(): void
    {
        $key = 'test_recent_pending_key';
        $claim = NotificationLog::claim($key, 'test_event', Membership::class, 888, 'email', 'recent@example.com');
        $this->assertNotNull($claim);

        // Updated 2 minutes ago (recent, within 5-minute timeout window)
        $claim->updated_at = now()->subMinutes(2);
        $claim->save();

        $reclaimAttempt = NotificationLog::claim($key, 'test_event', Membership::class, 888, 'email', 'recent@example.com');
        $this->assertNull($reclaimAttempt);
    }

    public function test_stale_pending_claim_can_be_reclaimed_once(): void
    {
        $key = 'test_stale_pending_key';
        $claim = NotificationLog::claim($key, 'test_event', Membership::class, 777, 'email', 'stale@example.com');
        $this->assertNotNull($claim);

        // Set updated_at to 10 minutes ago (> 5 minutes threshold)
        NotificationLog::where('id', $claim->id)->update(['updated_at' => now()->subMinutes(10)]);

        // First reclaim succeeds
        $reclaimed = NotificationLog::claim($key, 'test_event', Membership::class, 777, 'email', 'stale@example.com');
        $this->assertNotNull($reclaimed);
        $this->assertEquals('pending', $reclaimed->status);

        // Immediate subsequent reclaim is now recent and returns null
        $subsequentReclaim = NotificationLog::claim($key, 'test_event', Membership::class, 777, 'email', 'stale@example.com');
        $this->assertNull($subsequentReclaim);
    }

    public function test_two_simultaneous_stale_reclaim_attempts_cannot_both_succeed(): void
    {
        $key = 'test_atomic_stale_race_key';
        $claim = NotificationLog::claim($key, 'test_event', Membership::class, 666, 'email', 'race@example.com');
        $this->assertNotNull($claim);

        // Set record to stale (> 5 minutes)
        $staleTime = now()->subMinutes(10);
        NotificationLog::where('id', $claim->id)->update(['updated_at' => $staleTime]);

        // Process 1 acquires reclaim
        $process1 = NotificationLog::claim($key, 'test_event', Membership::class, 666, 'email', 'race@example.com');
        $this->assertNotNull($process1);

        // Process 2 attempting to reclaim immediately gets null because Process 1 updated the timestamp
        $process2 = NotificationLog::claim($key, 'test_event', Membership::class, 666, 'email', 'race@example.com');
        $this->assertNull($process2);
    }

    public function test_sent_and_logged_claims_cannot_be_reclaimed_even_if_old(): void
    {
        $keySent = 'test_sent_key';
        $claimSent = NotificationLog::claim($keySent, 'test_event', Membership::class, 555, 'email', 'sent@example.com');
        $this->assertNotNull($claimSent);
        $claimSent->markSent('Sent Subject', 'Sent Message');

        // Artificially make it old
        NotificationLog::where('id', $claimSent->id)->update(['updated_at' => now()->subDays(10)]);

        $reclaimSent = NotificationLog::claim($keySent, 'test_event', Membership::class, 555, 'email', 'sent@example.com');
        $this->assertNull($reclaimSent);
    }

    public function test_multiple_different_assignment_update_idempotency_keys_for_same_volunteer_are_allowed(): void
    {
        $volunteer = $this->createVolunteer(['status' => 'approved']);

        // 1st assignment change
        $key1 = "volunteer_assignment_updated:{$volunteer->id}:hash_state_president_ap";
        $claim1 = NotificationLog::claim($key1, 'volunteer_assignment_updated', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNotNull($claim1);
        $claim1->markSent('Assignment 1', 'Assigned to State President');

        // 2nd assignment change for SAME volunteer with DIFFERENT assignment hash/key
        $key2 = "volunteer_assignment_updated:{$volunteer->id}:hash_district_president_kadapa";
        $claim2 = NotificationLog::claim($key2, 'volunteer_assignment_updated', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNotNull($claim2, 'Multiple legitimate event occurrences for same entity must NOT be blocked by obsolete composite unique index');
        $claim2->markSent('Assignment 2', 'Assigned to District President');

        // Same key duplicate must still be blocked
        $claimDuplicate = NotificationLog::claim($key1, 'volunteer_assignment_updated', Volunteer::class, $volunteer->id, 'email', $volunteer->email);
        $this->assertNull($claimDuplicate);
    }

    public function test_historical_null_idempotency_key_records_remain_valid(): void
    {
        // Insert legacy rows without idempotency_key
        $legacy1 = NotificationLog::create([
            'idempotency_key'  => null,
            'event_type'       => 'legacy_event',
            'notifiable_type'  => Membership::class,
            'notifiable_id'    => 101,
            'channel'          => 'email',
            'recipient_email'  => 'legacy1@example.com',
            'status'           => 'sent',
        ]);

        $legacy2 = NotificationLog::create([
            'idempotency_key'  => null,
            'event_type'       => 'legacy_event',
            'notifiable_type'  => Membership::class,
            'notifiable_id'    => 102,
            'channel'          => 'email',
            'recipient_email'  => 'legacy2@example.com',
            'status'           => 'sent',
        ]);

        $this->assertNull($legacy1->idempotency_key);
        $this->assertNull($legacy2->idempotency_key);
        $this->assertEquals(2, NotificationLog::where('event_type', 'legacy_event')->count());
    }

    public function test_migration_is_safe_and_idempotent_on_populated_table(): void
    {
        // Populate notification log with records
        NotificationLog::create([
            'idempotency_key' => 'mig_test_key_1',
            'event_type'      => 'mig_event',
            'notifiable_type' => Membership::class,
            'notifiable_id'   => 201,
            'channel'         => 'email',
            'status'          => 'sent',
        ]);

        // Re-run migration up() to prove retry safety on populated table
        $migration = require database_path('migrations/2026_08_27_000001_add_idempotency_key_to_notification_logs.php');
        $migration->up();

        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('notification_logs', 'idempotency_key'));
        $this->assertEquals(1, NotificationLog::where('idempotency_key', 'mig_test_key_1')->count());

        $indexes = \Illuminate\Support\Facades\Schema::getIndexes('notification_logs');
        
        // 1. Unique index on idempotency_key exists
        $idempotencyIdx = collect($indexes)->firstWhere('name', 'notification_logs_idempotency_key_unique');
        $this->assertNotNull($idempotencyIdx);
        $this->assertTrue($idempotencyIdx['unique']);
        $this->assertEquals(['idempotency_key'], $idempotencyIdx['columns']);

        // 2. Old composite unique index does NOT exist as unique
        $oldIdx = collect($indexes)->firstWhere('name', 'notif_logs_idempotency_idx');
        $this->assertTrue($oldIdx === null || !$oldIdx['unique']);

        // 3. Lookup non-unique composite index exists
        $lookupIdx = collect($indexes)->firstWhere('name', 'notif_logs_lookup_idx');
        $this->assertNotNull($lookupIdx);
        $this->assertFalse($lookupIdx['unique']);
        $this->assertEquals(['event_type', 'notifiable_type', 'notifiable_id', 'channel'], $lookupIdx['columns']);
    }

    public function test_generic_database_query_exception_is_not_treated_as_duplicate_collision(): void
    {
        // Construct a generic QueryException that is NOT a unique violation (e.g. syntax error code 42000)
        $genericException = new \Illuminate\Database\QueryException(
            'mysql',
            'SELECT * FROM non_existent_table',
            [],
            new \Exception('Table not found', 1146)
        );

        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($genericException));

        // System Exception
        $runtimeException = new \RuntimeException('Database connection timed out');
        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($runtimeException));
    }

    public function test_true_unique_constraint_violation_is_detected(): void
    {
        // 1. MySQL: SQLSTATE 23000, driver code 1062, Duplicate entry => TRUE
        $pdo1062 = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry \'key_123\' for key \'notification_logs_idempotency_key_unique\'', 1062);
        $pdo1062->errorInfo = ['23000', 1062, 'Duplicate entry \'key_123\' for key \'notification_logs_idempotency_key_unique\''];
        $mysqlDuplicate = new \Illuminate\Database\QueryException('mysql', 'INSERT INTO notification_logs...', [], $pdo1062);
        $this->assertTrue(NotificationLog::isUniqueConstraintViolation($mysqlDuplicate));

        // 2. MySQL: SQLSTATE 23000, driver code 1452, foreign-key violation => FALSE
        $pdo1452 = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails', 1452);
        $pdo1452->errorInfo = ['23000', 1452, 'Cannot add or update a child row: a foreign key constraint fails'];
        $mysqlForeignKey = new \Illuminate\Database\QueryException('mysql', 'INSERT INTO notification_logs...', [], $pdo1452);
        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($mysqlForeignKey));

        // 3. MySQL: SQLSTATE 23000, driver code 1048, NOT NULL violation => FALSE
        $pdo1048 = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 1048 Column \'event_type\' cannot be null', 1048);
        $pdo1048->errorInfo = ['23000', 1048, 'Column \'event_type\' cannot be null'];
        $mysqlNotNull = new \Illuminate\Database\QueryException('mysql', 'INSERT INTO notification_logs...', [], $pdo1048);
        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($mysqlNotNull));

        // 4. SQLite: error code 19, "UNIQUE constraint failed" => TRUE
        $pdoSqliteUnique = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: notification_logs.idempotency_key', 19);
        $pdoSqliteUnique->errorInfo = ['23000', 19, 'UNIQUE constraint failed: notification_logs.idempotency_key'];
        $sqliteUnique = new \Illuminate\Database\QueryException('sqlite', 'INSERT INTO notification_logs...', [], $pdoSqliteUnique);
        $this->assertTrue(NotificationLog::isUniqueConstraintViolation($sqliteUnique));

        // 5. SQLite: error code 19, "FOREIGN KEY constraint failed" => FALSE
        $pdoSqliteFk = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed', 19);
        $pdoSqliteFk->errorInfo = ['23000', 19, 'FOREIGN KEY constraint failed'];
        $sqliteFk = new \Illuminate\Database\QueryException('sqlite', 'INSERT INTO notification_logs...', [], $pdoSqliteFk);
        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($sqliteFk));

        // 6. SQLite: error code 19, "NOT NULL constraint failed" => FALSE
        $pdoSqliteNotNull = new \PDOException('SQLSTATE[23000]: Integrity constraint violation: 19 NOT NULL constraint failed: notification_logs.event_type', 19);
        $pdoSqliteNotNull->errorInfo = ['23000', 19, 'NOT NULL constraint failed: notification_logs.event_type'];
        $sqliteNotNull = new \Illuminate\Database\QueryException('sqlite', 'INSERT INTO notification_logs...', [], $pdoSqliteNotNull);
        $this->assertFalse(NotificationLog::isUniqueConstraintViolation($sqliteNotNull));

        // 7. PostgreSQL: SQLSTATE 23505 => TRUE
        $pdoPg = new \PDOException('SQLSTATE[23505]: Unique violation: 7 ERROR: duplicate key value violates unique constraint "notification_logs_idempotency_key_unique"', 7);
        $pdoPg->errorInfo = ['23505', 7, 'ERROR: duplicate key value violates unique constraint'];
        $pgUnique = new \Illuminate\Database\QueryException('pgsql', 'INSERT INTO notification_logs...', [], $pdoPg);
        $this->assertTrue(NotificationLog::isUniqueConstraintViolation($pgUnique));

        // 8. Native Laravel UniqueConstraintViolationException => TRUE
        $laravelViolation = new \Illuminate\Database\UniqueConstraintViolationException(
            'mysql',
            'INSERT INTO notification_logs...',
            [],
            $pdo1062
        );
        $this->assertTrue(NotificationLog::isUniqueConstraintViolation($laravelViolation));
    }
}
