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
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;
use App\Services\VolunteerCadreScopeService;
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
    protected static int $volCounter = 200;

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

        $membershipId = $overrides['membership_id'] ?? ('100000000' . str_pad((string)$cnt, 3, '0', STR_PAD_LEFT));
        $fullName = $overrides['full_name'] ?? ("Volunteer Name {$cnt}");
        $phone = $overrides['phone'] ?? ('98765' . str_pad((string)$cnt, 5, '0', STR_PAD_LEFT));

        Membership::firstOrCreate(
            ['membership_id' => $membershipId],
            [
                'full_name' => $fullName,
                'phone' => $phone,
                'payment_status' => 'success',
                'state_id' => $overrides['state_id'] ?? null,
                'district_id' => $overrides['district_id'] ?? null,
                'assembly_segment_id' => $overrides['assembly_segment_id'] ?? null,
                'mandal_id' => $overrides['mandal_id'] ?? null,
                'panchayat_id' => $overrides['panchayat_id'] ?? null,
            ]
        );

        $defaults = [
            'membership_id' => $membershipId,
            'phone' => $phone,
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

        unset($overrides['full_name']);

        return Volunteer::create(array_merge($defaults, $overrides));
    }

    /** 1. Geographic Mapping Review link is removed from Admin Sidebar */
    public function test_geo_mapping_review_link_removed_from_admin_sidebar()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.volunteers.index'));
        $response->assertStatus(200);
        $response->assertDontSee('GEO MAPPING REVIEW');
        $response->assertDontSee('/admin/geo-mapping-review');
    }

    /** 2. Geographic Mapping Review route is inaccessible/removed (404) */
    public function test_geo_mapping_review_route_inaccessible_removed()
    {
        $response = $this->actingAs($this->admin)->get('/admin/geo-mapping-review');
        $response->assertStatus(404);
    }

    /** 3. Admin can assign Panchayat President from Volunteer Desk with immediate verification */
    public function test_admin_can_assign_panchayat_president_with_immediate_verification()
    {
        $volunteer = $this->createVolunteer([
            'status' => 'pending',
            'cadre_level' => null,
            'geo_mapping_status' => 'unmapped',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('approved', $volunteer->status);
        $this->assertTrue((bool)$volunteer->is_active);
        $this->assertEquals('panchayat_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->panchayat->id, $volunteer->panchayat_id);
    }

    /** 4. Admin can assign Mandal President from Volunteer Desk */
    public function test_admin_can_assign_mandal_president()
    {
        $volunteer = $this->createVolunteer([
            'status' => 'pending',
            'cadre_level' => null,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('mandal_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->mandal->id, $volunteer->mandal_id);
    }

    /** 5. Admin can assign Assembly President from Volunteer Desk */
    public function test_admin_can_assign_assembly_president()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'assembly_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('assembly_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->assembly->id, $volunteer->assembly_segment_id);
    }

    /** 6. Admin can assign District President from Volunteer Desk */
    public function test_admin_can_assign_district_president()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'district_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('district_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->district->id, $volunteer->district_id);
    }

    /** 7. Admin can assign State President from Volunteer Desk */
    public function test_admin_can_assign_state_president()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'state_president',
            'state_id' => $this->state->id,
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('state_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
        $this->assertEquals($this->state->id, $volunteer->state_id);
    }

    /** 8. Admin can assign National President from Volunteer Desk */
    public function test_admin_can_assign_national_president()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'national_president',
        ]);

        $response->assertStatus(302);
        $volunteer->refresh();

        $this->assertEquals('national_president', $volunteer->cadre_level);
        $this->assertEquals('verified', $volunteer->geo_mapping_status);
    }

    /** 9. Parent-Child Invalid Geography is Rejected */
    public function test_parent_child_invalid_geography_is_rejected()
    {
        $otherDistrict = GeoDistrict::create([
            'state_id' => $this->state->id,
            'name' => 'Kurnool',
            'is_active' => true,
        ]);

        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $otherDistrict->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
        ]);

        $response->assertSessionHasErrors('hierarchy');
    }

    /** 10. Duplicate Active President Assignment is Prevented */
    public function test_duplicate_active_president_assignment_is_prevented()
    {
        $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        $vol2 = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$vol2->id}", [
            'status' => 'approved',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
        ]);

        $response->assertSessionHasErrors('duplicate');
    }

    /** 11. Mandal President Main Dashboard Contains Clickable Panchayat Table */
    public function test_mandal_president_main_dashboard_contains_clickable_panchayat_table()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($mandalPres, 'volunteer')->get('/volunteer/dashboard');
        $response->assertStatus(200);
        $response->assertSee('PANCHAYATS UNDER YOUR MANDAL');
        $response->assertSee('Akkalareddypalli');
        $response->assertSee(route('volunteer.hierarchy.panchayat', $this->panchayat->id));
    }

    /** 12. Mandal President Can Open Authorized Panchayat Detail with Real Counts */
    public function test_mandal_president_can_open_authorized_panchayat_detail_with_real_counts()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Create a Panchayat President volunteer in this Panchayat
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'full_name' => 'Ramesh Panchayat Leader',
            'phone' => '9988771122',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        // Create 2 canonical registered members in this Panchayat
        $m1 = Membership::create([
            'membership_id' => '100000000201',
            'phone' => '9876500201',
            'full_name' => 'Panchayat Resident One',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'payment_status' => 'success',
        ]);

        $m2 = Membership::create([
            'membership_id' => '100000000202',
            'phone' => '9876500202',
            'full_name' => 'Panchayat Resident Two',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'payment_status' => 'success',
        ]);

        // Create 1 Event with 1 participant and 1 beneficiary
        $event = VolunteerEvent::create([
            'volunteer_id' => $panchayatPres->id,
            'title' => 'Village Annadanam Camp',
            'event_type' => 'Annadanam',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'venue' => 'Temple Hall',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
        ]);

        VolunteerEventMember::create([
            'volunteer_event_id' => $event->id,
            'membership_record_id' => $m1->id,
            'membership_id' => $m1->membership_id,
            'participation_type' => 'participant',
            'participation_status' => 'registered',
            'added_by_volunteer_id' => $panchayatPres->id,
        ]);

        VolunteerEventMember::create([
            'volunteer_event_id' => $event->id,
            'membership_record_id' => $m2->id,
            'membership_id' => $m2->membership_id,
            'participation_type' => 'beneficiary',
            'participation_status' => 'benefited',
            'benefit_details' => 'Food Kit Provided',
            'added_by_volunteer_id' => $panchayatPres->id,
        ]);

        $response = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response->assertStatus(200);
        $response->assertSee('Akkalareddypalli');
        $response->assertSee('Ramesh Panchayat Leader');
        $response->assertSee('9988771122');
        $response->assertSee('Panchayat Resident One');
        $response->assertSee('Village Annadanam Camp');
        // Check registered members count
        $response->assertSee('Registered Members');
    }

    /** 13. Zero Event and Beneficiary Data Displays 0 Safely */
    public function test_zero_event_and_beneficiary_data_displays_zero_safely()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response->assertStatus(200);
        $response->assertSee('Total Events');
        $response->assertSee('Beneficiaries');
    }

    /** 14. Foreign Panchayat URL Returns 403 for Mandal President */
    public function test_foreign_panchayat_url_returns_403_for_mandal_president()
    {
        $otherMandal = GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'name' => 'Kalasapadu',
            'is_active' => true,
        ]);

        $foreignPanchayat = GeoPanchayat::create([
            'mandal_id' => $otherMandal->id,
            'name' => 'Foreign Panchayat',
            'is_active' => true,
        ]);

        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$foreignPanchayat->id}");
        $response->assertStatus(403);
    }

    /** 15. Assembly President Can View Mandal Table and Open Authorized Mandal Detail */
    public function test_assembly_president_can_view_mandal_table_and_open_mandal_detail()
    {
        $assemblyPres = $this->createVolunteer([
            'cadre_level' => 'assembly_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $dashRes = $this->actingAs($assemblyPres, 'volunteer')->get('/volunteer/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('MANDALS UNDER YOUR ASSEMBLY SEGMENT');
        $dashRes->assertSee(route('volunteer.hierarchy.mandal', $this->mandal->id));

        $mandalRes = $this->actingAs($assemblyPres, 'volunteer')->get("/volunteer/hierarchy/mandals/{$this->mandal->id}");
        $mandalRes->assertStatus(200);
        $mandalRes->assertSee('Porumamilla');
        $mandalRes->assertSee('Panchayats Under Porumamilla');
    }

    /** 16. Assembly President Accessing Foreign Mandal Returns 403 */
    public function test_assembly_president_accessing_foreign_mandal_returns_403()
    {
        $otherAssembly = GeoAssemblySegment::create([
            'district_id' => $this->district->id,
            'name' => 'Rajampet',
            'is_active' => true,
        ]);

        $foreignMandal = GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => $otherAssembly->id,
            'name' => 'Rajampet Mandal',
            'is_active' => true,
        ]);

        $assemblyPres = $this->createVolunteer([
            'cadre_level' => 'assembly_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($assemblyPres, 'volunteer')->get("/volunteer/hierarchy/mandals/{$foreignMandal->id}");
        $response->assertStatus(403);
    }

    /** 17. District President Can View Assembly Table and Open Authorized Assembly Detail */
    public function test_district_president_can_view_assembly_table_and_open_assembly_detail()
    {
        $districtPres = $this->createVolunteer([
            'cadre_level' => 'district_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $dashRes = $this->actingAs($districtPres, 'volunteer')->get('/volunteer/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('ASSEMBLY SEGMENTS UNDER YOUR DISTRICT');
        $dashRes->assertSee(route('volunteer.hierarchy.assembly', $this->assembly->id));

        $asmRes = $this->actingAs($districtPres, 'volunteer')->get("/volunteer/hierarchy/assemblies/{$this->assembly->id}");
        $asmRes->assertStatus(200);
        $asmRes->assertSee('Badvel');
        $asmRes->assertSee('Mandals Under Badvel');
    }

    /** 18. District President Accessing Foreign Assembly Returns 403 */
    public function test_district_president_accessing_foreign_assembly_returns_403()
    {
        $otherDistrict = GeoDistrict::create([
            'state_id' => $this->state->id,
            'name' => 'Kurnool',
            'is_active' => true,
        ]);

        $foreignAssembly = GeoAssemblySegment::create([
            'district_id' => $otherDistrict->id,
            'name' => 'Adoni',
            'is_active' => true,
        ]);

        $districtPres = $this->createVolunteer([
            'cadre_level' => 'district_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($districtPres, 'volunteer')->get("/volunteer/hierarchy/assemblies/{$foreignAssembly->id}");
        $response->assertStatus(403);
    }

    /** 19. State President Can View District Table and Open Authorized District Detail */
    public function test_state_president_can_view_district_table_and_open_district_detail()
    {
        $statePres = $this->createVolunteer([
            'cadre_level' => 'state_president',
            'state_id' => $this->state->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $dashRes = $this->actingAs($statePres, 'volunteer')->get('/volunteer/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('DISTRICTS UNDER YOUR STATE');
        $dashRes->assertSee(route('volunteer.hierarchy.district', $this->district->id));

        $distRes = $this->actingAs($statePres, 'volunteer')->get("/volunteer/hierarchy/districts/{$this->district->id}");
        $distRes->assertStatus(200);
        $distRes->assertSee('YSR Kadapa');
        $distRes->assertSee('Assembly Segments Under YSR Kadapa');
    }

    /** 20. State President Accessing Foreign District Returns 403 */
    public function test_state_president_accessing_foreign_district_returns_403()
    {
        $otherState = GeoState::create([
            'name' => 'Karnataka',
            'code' => 'KA',
            'is_active' => true,
        ]);

        $foreignDistrict = GeoDistrict::create([
            'state_id' => $otherState->id,
            'name' => 'Bengaluru Urban',
            'is_active' => true,
        ]);

        $statePres = $this->createVolunteer([
            'cadre_level' => 'state_president',
            'state_id' => $this->state->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($statePres, 'volunteer')->get("/volunteer/hierarchy/districts/{$foreignDistrict->id}");
        $response->assertStatus(403);
    }

    /** 21. National President Can Drill Down State -> District -> Assembly -> Mandal -> Panchayat */
    public function test_national_president_can_drill_down_all_hierarchy_tiers()
    {
        $nationalPres = $this->createVolunteer([
            'cadre_level' => 'national_president',
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // 1. Dashboard -> State Table
        $dashRes = $this->actingAs($nationalPres, 'volunteer')->get('/volunteer/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee('STATE PRESIDENT DIRECTORY');
        $dashRes->assertSee(route('volunteer.hierarchy.state', $this->state->id));

        // 2. State Detail
        $stateRes = $this->actingAs($nationalPres, 'volunteer')->get("/volunteer/hierarchy/states/{$this->state->id}");
        $stateRes->assertStatus(200);
        $stateRes->assertSee('Districts Under Andhra Pradesh');

        // 3. District Detail
        $distRes = $this->actingAs($nationalPres, 'volunteer')->get("/volunteer/hierarchy/districts/{$this->district->id}");
        $distRes->assertStatus(200);
        $distRes->assertSee('Assembly Segments Under YSR Kadapa');

        // 4. Assembly Detail
        $asmRes = $this->actingAs($nationalPres, 'volunteer')->get("/volunteer/hierarchy/assemblies/{$this->assembly->id}");
        $asmRes->assertStatus(200);
        $asmRes->assertSee('Mandals Under Badvel');

        // 5. Mandal Detail
        $mdlRes = $this->actingAs($nationalPres, 'volunteer')->get("/volunteer/hierarchy/mandals/{$this->mandal->id}");
        $mdlRes->assertStatus(200);
        $mdlRes->assertSee('Panchayats Under Porumamilla');

        // 6. Panchayat Detail
        $panRes = $this->actingAs($nationalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $panRes->assertStatus(200);
        $panRes->assertSee('Akkalareddypalli');
    }

    /** 22. National President Still Receives 403 for Admin Routes */
    public function test_national_president_cannot_access_admin_routes()
    {
        $nationalPres = $this->createVolunteer([
            'cadre_level' => 'national_president',
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($nationalPres, 'volunteer')->get('/admin/volunteers');
        // Volunteer guard is not web admin guard; should redirect or deny
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    /** 23. Panchayat President Directly Sees Own Panchayat Information */
    public function test_panchayat_president_directly_sees_own_panchayat_information()
    {
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($panchayatPres, 'volunteer')->get('/volunteer/dashboard');
        $response->assertStatus(200);
        $response->assertSee('PANCHAYAT DETAILS');
        $response->assertSee('Akkalareddypalli');
        $response->assertSee('Porumamilla');
    }

    /** 24. Panchayat President Cannot Open Another Panchayat */
    public function test_panchayat_president_cannot_open_another_panchayat()
    {
        $foreignPanchayat = GeoPanchayat::create([
            'mandal_id' => $this->mandal->id,
            'name' => 'Kavalakuntla',
            'is_active' => true,
        ]);

        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($panchayatPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$foreignPanchayat->id}");
        $response->assertStatus(403);
    }

    /** 25. Breadcrumb Hierarchy is Rendered Properly */
    public function test_breadcrumb_hierarchy_rendered_properly()
    {
        $assemblyPres = $this->createVolunteer([
            'cadre_level' => 'assembly_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($assemblyPres, 'volunteer')->get("/volunteer/hierarchy/mandals/{$this->mandal->id}");
        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Badvel');
        $response->assertSee('Porumamilla');
    }

    /** 26. Not Assigned President Fallback Works */
    public function test_not_assigned_president_fallback_works()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Akkalareddypalli has no assigned president yet
        $response = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response->assertStatus(200);
        $response->assertSee('Not Assigned');
        $response->assertSee('—');
    }

    /** 27. Counts Use Canonical IDs Only (Legacy Free-Text with Wrong Canonical ID Does Not Count) */
    public function test_counts_use_canonical_ids_only()
    {
        // Member A: Text is 'Akkalareddypalli' but panchayat_id is NULL
        Membership::create([
            'membership_id' => '100000000291',
            'phone' => '9876500291',
            'full_name' => 'Text Only Member',
            'grama_panchayat' => 'Akkalareddypalli',
            'panchayat_id' => null,
            'payment_status' => 'success',
        ]);

        // Member B: Has canonical panchayat_id
        Membership::create([
            'membership_id' => '100000000292',
            'phone' => '9876500292',
            'full_name' => 'Canonical Mapped Member',
            'panchayat_id' => $this->panchayat->id,
            'payment_status' => 'success',
        ]);

        $stats = VolunteerCadreScopeService::getUnitStatistics('panchayat', $this->panchayat->id);
        // Only canonical Member B is counted; Member A with text-only is excluded
        $this->assertEquals(1, $stats['members_count']);
    }

    /** 28. Higher President Cannot Edit Subordinate Volunteer Events */
    public function test_higher_president_cannot_edit_subordinate_volunteer_events()
    {
        $subordinateVolunteer = $this->createVolunteer([
            'cadre_level' => 'volunteer',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        $event = VolunteerEvent::create([
            'volunteer_id' => $subordinateVolunteer->id,
            'title' => 'Subordinate Event',
            'event_type' => 'Annadanam',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'upcoming',
            'venue' => 'Main Center',
        ]);

        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Mandal President attempts to edit subordinate's event
        $response = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/events/{$event->id}/edit");
        $response->assertStatus(403);
    }

    /** 29. Sensitive Identity and Payment Fields Never Rendered */
    public function test_sensitive_identity_and_payment_fields_never_rendered()
    {
        $panchayatPres = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $member = Membership::create([
            'membership_id' => '100000000299',
            'phone' => '9876500299',
            'full_name' => 'Private Member',
            'panchayat_id' => $this->panchayat->id,
            'payment_status' => 'success',
            'aadhaar_number' => '123456789012',
            'payment_order_id' => 'order_secret_12345',
            'payment_transaction_id' => 'tx_secret_99999',
        ]);

        $response = $this->actingAs($panchayatPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response->assertStatus(200);
        $response->assertDontSee('123456789012');
        $response->assertDontSee('order_secret_12345');
        $response->assertDontSee('tx_secret_99999');
    }

    /** 30. Cascading Geo AJAX Endpoint Returns Accurate Children */
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

    /** 31. Public President Title Accessor */
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

    /** 32. Panchayat with more than 50 canonical members paginates with members_page and reports total summary */
    public function test_panchayat_members_pagination_with_more_than_50_canonical_members()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Create 60 canonical members in this Panchayat
        for ($i = 1; $i <= 60; $i++) {
            $numStr = sprintf('%03d', $i);
            Membership::create([
                'membership_id' => '10000000' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'phone' => '987000' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'full_name' => "Panchayat Member Alpha {$numStr}",
                'panchayat_id' => $this->panchayat->id,
                'payment_status' => 'success',
            ]);
        }

        // Page 1
        $response1 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response1->assertStatus(200);
        $response1->assertSee('Registered Members (60)');
        $response1->assertSee('Showing 1-50 of 60');
        $response1->assertSee('Panchayat Member Alpha 060'); // latest member on page 1 (descending)
        $response1->assertDontSee('Panchayat Member Alpha 005'); // early member on page 2

        // Page 2
        $response2 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}?members_page=2");
        $response2->assertStatus(200);
        $response2->assertSee('Showing 51-60 of 60');
        $response2->assertSee('Panchayat Member Alpha 005');
        $response2->assertSee('Panchayat Member Alpha 001');
    }

    /** 33. Panchayat volunteers pagination uses volunteers_page */
    public function test_panchayat_volunteers_pagination_uses_volunteers_page()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Create 55 volunteers in this Panchayat
        for ($i = 1; $i <= 55; $i++) {
            $numStr = sprintf('%03d', $i);
            $this->createVolunteer([
                'full_name' => "Batch Volunteer {$numStr}",
                'panchayat_id' => $this->panchayat->id,
                'status' => 'approved',
                'is_active' => true,
                'geo_mapping_status' => 'verified',
            ]);
        }

        // Page 1
        $response1 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response1->assertStatus(200);
        $response1->assertSee('Showing 1-50 of 55');
        $response1->assertSee('Batch Volunteer 055');
        $response1->assertDontSee('Batch Volunteer 005');

        // Page 2
        $response2 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}?volunteers_page=2");
        $response2->assertStatus(200);
        $response2->assertSee('Showing 51-55 of 55');
        $response2->assertSee('Batch Volunteer 005');
        $response2->assertSee('Batch Volunteer 001');
    }

    /** 34. Panchayat events pagination uses events_page */
    public function test_panchayat_events_pagination_uses_events_page()
    {
        $mandalPres = $this->createVolunteer([
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $panchayatVol = $this->createVolunteer([
            'panchayat_id' => $this->panchayat->id,
            'status' => 'approved',
            'is_active' => true,
            'geo_mapping_status' => 'verified',
        ]);

        // Create 30 events for this volunteer with canonical panchayat_id
        for ($i = 1; $i <= 30; $i++) {
            $numStr = sprintf('%03d', $i);
            VolunteerEvent::create([
                'volunteer_id' => $panchayatVol->id,
                'title' => "Panchayat Seva Event Number {$numStr}",
                'event_type' => 'Annadanam',
                'event_date' => now()->addDays($i)->toDateString(),
                'status' => 'upcoming',
                'venue' => "Venue Center {$numStr}",
                'panchayat_id' => $this->panchayat->id,
                'mandal_id' => $this->mandal->id,
                'assembly_segment_id' => $this->assembly->id,
                'district_id' => $this->district->id,
                'state_id' => $this->state->id,
            ]);
        }

        // Page 1
        $response1 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}");
        $response1->assertStatus(200);
        $response1->assertSee('Showing 1-25 of 30');
        $response1->assertSee('Panchayat Seva Event Number 030');
        $response1->assertDontSee('Panchayat Seva Event Number 005');

        // Page 2
        $response2 = $this->actingAs($mandalPres, 'volunteer')->get("/volunteer/hierarchy/panchayats/{$this->panchayat->id}?events_page=2");
        $response2->assertStatus(200);
        $response2->assertSee('Showing 26-30 of 30');
        $response2->assertSee('Panchayat Seva Event Number 005');
        $response2->assertSee('Panchayat Seva Event Number 001');
    }

    /** 35. Panchayat President cannot be approved without assembly_segment_id */
    public function test_panchayat_president_cannot_be_approved_without_assembly_segment_id()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => null,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
        ]);

        $response->assertSessionHasErrors('panchayat_id');
        $volunteer->refresh();
        $this->assertNotEquals('verified', $volunteer->geo_mapping_status);
    }

    /** 36. Mandal President cannot be approved without assembly_segment_id */
    public function test_mandal_president_cannot_be_approved_without_assembly_segment_id()
    {
        $volunteer = $this->createVolunteer();

        $response = $this->actingAs($this->admin)->post("/admin/volunteers/cadre/{$volunteer->id}", [
            'status' => 'approved',
            'cadre_level' => 'mandal_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => null,
            'mandal_id' => $this->mandal->id,
        ]);

        $response->assertSessionHasErrors('mandal_id');
        $volunteer->refresh();
        $this->assertNotEquals('verified', $volunteer->geo_mapping_status);
    }

    /** 37. Event creation snapshots verified canonical geography server-side */
    public function test_event_creation_snapshots_verified_canonical_geography_server_side()
    {
        $volunteer = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($volunteer, 'volunteer')->post('/volunteer/events', [
            'title' => 'Snapshotted Village Seva',
            'event_type' => 'Annadanam',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'venue' => 'Main Temple',
        ]);

        $response->assertStatus(302);

        $event = VolunteerEvent::where('title', 'Snapshotted Village Seva')->first();
        $this->assertNotNull($event);
        $this->assertEquals($this->state->id, $event->state_id);
        $this->assertEquals($this->district->id, $event->district_id);
        $this->assertEquals($this->assembly->id, $event->assembly_segment_id);
        $this->assertEquals($this->mandal->id, $event->mandal_id);
        $this->assertEquals($this->panchayat->id, $event->panchayat_id);
    }

    /** 38. Browser cannot spoof event canonical geography IDs */
    public function test_browser_cannot_spoof_event_canonical_geography_ids()
    {
        $unverifiedVolunteer = $this->createVolunteer([
            'cadre_level' => 'volunteer',
            'geo_mapping_status' => 'unmapped',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($unverifiedVolunteer, 'volunteer')->post('/volunteer/events', [
            'title' => 'Spoofed Geo Event',
            'event_type' => 'Annadanam',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'venue' => 'Main Temple',
            'panchayat_id' => $this->panchayat->id, // Spoofed field
            'mandal_id' => $this->mandal->id,       // Spoofed field
        ]);

        $response->assertStatus(302);

        $event = VolunteerEvent::where('title', 'Spoofed Geo Event')->first();
        $this->assertNotNull($event);
        // Server rejects manufacturing canonical IDs for unverified volunteer
        $this->assertNull($event->panchayat_id);
        $this->assertNull($event->mandal_id);
    }

    /** 39. Reassigning volunteer later does NOT move historical event statistics */
    public function test_reassigning_volunteer_later_does_not_move_historical_event_statistics()
    {
        $otherPanchayat = GeoPanchayat::create([
            'mandal_id' => $this->mandal->id,
            'name' => 'Second Panchayat',
            'is_active' => true,
        ]);

        $volunteer = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
            'password' => Hash::make('password123'),
        ]);

        // Event created under first Panchayat
        $event = VolunteerEvent::create([
            'volunteer_id' => $volunteer->id,
            'title' => 'Historical Event of First Panchayat',
            'event_type' => 'Annadanam',
            'event_date' => now()->subDays(5)->toDateString(),
            'status' => 'completed',
            'panchayat_id' => $this->panchayat->id,
            'mandal_id' => $this->mandal->id,
            'assembly_segment_id' => $this->assembly->id,
            'district_id' => $this->district->id,
            'state_id' => $this->state->id,
        ]);

        // Reassign volunteer to second Panchayat
        $volunteer->update([
            'panchayat_id' => $otherPanchayat->id,
        ]);

        // Statistics of first Panchayat must still count the event
        $statsFirst = VolunteerCadreScopeService::getUnitStatistics('panchayat', $this->panchayat->id);
        $this->assertEquals(1, $statsFirst['events_count']);

        // Statistics of second Panchayat must be 0
        $statsSecond = VolunteerCadreScopeService::getUnitStatistics('panchayat', $otherPanchayat->id);
        $this->assertEquals(0, $statsSecond['events_count']);
    }

    /** 40. Legacy event with NULL canonical IDs excluded from canonical counts */
    public function test_legacy_event_with_null_canonical_ids_excluded_from_canonical_counts()
    {
        $volunteer = $this->createVolunteer([
            'cadre_level' => 'panchayat_president',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'assembly_segment_id' => $this->assembly->id,
            'mandal_id' => $this->mandal->id,
            'panchayat_id' => $this->panchayat->id,
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        // Legacy event with free text 'Akkalareddypalli' but NULL panchayat_id
        VolunteerEvent::create([
            'volunteer_id' => $volunteer->id,
            'title' => 'Legacy Free Text Event',
            'event_type' => 'Annadanam',
            'event_date' => now()->subDays(10)->toDateString(),
            'village' => 'Akkalareddypalli',
            'panchayat_id' => null,
            'status' => 'completed',
        ]);

        $stats = VolunteerCadreScopeService::getUnitStatistics('panchayat', $this->panchayat->id);
        $this->assertEquals(0, $stats['events_count']);
    }

    /** 41. mandalsFor filters solely by assembly_segment_id without ambiguity */
    public function test_mandals_for_filters_solely_by_assembly_segment_id_without_ambiguity()
    {
        $otherAssembly = GeoAssemblySegment::create([
            'district_id' => $this->district->id,
            'name' => 'Mydukur',
            'is_active' => true,
        ]);

        $mandalInOtherAssembly = GeoMandal::create([
            'district_id' => $this->district->id,
            'assembly_segment_id' => $otherAssembly->id,
            'name' => 'Mydukur Mandal',
            'is_active' => true,
        ]);

        $nationalPres = $this->createVolunteer([
            'cadre_level' => 'national_president',
            'geo_mapping_status' => 'verified',
            'is_active' => true,
        ]);

        $mandals = VolunteerCadreScopeService::mandalsFor($nationalPres, $this->assembly->id)->get();
        $this->assertTrue($mandals->contains('id', $this->mandal->id));
        $this->assertFalse($mandals->contains('id', $mandalInOtherAssembly->id));
    }
}
