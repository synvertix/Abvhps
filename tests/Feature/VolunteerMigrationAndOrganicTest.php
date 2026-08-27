<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Membership;
use App\Models\Volunteer;
use App\Models\RudrasenaMember;

class VolunteerMigrationAndOrganicTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Officer',
            'email' => 'admin@abvhps.org',
            'password' => bcrypt('AdminPassword123'),
        ]);
    }

    /**
     * TEST 1: Srinivasa Rao is assigned a unique randomized 6-digit ID (not 100001),
     * has default password 'password' hashed, and must change password on first login.
     */
    public function test_srinivasa_rao_has_randomized_six_digit_id_and_default_password(): void
    {
        $member = Membership::create([
            'membership_id' => '583742916405',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'full_name' => 'SRINIVASA RAO',
            'country' => 'India',
            'state' => 'Andhra Pradesh',
            'district' => 'Kadapa',
            'is_completed' => 1
        ]);

        $volunteer = Volunteer::create([
            'membership_id' => $member->membership_id,
            'phone' => $member->phone,
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR999888',
            'email' => 'srinivas@abvhps.org',
            'bank_name' => 'SBI',
            'account_holder_name' => 'Srinivasa Rao',
            'account_number' => '9988776655',
            'ifsc_code' => 'SBIN0001',
            'branch_name' => 'Porumamilla',
            'nominee_name' => 'Nominee',
            'nominee_relation' => 'Father',
            'nominee_phone' => '9876543210',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path' => 'doc2.pdf',
            'document_bank_path' => 'doc3.pdf',
            'status' => 'pending',
        ]);

        // Admin approves volunteer
        $this->actingAs($this->admin)->post(route('admin.volunteers.cadreUpdate', $volunteer->id), [
            'status' => 'Verified',
            'cadre' => 'District Coordinator',
            'locality' => 'Kadapa District'
        ]);

        $volunteer->refresh();

        // Check ID format
        $this->assertNotEquals('100001', $volunteer->volunteer_id, "Volunteer ID must not be legacy 100001");
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $volunteer->volunteer_id);
        // Check secure random password and must_change_password flag
        $this->assertNotEmpty($volunteer->password);
        $this->assertTrue((bool)$volunteer->must_change_password, "must_change_password must be true initially");

        // Set a known temporary password to test first login and forced password change workflow
        $tempPassword = 'TempPassword123';
        $volunteer->update(['password' => Hash::make($tempPassword)]);

        // Volunteer logs in with temporary password
        $loginRes = $this->post(route('volunteer.login.submit'), [
            'volunteer_id' => $volunteer->volunteer_id,
            'password' => $tempPassword,
        ]);

        // First login must redirect to change password
        $loginRes->assertRedirect(route('volunteer.change_password'));
        $this->assertAuthenticatedAs($volunteer, 'volunteer');

        // Cannot access dashboard before changing password
        $dashRes = $this->get(route('volunteer.dashboard'));
        $dashRes->assertRedirect(route('volunteer.change_password'));

        // Change password
        $changeRes = $this->post(route('volunteer.change_password.submit'), [
            'current_password' => $tempPassword,
            'new_password' => 'SecureNewPass123',
            'new_password_confirmation' => 'SecureNewPass123',
        ]);
        $changeRes->assertRedirect(route('volunteer.dashboard'));

        $volunteer->refresh();
        $this->assertFalse((bool)$volunteer->must_change_password);
        $this->assertTrue(Hash::check('SecureNewPass123', $volunteer->password));

        // Public QR Verification URL works dynamically
        $verifyRes = $this->get(route('verify.volunteer', $volunteer->volunteer_id));
        $verifyRes->assertStatus(200);
        $verifyRes->assertSee('SRINIVASA RAO');
        $verifyRes->assertSee($volunteer->volunteer_id);
        $verifyRes->assertDontSee('password');
    }

    /**
     * TEST 2: Rudra Sena RS0001 is completely untouched.
     */
    public function test_rudrasena_rs0001_remains_isolated_and_untouched(): void
    {
        $member = Membership::create([
            'membership_id' => '583742916406',
            'phone' => '9876543211',
            'payment_status' => 'success',
            'full_name' => 'SRINIVASA RAO',
            'country' => 'India',
            'state' => 'Andhra Pradesh',
            'district' => 'Kadapa',
            'is_completed' => 1
        ]);

        $rudrasena = RudrasenaMember::create([
            'membership_id' => $member->membership_id,
            'full_name' => $member->full_name,
            'email' => 'rudra_srinivas@abvhps.org',
            'mobile' => $member->phone,
            'dob' => '1995-05-15',
            'age' => 30,
            'blood_group' => 'O+',
            'gotram' => 'Kashyapa',
            'nominee_name' => 'Nominee',
            'nominee_relation' => 'Mother',
            'nominee_age' => 55,
            'nominee_contact' => '9876543210',
            'bank_holder_name' => 'Srinivasa Rao',
            'bank_account_number' => '11223344',
            'bank_ifsc_code' => 'SBIN0001',
            'bank_name_branch' => 'Porumamilla',
            'document_health_declaration' => 'doc1.pdf',
            'document_family_declaration' => 'doc2.pdf',
            'document_id_proof' => 'doc3.pdf',
            'document_bank_proof' => 'doc4.pdf',
            'rudrasena_id' => 'RS0001',
            'assigned_cadder' => 'Dharma Rakshak',
            'assigned_locality' => 'Kadapa Zone',
            'status' => 'verified',
            'disclaimer_accepted' => true,
        ]);

        $this->assertEquals('RS0001', $rudrasena->rudrasena_id);

        $res = $this->get(route('verify.rudrasena', 'RS0001'));
        $res->assertStatus(200);
        $res->assertSee('RS0001');
        $res->assertSee('SRINIVASA RAO');
    }

    /**
     * TEST 3: Organic Farmers option is visible in navigation and functional.
     */
    public function test_organic_farmers_visible_in_ui_and_functional(): void
    {
        $homeRes = $this->get(url('/'));
        $homeRes->assertStatus(200);
        $homeRes->assertSee('ORGANIC FARMERS');

        $formRes = $this->get(route('organicfarmers.form'));
        $formRes->assertStatus(200);
        $formRes->assertSee('ORGANIC FARMERS REGISTRY DESK');
    }
}
