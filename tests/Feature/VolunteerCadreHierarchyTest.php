<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\GeoState;
use App\Models\GeoDistrict;
use App\Models\GeoAssemblySegment;
use App\Models\GeoMandal;
use App\Models\GeoPanchayat;
use App\Models\GeoAlias;
use App\Services\VolunteerCadreScopeService;
use App\Services\GeoHierarchyBackfillService;
use Database\Seeders\CanonicalGeoSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class VolunteerCadreHierarchyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected GeoState $state;
    protected GeoDistrict $district;
    protected GeoAssemblySegment $assembly;
    protected GeoMandal $mandal;
    protected GeoPanchayat $panchayat;
    protected static int $volCounter = 100;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'ADMIN TEST',
            'email' => 'admin@abvhps.org',
            'password' => Hash::make('password123'),
        ]);

        // Seed Canonical Masters
        $this->seed(CanonicalGeoSeeder::class);
        $this->state = GeoState::where('name', 'Andhra Pradesh')->first();
        $this->district = GeoDistrict::where('name', 'YSR Kadapa')->first();
        $this->assembly = GeoAssemblySegment::where('name', 'Badvel')->first();
        $this->mandal = GeoMandal::where('name', 'Porumamilla')->first();
        $this->panchayat = GeoPanchayat::where('name', 'Akkalareddypalli')->first();
    }

    protected function createVolunteer(array $overrides = []): Volunteer
    {
        self::$volCounter++;
        $cnt = self::$volCounter;

        $defaults = [
            'membership_id' => '100000000' . str_pad((string)$cnt, 3, '0', STR_PAD_LEFT),
            'phone' => '98765' . str_pad((string)$cnt, 5, '0', STR_PAD_LEFT),
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR' . $cnt . rand(100, 999),
            'email' => "volunteer{$cnt}@abvhps.org",
            'bank_name' => 'State Bank of India',
            'account_holder_name' => 'Volunteer Test',
            'account_number' => '123456789' . $cnt,
            'ifsc_code' => 'SBIN0001234',
            'branch_name' => 'Porumamilla',
            'nominee_name' => 'Nominee Test',
            'nominee_relation' => 'Mother',
            'nominee_phone' => '9876543210',
            'document_declaration_path' => 'doc_decl.pdf',
            'document_voter_path' => 'doc_voter.pdf',
            'document_bank_path' => 'doc_bank.pdf',
            'status' => 'approved',
            'is_active' => true,
        ];

        return Volunteer::create(array_merge($defaults, $overrides));
    }

    /** 1. CanonicalGeoSeeder Populates 5-Tier Hierarchy without Cuddapah alias */
    public function test_canonical_geo_seeder_populates_5_tier_hierarchy_without_cuddapah_alias()
    {
        $this->assertNotNull($this->state);
        $this->assertNotNull($this->district);
        $this->assertNotNull($this->assembly);
        $this->assertNotNull($this->mandal);
        $this->assertNotNull($this->panchayat);

        $this->assertEquals($this->state->id, $this->district->state_id);
        $this->assertEquals($this->district->id, $this->assembly->district_id);
        $this->assertEquals($this->district->id, $this->mandal->district_id);
        $this->assertEquals($this->assembly->id, $this->mandal->assembly_segment_id);
        $this->assertEquals($this->mandal->id, $this->panchayat->mandal_id);

        $cuddapahAlias = GeoAlias::where('alias_name', 'Cuddapah')->first();
        $this->assertNull($cuddapahAlias, 'Cuddapah alias must NOT be auto-seeded before admin review');
    }

    /** 2. Dry-Run Backfill Performs Zero Database Writes */
    public function test_dry_run_backfill_performs_zero_database_writes()
    {
        $membership = Membership::create([
            'membership_id' => '100000000001',
            'phone' => '9876500001',
            'full_name' => 'Legacy Member 1',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
            'assembly_segment' => 'Badvel',
            'mandal' => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddypalli',
            'payment_status' => 'success',
        ]);

        $volunteer = $this->createVolunteer([
            'membership_id' => $membership->membership_id,
            'volunteer_id' => '662424',
            'full_name' => 'Volunteer One',
            'cadre' => 'Village President',
            'role' => 'village_president',
        ]);

        $this->artisan('volunteer-cadre:backfill-geography --dry-run')
            ->assertExitCode(0);

        $volunteer->refresh();
        $membership->refresh();

        $this->assertNull($volunteer->state_id, 'Dry-run must not write state_id');
        $this->assertNull($volunteer->cadre_level, 'Dry-run must not write cadre_level');
        $this->assertNull($membership->state_id, 'Dry-run must not write state_id to membership');
    }

    /** 3. Backfill Identifies Cuddapah as Geographic Conflict Before Alias Approval */
    public function test_backfill_identifies_cuddapah_as_geographic_conflict_before_alias_approved()
    {
        $membership = Membership::create([
            'membership_id' => '100000000002',
            'phone' => '9876500002',
            'full_name' => 'Cuddapah Member',
            'state' => 'Andhra Pradesh',
            'district' => 'Cuddapah',
            'payment_status' => 'success',
        ]);

        $service = new GeoHierarchyBackfillService();
        $res = $service->evaluateRecord($membership, 'membership', true);

        $this->assertEquals('GEOGRAPHIC_CONFLICT', $res['classification']);
        $this->assertStringContainsString('Cuddapah', $res['reason']);
    }

    /** 4. Backfill Identifies Volunteer #2 as Cadre Conflict */
    public function test_backfill_identifies_volunteer_2_as_cadre_conflict()
    {
        $volunteer = $this->createVolunteer([
            'volunteer_id' => '773434',
            'full_name' => 'Volunteer Two',
            'cadre' => 'Mandal Presedient',
            'designation' => 'Mandal Presedient',
            'role' => 'village_president',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
        ]);

        $service = new GeoHierarchyBackfillService();
        $res = $service->evaluateRecord($volunteer, 'volunteer', true);

        $this->assertEquals('CADRE_CONFLICT', $res['classification']);
        $this->assertNull($res['proposed_cadre']);
    }

    /** 5. Backfill Maps Volunteer #1 to Panchayat President with Full 5 Tiers */
    public function test_backfill_maps_volunteer_1_to_panchayat_president()
    {
        $volunteer = $this->createVolunteer([
            'volunteer_id' => '662424',
            'full_name' => 'Volunteer One',
            'cadre' => 'Village President',
            'designation' => 'Village President',
            'role' => 'village_president',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
            'assembly_segment' => 'Badvel',
            'mandal' => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddypalli',
        ]);

        $service = new GeoHierarchyBackfillService();
        $res = $service->evaluateRecord($volunteer, 'volunteer', false);

        $this->assertEquals('WOULD_MATCH', $res['classification']);
        $this->assertEquals('panchayat_president', $res['proposed_cadre']);

        $volunteer->refresh();
        $this->assertEquals('panchayat_president', $volunteer->cadre_level);
        $this->assertEquals($this->panchayat->id, $volunteer->panchayat_id);
        $this->assertEquals('matched', $volunteer->geo_mapping_status);
    }

    /** 6. Force Backfill Persists Non-Conflicting Records Only, Leaving Conflicts Untouched */
    public function test_force_backfill_persists_non_conflicting_records_only()
    {
        $cleanMember = Membership::create([
            'membership_id' => '100000000005',
            'phone' => '9876500005',
            'full_name' => 'Clean Member',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
            'assembly_segment' => 'Badvel',
            'mandal' => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddypalli',
            'payment_status' => 'success',
        ]);

        $conflictMember = Membership::create([
            'membership_id' => '100000000006',
            'phone' => '9876500006',
            'full_name' => 'Conflict Member',
            'state' => 'Andhra Pradesh',
            'district' => 'Cuddapah',
            'payment_status' => 'success',
        ]);

        $conflictVolunteer = $this->createVolunteer([
            'volunteer_id' => '773434',
            'full_name' => 'Conflict Volunteer',
            'cadre' => 'Mandal Presedient',
            'role' => 'village_president',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
        ]);

        $this->artisan('volunteer-cadre:backfill-geography --force')
            ->assertExitCode(0);

        $cleanMember->refresh();
        $conflictMember->refresh();
        $conflictVolunteer->refresh();

        // Clean record got persisted
        $this->assertEquals($this->state->id, $cleanMember->state_id);
        $this->assertEquals($this->district->id, $cleanMember->district_id);
        $this->assertEquals('matched', $cleanMember->geo_mapping_status);

        // Conflict member remains unwritten/untouched
        $this->assertNull($conflictMember->district_id);
        $this->assertNull($conflictMember->state_id);
        $this->assertEquals('unmapped', $conflictMember->geo_mapping_status);

        // Conflict volunteer remains unwritten/untouched
        $this->assertNull($conflictVolunteer->cadre_level);
        $this->assertNull($conflictVolunteer->district_id);
        $this->assertEquals('unmapped', $conflictVolunteer->geo_mapping_status);
    }

    /** 7. Admin Approves Cuddapah Alias and Backfill Resolves District */
    public function test_admin_approves_cuddapah_alias_and_backfill_resolves_district()
    {
        $membership = Membership::create([
            'membership_id' => '100000000007',
            'phone' => '9876500007',
            'full_name' => 'Cuddapah Resident',
            'state' => 'Andhra Pradesh',
            'district' => 'Cuddapah',
            'payment_status' => 'success',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/geo-mapping-review/alias', [
            'entity_type' => 'district',
            'alias_name' => 'Cuddapah',
            'canonical_id' => $this->district->id,
            'state_id' => $this->state->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('geo_aliases', [
            'alias_name' => 'Cuddapah',
            'canonical_id' => $this->district->id,
        ]);

        $service = new GeoHierarchyBackfillService();
        $res = $service->evaluateRecord($membership, 'membership', false);

        $this->assertEquals('WOULD_PARTIAL_MATCH', $res['classification']);
        $membership->refresh();
        $this->assertEquals($this->district->id, $membership->district_id);
    }

    /** 8. Admin Resolves Volunteer #2 Cadre Conflict and Verifies */
    public function test_admin_resolves_volunteer_2_cadre_conflict_and_verifies()
    {
        $volunteer = $this->createVolunteer([
            'volunteer_id' => '773434',
            'full_name' => 'Volunteer Two Conflict',
            'cadre' => 'Mandal Presedient',
            'role' => 'village_president',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
            'mandal' => 'Porumamilla',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/geo-mapping-review/update/volunteer/{$volunteer->id}", [
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'confirm_verified' => 1,
        ]);

        $response->assertSessionHas('success');

        $volunteer->refresh();
        $this->assertEquals('mandal_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->mandal->id, $volunteer->mandal_id);
    }

    /** 9. Duplicate Active President Assignment is Prevented (Strict Active Check) */
    public function test_duplicate_active_president_assignment_is_prevented()
    {
        $vol1 = $this->createVolunteer([
            'volunteer_id' => '100001',
            'full_name' => 'Active President 1',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        $vol2 = $this->createVolunteer([
            'volunteer_id' => '100002',
            'full_name' => 'Aspiring President 2',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$vol2->id}", [
            'status' => 'approved',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
        ]);

        $response->assertSessionHasErrors('duplicate');
    }

    /** 10. Strict Assembly -> Mandal Parent Validation */
    public function test_strict_assembly_to_mandal_parent_validation()
    {
        $otherAssembly = GeoAssemblySegment::create([
            'district_id' => $this->district->id,
            'name' => 'Mydukur',
            'is_active' => true,
        ]);

        $orphanMandal = GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => null, // NULL assembly segment
            'name' => 'Orphan Mandal',
            'is_active' => true,
        ]);

        // 1. Assembly Badvel + Mandal with assembly_segment_id NULL => rejected
        $err1 = VolunteerCadreScopeService::validateParentChildGeography(
            $this->state->id,
            $this->district->id,
            $this->assembly->id,
            $orphanMandal->id,
            null
        );
        $this->assertNotNull($err1);
        $this->assertStringContainsString('Assembly Segment', $err1);

        // 2. Assembly Badvel + Mandal belonging to Mydukur assembly => rejected
        $mydukurMandal = GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => $otherAssembly->id,
            'name' => 'Mydukur Mandal',
            'is_active' => true,
        ]);

        $err2 = VolunteerCadreScopeService::validateParentChildGeography(
            $this->state->id,
            $this->district->id,
            $this->assembly->id,
            $mydukurMandal->id,
            null
        );
        $this->assertNotNull($err2);
        $this->assertStringContainsString('Assembly Segment', $err2);

        // 3. Assembly Badvel + Porumamilla mandal (which belongs to Badvel) => valid
        $err3 = VolunteerCadreScopeService::validateParentChildGeography(
            $this->state->id,
            $this->district->id,
            $this->assembly->id,
            $this->mandal->id,
            null
        );
        $this->assertNull($err3);
    }

    /** 11. Cross-Assembly Mandal Fallback is Disallowed in Backfill */
    public function test_cross_assembly_mandal_fallback_is_disallowed_in_backfill()
    {
        $otherAssembly = GeoAssemblySegment::create([
            'district_id' => $this->district->id,
            'name' => 'Mydukur',
            'is_active' => true,
        ]);

        // Create a mandal named 'Khajipet' belonging to Mydukur assembly
        GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => $otherAssembly->id,
            'name' => 'Khajipet',
            'is_active' => true,
        ]);

        // Record has Badvel assembly segment + Khajipet mandal (which is in Mydukur)
        $membership = Membership::create([
            'membership_id' => '100000000020',
            'phone' => '9876500020',
            'full_name' => 'Cross Assembly Test',
            'state' => 'Andhra Pradesh',
            'district' => 'YSR Kadapa',
            'assembly_segment' => 'Badvel',
            'mandal' => 'Khajipet',
            'payment_status' => 'success',
        ]);

        $service = new GeoHierarchyBackfillService();
        $res = $service->evaluateRecord($membership, 'membership', true);

        $this->assertEquals('GEOGRAPHIC_CONFLICT', $res['classification']);
        $this->assertStringContainsString('different Assembly Segment', $res['reason']);
    }

    /** 12. Strict is_active True/False/Null Enforcement */
    public function test_strict_is_active_enforcement_in_verification()
    {
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
        ]);

        // 1. is_active = true => allowed
        $panchayatPres->is_active = true;
        $this->assertTrue(VolunteerCadreScopeService::isVerifiedCadre($panchayatPres));

        // 2. is_active = false => denied
        $panchayatPres->is_active = false;
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($panchayatPres));

        // 3. is_active = null => denied
        $panchayatPres->is_active = null;
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($panchayatPres));
    }

    /** 13. Jurisdiction ID is Required in isVerifiedCadre */
    public function test_jurisdiction_id_is_required_in_is_verified_cadre()
    {
        // 1. State President missing state_id => denied
        $statePres = $this->createVolunteer([
            'cadre_level' => 'state_president',
            'state_id' => null,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($statePres));

        // 2. District President missing district_id => denied
        $distPres = $this->createVolunteer([
            'cadre_level' => 'district_president',
            'state_id' => $this->state->id,
            'district_id' => null,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($distPres));

        // 3. Mandal President missing mandal_id => denied
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => null,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($mandalPres));

        // 4. Panchayat President missing panchayat_id => denied
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => null,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $this->assertFalse(VolunteerCadreScopeService::isVerifiedCadre($panchayatPres));

        // 5. National President requires no lower jurisdiction ID => allowed
        $natPres = $this->createVolunteer([
            'cadre_level' => 'national_president',
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);
        $this->assertTrue(VolunteerCadreScopeService::isVerifiedCadre($natPres));
    }

    /** 14. Authorized Member Query Uses Canonical IDs ONLY (Legacy Text NEVER Expands Scope) */
    public function test_members_for_uses_canonical_ids_only()
    {
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'status' => 'approved',
            'is_active' => true,
        ]);

        // Member A: Has legacy text 'Akkalareddypalli' but canonical panchayat_id is NULL
        $memberA = Membership::create([
            'membership_id' => '100000000031',
            'phone' => '9876500031',
            'full_name' => 'Legacy Text Member',
            'grama_panchayat' => 'Akkalareddypalli',
            'mandal' => 'Porumamilla',
            'district' => 'YSR Kadapa',
            'state' => 'Andhra Pradesh',
            'panchayat_id' => null, // Not canonically mapped
            'payment_status' => 'success',
        ]);

        // Member B: Has canonical panchayat_id
        $memberB = Membership::create([
            'membership_id' => '100000000032',
            'phone' => '9876500032',
            'full_name' => 'Canonical Member',
            'panchayat_id' => $this->panchayat->id,
            'mandal_id' => $this->mandal->id,
            'district_id' => $this->district->id,
            'state_id' => $this->state->id,
            'payment_status' => 'success',
        ]);

        $visibleMembers = VolunteerCadreScopeService::membersFor($panchayatPres)->pluck('membership_id')->toArray();

        $this->assertContains('100000000032', $visibleMembers, 'Canonically mapped member must be visible');
        $this->assertNotContains('100000000031', $visibleMembers, 'Legacy text member without canonical FK must NOT be visible');
    }

    /** 15. Scoped Query Builders Fail Closed When Not Verified */
    public function test_scoped_query_builders_fail_closed_when_not_verified()
    {
        $unverifiedPres = $this->createVolunteer([
            'cadre_level' => 'state_president',
            'state_id' => $this->state->id,
            'geo_mapping_status' => 'unmapped', // Unmapped => not verified
            'status' => 'approved',
            'is_active' => true,
        ]);

        $this->assertEquals(0, VolunteerCadreScopeService::statesFor($unverifiedPres)->count());
        $this->assertEquals(0, VolunteerCadreScopeService::districtsFor($unverifiedPres)->count());
        $this->assertEquals(0, VolunteerCadreScopeService::assemblySegmentsFor($unverifiedPres)->count());
        $this->assertEquals(0, VolunteerCadreScopeService::mandalsFor($unverifiedPres)->count());
        $this->assertEquals(0, VolunteerCadreScopeService::panchayatsFor($unverifiedPres)->count());
        $this->assertEquals(0, VolunteerCadreScopeService::membersFor($unverifiedPres)->count());
    }

    /** 16. Command Mutual Exclusion Validation */
    public function test_command_mutual_exclusion_validation()
    {
        // 1. Passing both --dry-run and --force fails
        $this->artisan('volunteer-cadre:backfill-geography --dry-run --force')
            ->expectsOutput('Choose either --dry-run or --force, not both.')
            ->assertExitCode(1);

        // 2. Passing both --only-volunteers and --only-memberships fails
        $this->artisan('volunteer-cadre:backfill-geography --only-volunteers --only-memberships')
            ->expectsOutput('Choose either --only-volunteers or --only-memberships, not both.')
            ->assertExitCode(1);

        // 3. Default with no flags runs as read-only dry-run
        $this->artisan('volunteer-cadre:backfill-geography')
            ->expectsOutputToContain('READ-ONLY DRY-RUN')
            ->assertExitCode(0);
    }

    /** 17. Unauthorized Dashboard Access Returns 403 */
    public function test_unauthorized_dashboard_access_returns_403()
    {
        $panchayatPres = $this->createVolunteer([
            'volunteer_id' => '662424',
            'full_name' => 'Panchayat Leader',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($panchayatPres, 'volunteer')->get('/volunteer/dashboard/mandal');
        $response->assertStatus(403);
    }

    /** 18. Subordinate Unit Directory Renders Not Assigned Fallback */
    public function test_subordinate_unit_directory_renders_not_assigned_fallback()
    {
        $mandalPres = $this->createVolunteer([
            'volunteer_id' => '773434',
            'full_name' => 'Mandal Leader',
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'password' => Hash::make('password123'),
        ]);

        $subordinates = VolunteerCadreScopeService::subordinateUnitsFor($mandalPres);
        $this->assertGreaterThan(0, $subordinates->count());
        $akkala = $subordinates->firstWhere('unit_name', 'Akkalareddypalli');
        $this->assertNotNull($akkala);
        $this->assertEquals('Not Assigned', $akkala['president_name']);
        $this->assertFalse($akkala['is_assigned']);
    }

    /** 19. Zero Event History Renders Clean Empty State */
    public function test_zero_event_history_renders_clean_empty_state()
    {
        $panchayatPres = $this->createVolunteer([
            'volunteer_id' => '662424',
            'full_name' => 'Panchayat Leader',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($panchayatPres, 'volunteer')->get('/volunteer/dashboard/panchayat');
        $response->assertStatus(200);
        $response->assertSee('0'); // Benefits delivered count is 0
    }

    /** 20. Cascading Geo AJAX Endpoint Returns Accurate Children */
    public function test_cascading_geo_ajax_endpoint_returns_accurate_children()
    {
        // 1. Get Districts by State
        $response1 = $this->actingAs($this->admin)->getJson("/admin/geo/hierarchy?state_id={$this->state->id}");
        $response1->assertStatus(200);
        $response1->assertJsonFragment(['name' => 'YSR Kadapa']);

        // 2. Get Mandals by District
        $response2 = $this->actingAs($this->admin)->getJson("/admin/geo/hierarchy?district_id={$this->district->id}");
        $response2->assertStatus(200);
        $response2->assertJsonFragment(['name' => 'Porumamilla']);

        // 3. Get Panchayats by Mandal
        $response3 = $this->actingAs($this->admin)->getJson("/admin/geo/hierarchy?mandal_id={$this->mandal->id}");
        $response3->assertStatus(200);
        $response3->assertJsonFragment(['name' => 'Akkalareddypalli']);
    }

    /** 21. Public President Title Accessor */
    public function test_public_president_title_accessor()
    {
        $this->assertEquals('Panchayat President', Volunteer::cadreLevelToPublicTitle('panchayat_president'));
        $this->assertEquals('Mandal President', Volunteer::cadreLevelToPublicTitle('mandal_president'));
        $this->assertEquals('Taluka President / Assembly Segment President', Volunteer::cadreLevelToPublicTitle('assembly_president'));
        $this->assertEquals('District President', Volunteer::cadreLevelToPublicTitle('district_president'));
        $this->assertEquals('State President', Volunteer::cadreLevelToPublicTitle('state_president'));
        $this->assertEquals('National President', Volunteer::cadreLevelToPublicTitle('national_president'));
        $this->assertEquals('Volunteer', Volunteer::cadreLevelToPublicTitle('volunteer'));
    }
}
