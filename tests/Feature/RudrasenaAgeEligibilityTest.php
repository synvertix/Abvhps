<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Membership;
use App\Models\RudrasenaMember;
use App\Services\RudrasenaEligibilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RudrasenaAgeEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $member;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Freeze time to a deterministic point for all boundary testing
        // Date: 2026-08-27
        Carbon::setTestNow(Carbon::create(2026, 8, 27, 12, 0, 0));

        $this->admin = User::create([
            'name' => 'ADMIN TEST',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789')
        ]);

        $this->member = Membership::create([
            'membership_id' => '915123456789',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'payment_id' => 'TXN-RUDRASENA-AGE',
            'aadhaar_number' => '987654321098',
            'full_name' => 'AGE TEST MEMBER',
            'father_or_husband_name' => 'Father Name',
            'gotram' => 'Siva Gotram',
            'occupation' => 'Business',
            'blood_group' => 'O+',
            'email' => 'age_member@test.com',
            'pincode' => '516193',
            'grama_panchayat' => 'Porumamilla',
            'mandal' => 'Porumamilla',
            'assembly_segment' => 'Badvel',
            'district' => 'Kadapa',
            'state' => 'Andhra Pradesh',
            'country' => 'India',
            'dob' => '1995-08-15',
            'is_completed' => 1
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow(); // Reset frozen time
        parent::tearDown();
    }

    /**
     * 1. Exactly 24 years old => PASS
     * As of 2026-08-27, born on 2002-08-27 is exactly 24 years old.
     */
    public function test_exactly_24_years_old_passes_eligibility(): void
    {
        $dob = '2002-08-27';
        $this->assertEquals(24, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertTrue(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 2. One day before 24th birthday => FAIL
     * As of 2026-08-27, born on 2002-08-28 is 23 years, 364 days old.
     */
    public function test_one_day_before_24th_birthday_fails_eligibility(): void
    {
        $dob = '2002-08-28';
        $this->assertEquals(23, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 3. Exactly 44 years old => PASS
     * As of 2026-08-27, born on 1982-08-27 is exactly 44 years old.
     */
    public function test_exactly_44_years_old_passes_eligibility(): void
    {
        $dob = '1982-08-27';
        $this->assertEquals(44, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertTrue(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 4. One day before 45th birthday => PASS
     * As of 2026-08-27, born on 1981-08-28 is 44 years, 364 days old.
     */
    public function test_one_day_before_45th_birthday_passes_eligibility(): void
    {
        $dob = '1981-08-28';
        $this->assertEquals(44, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertTrue(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 5. Exactly 45 years old => FAIL
     * As of 2026-08-27, born on 1981-08-27 is exactly 45 years old.
     */
    public function test_exactly_45_years_old_fails_eligibility(): void
    {
        $dob = '1981-08-27';
        $this->assertEquals(45, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 6. Under 24 => FAIL
     */
    public function test_under_24_years_old_fails(): void
    {
        $dob = '2005-01-01'; // 21 years old
        $this->assertEquals(21, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 7. Above 44 => FAIL
     */
    public function test_above_44_years_old_fails(): void
    {
        $dob = '1975-01-01'; // 51 years old
        $this->assertEquals(51, RudrasenaEligibilityService::calculateAge($dob));
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible($dob));
    }

    /**
     * 8. Future DOB => FAIL
     */
    public function test_future_dob_fails(): void
    {
        $dob = '2028-01-01';
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible($dob));
        $this->expectException(\InvalidArgumentException::class);
        RudrasenaEligibilityService::calculateAge($dob);
    }

    /**
     * 9. Invalid DOB => FAIL
     */
    public function test_invalid_dob_fails(): void
    {
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible('not-a-date'));
    }

    /**
     * 10. Missing DOB => throws Exception or fails
     */
    public function test_missing_dob_fails(): void
    {
        $this->assertFalse(RudrasenaEligibilityService::isAgeEligible(''));
        $this->expectException(\InvalidArgumentException::class);
        RudrasenaEligibilityService::calculateAge('');
    }

    /**
     * 11. Leap-day DOB (Feb 29) calculated accurately.
     * Born on 2000-02-29, on 2026-08-27 is 26 years old => PASS.
     */
    public function test_leap_day_dob_calculated_accurately(): void
    {
        $dobLeap2000 = '2000-02-29';
        $this->assertEquals(26, RudrasenaEligibilityService::calculateAge($dobLeap2000));
        $this->assertTrue(RudrasenaEligibilityService::isAgeEligible($dobLeap2000));

        $dobLeap2004 = '2004-02-29';
        // Born on 2004-02-29:
        // As of 2028-02-28, exactly 1 day before 24th birthday -> age is 23
        // As of 2028-02-29, 24th birthday -> age is 24
        $this->assertEquals(23, RudrasenaEligibilityService::calculateAge($dobLeap2004, Carbon::create(2028, 2, 28)));
        $this->assertEquals(24, RudrasenaEligibilityService::calculateAge($dobLeap2004, Carbon::create(2028, 2, 29)));
    }

    /**
     * 12. Verification endpoint checks DOB from Membership table (24-44).
     */
    public function test_verify_membership_endpoint_enforces_24_to_44(): void
    {
        // 1. Member with 31 years old (born 1995-08-15) -> PASS
        $this->member->update(['dob' => '1995-08-15']);
        $resPass = $this->postJson(route('rudrasena.verify_member'), [
            'membership_id' => $this->member->membership_id
        ]);
        $resPass->assertStatus(200);
        $resPass->assertJson(['success' => true]);

        // 2. Member with 20 years old (born 2006-08-15) -> FAIL
        $this->member->update(['dob' => '2006-08-15']);
        $resYoung = $this->postJson(route('rudrasena.verify_member'), [
            'membership_id' => $this->member->membership_id
        ]);
        $resYoung->assertStatus(200);
        $resYoung->assertJson([
            'success' => false,
            'message' => 'Rudrasena eligibility is limited to persons aged 24 to 44 years. Your current calculated age is 20.'
        ]);

        // 3. Member with 46 years old (born 1980-08-15) -> FAIL
        $this->member->update(['dob' => '1980-08-15']);
        $resOld = $this->postJson(route('rudrasena.verify_member'), [
            'membership_id' => $this->member->membership_id
        ]);
        $resOld->assertStatus(200);
        $resOld->assertJson([
            'success' => false,
            'message' => 'Rudrasena eligibility is limited to persons aged 24 to 44 years. Your current calculated age is 46.'
        ]);
    }

    /**
     * 13. Public registration submission verifies server-side DOB and rejects ineligible age even if client sends spoofed age.
     */
    public function test_submit_registration_rejects_ineligible_dob_and_ignores_spoofed_client_age(): void
    {
        $payload = [
            'membership_id' => $this->member->membership_id,
            'full_name' => 'AGE TEST MEMBER',
            'email' => 'age_member@test.com',
            'mobile' => '9876543210',
            'volunteer_type' => 'Emergency Response',
            'dob' => '2006-05-15', // 20 years old (Ineligible)
            'age' => 30, // Client spoofed age!
            'blood_group' => 'O+',
            'gotram' => 'Siva Gotram',
            'nominee_name' => 'Nominee Lakshmi',
            'nominee_relation' => 'Mother',
            'nominee_age' => 55,
            'nominee_contact' => '9876543211',
            'bank_holder_name' => 'Age Test Member',
            'bank_account_number' => '123456789012',
            'bank_ifsc_code' => 'SBIN0001234',
            'bank_name_branch' => 'SBI Porumamilla',
            'document_health_declaration' => UploadedFile::fake()->create('health.jpg', 100, 'image/jpeg'),
            'document_family_declaration' => UploadedFile::fake()->create('family.jpg', 100, 'image/jpeg'),
            'document_id_proof' => UploadedFile::fake()->create('id.jpg', 100, 'image/jpeg'),
            'document_bank_proof' => UploadedFile::fake()->create('bank.jpg', 100, 'image/jpeg'),
            'disclaimer_accepted' => true,
        ];

        $response = $this->postJson(route('rudrasena.submit'), $payload);
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Rudrasena eligibility is limited to persons aged 24 to 44 years. Your current calculated age is 20.'
        ]);

        $this->assertDatabaseMissing('rudrasena_members', [
            'membership_id' => $this->member->membership_id
        ]);
    }

    /**
     * 14. Admin Approval recalculates current age and rejects approval if applicant turned 45.
     */
    public function test_admin_approval_recalculates_current_age_at_approval_time(): void
    {
        // Created when age was 44 (born 1981-08-28, on 2026-08-26 age was 44)
        $rudra = RudrasenaMember::create([
            'membership_id' => '915111222333',
            'full_name' => 'TURNING 45 MEMBER',
            'email' => 'turning45@test.com',
            'mobile' => '9876543201',
            'volunteer_type' => 'Full-Time',
            'dob' => '1981-08-26', // Born Aug 26, 1981. On Aug 27, 2026 => 45 years old!
            'age' => 44, // Registered yesterday at 44
            'nominee_name' => 'Nominee 1',
            'nominee_relation' => 'Mother',
            'nominee_age' => 55,
            'nominee_contact' => '9876543211',
            'bank_holder_name' => 'User 1',
            'bank_account_number' => '1234567890',
            'bank_ifsc_code' => 'SBIN0001234',
            'bank_name_branch' => 'SBI Porumamilla',
            'document_health_declaration' => 'doc1.jpg',
            'document_family_declaration' => 'doc2.jpg',
            'document_id_proof' => 'doc3.jpg',
            'document_bank_proof' => 'doc4.jpg',
            'status' => 'pending'
        ]);

        // Attempt admin approval today when applicant is now 45
        $res = $this->actingAs($this->admin)->post(route('admin.rudrasena.update', $rudra->id), [
            'status' => 'Verified',
            'assigned_cadder' => 'Commander',
            'assigned_locality' => 'Porumamilla'
        ]);

        $res->assertSessionHasErrors('status');
        $rudra->refresh();
        $this->assertEquals('pending', $rudra->status);
        $this->assertNull($rudra->rudrasena_id);
    }

    /**
     * 15. Regression confirmation: Out-of-range historical records are preserved and NOT deleted.
     */
    public function test_existing_out_of_range_records_are_not_deleted(): void
    {
        $historical = RudrasenaMember::create([
            'membership_id' => '915999888777',
            'full_name' => 'HISTORICAL ELDER MEMBER',
            'email' => 'elder@test.com',
            'mobile' => '9876543209',
            'volunteer_type' => 'Full-Time',
            'dob' => '1970-01-01', // 56 years old
            'age' => 56,
            'nominee_name' => 'Nominee',
            'nominee_relation' => 'Son',
            'nominee_age' => 30,
            'nominee_contact' => '9876543219',
            'bank_holder_name' => 'Elder User',
            'bank_account_number' => '1234567899',
            'bank_ifsc_code' => 'SBIN0001234',
            'bank_name_branch' => 'SBI HQ',
            'document_health_declaration' => 'doc1.jpg',
            'document_family_declaration' => 'doc2.jpg',
            'document_id_proof' => 'doc3.jpg',
            'document_bank_proof' => 'doc4.jpg',
            'rudrasena_id' => 'RS0099',
            'status' => 'verified'
        ]);

        $this->assertDatabaseHas('rudrasena_members', [
            'id' => $historical->id,
            'rudrasena_id' => 'RS0099',
            'status' => 'verified'
        ]);

        // Accessing roster does not delete or alter record
        $response = $this->actingAs($this->admin)->get(route('admin.rudrasena.index'));
        $response->assertStatus(200);

        $this->assertDatabaseHas('rudrasena_members', [
            'id' => $historical->id,
            'rudrasena_id' => 'RS0099',
            'status' => 'verified'
        ]);
    }
}
