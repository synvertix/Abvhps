<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\Blog;
use App\Models\OurTeam;
use App\Models\OurSupport;
use App\Models\VolunteerEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

class RouteAndAccessAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Volunteer $approvedVolunteer;
    protected Volunteer $unapprovedVolunteer;
    protected Volunteer $passwordChangeVolunteer;
    protected Membership $approvedMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Commander',
            'email' => 'commander@abvhps.org',
            'password' => Hash::make('AdminSecure123!'),
        ]);

        $this->approvedMember = Membership::create([
            'membership_id' => '915000000001',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'full_name' => 'NATIONAL PRESIDENT VOLUNTEER',
            'country' => 'India',
            'state' => 'Andhra Pradesh',
            'district' => 'Kadapa',
            'assembly_segment' => 'Badvel',
            'mandal' => 'Porumamilla',
            'grama_panchayat' => 'Porumamilla GP',
            'is_completed' => 1,
        ]);

        $this->approvedVolunteer = Volunteer::create([
            'membership_id' => $this->approvedMember->membership_id,
            'volunteer_login_id' => '888001',
            'volunteer_id' => '888001',
            'full_name' => 'NATIONAL PRESIDENT VOLUNTEER',
            'phone' => '9876543210',
            'email' => 'president@abvhps.org',
            'qualification' => 'Post Graduate',
            'voter_id_number' => 'VTR999001',
            'bank_name' => 'State Bank of India',
            'account_holder_name' => 'National President',
            'account_number' => '1122334455',
            'ifsc_code' => 'SBIN0001234',
            'branch_name' => 'Kadapa Main',
            'nominee_name' => 'Nominee Person',
            'nominee_relation' => 'Spouse',
            'nominee_phone' => '9876543219',
            'document_declaration_path' => 'docs/dec.pdf',
            'document_voter_path' => 'docs/voter.pdf',
            'document_bank_path' => 'docs/bank.pdf',
            'password' => Hash::make('PermanentPass123!'),
            'status' => 'approved',
            'is_active' => true,
            'cadre_level' => 'national_president',
            'must_change_password' => false,
        ]);

        $this->unapprovedVolunteer = Volunteer::create([
            'membership_id' => '915000000002',
            'volunteer_login_id' => '888002',
            'volunteer_id' => '888002',
            'full_name' => 'PENDING VOLUNTEER',
            'phone' => '9876543211',
            'email' => 'pending@abvhps.org',
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR999002',
            'bank_name' => 'SBI',
            'account_holder_name' => 'Pending Volunteer',
            'account_number' => '1122334456',
            'ifsc_code' => 'SBIN0001234',
            'branch_name' => 'Kadapa Main',
            'nominee_name' => 'Nominee',
            'nominee_relation' => 'Parent',
            'nominee_phone' => '9876543218',
            'document_declaration_path' => 'docs/dec2.pdf',
            'document_voter_path' => 'docs/voter2.pdf',
            'document_bank_path' => 'docs/bank2.pdf',
            'password' => Hash::make('TempPass123!'),
            'status' => 'pending',
            'is_active' => false,
            'cadre_level' => 'panchayat_president',
            'must_change_password' => false,
        ]);

        $this->passwordChangeVolunteer = Volunteer::create([
            'membership_id' => '915000000003',
            'volunteer_login_id' => '888003',
            'volunteer_id' => '888003',
            'full_name' => 'NEW VOLUNTEER',
            'phone' => '9876543212',
            'email' => 'newvol@abvhps.org',
            'qualification' => 'Graduate',
            'voter_id_number' => 'VTR999003',
            'bank_name' => 'SBI',
            'account_holder_name' => 'New Volunteer',
            'account_number' => '1122334457',
            'ifsc_code' => 'SBIN0001234',
            'branch_name' => 'Kadapa Main',
            'nominee_name' => 'Nominee',
            'nominee_relation' => 'Parent',
            'nominee_phone' => '9876543217',
            'document_declaration_path' => 'docs/dec3.pdf',
            'document_voter_path' => 'docs/voter3.pdf',
            'document_bank_path' => 'docs/bank3.pdf',
            'password' => Hash::make('TempPass123!'),
            'status' => 'approved',
            'is_active' => true,
            'cadre_level' => 'panchayat_president',
            'must_change_password' => true,
        ]);
    }

    /**
     * =========================================================================
     * 1. ADMIN SECURITY & AUTHORIZATION TESTS
     * =========================================================================
     */
    public function test_guest_is_denied_access_to_all_protected_admin_routes(): void
    {
        $protectedAdminRoutes = [
            '/admin/dashboard',
            '/admin/our-team',
            '/admin/our-team/create',
            '/admin/team',
            '/admin/team/create',
            '/admin/donations',
            '/admin/blogs',
            '/admin/blogs/create',
            '/admin/gallery',
            '/admin/support',
            '/admin/support/create',
            '/admin/our-supports',
            '/admin/our-supports/create',
            '/admin/membership-ledger',
            '/admin/membership-pending',
            '/admin/volunteers',
            '/admin/volunteer-events',
            '/admin/rudrasena',
            '/admin/local-gateways',
            '/admin/exams',
            '/admin/fundraising',
            '/admin/fundraising/create',
            '/admin/contacts',
            '/admin/certificates',
            '/admin/settings',
            '/admin/banner',
            '/admin/banners',
            '/admin/volunteer/view-card/888001',
        ];

        foreach ($protectedAdminRoutes as $uri) {
            $response = $this->get($uri);
            $response->assertRedirect(route('login'));
        }
    }

    public function test_guest_is_denied_from_admin_post_endpoints(): void
    {
        $responseApprove = $this->post('/admin/volunteer/approve', ['volunteer_id' => 1, 'action' => 'approve']);
        $responseApprove->assertRedirect(route('login'));

        $responseStoreSupport = $this->post('/admin/our-supports/store', ['name' => 'TEST']);
        $responseStoreSupport->assertRedirect(route('login'));
    }

    public function test_authenticated_volunteer_cannot_access_admin_routes(): void
    {
        $volunteer = $this->approvedVolunteer;

        $response = $this->actingAs($volunteer, 'volunteer')->get('/admin/dashboard');
        $response->assertRedirect(route('login'));

        $responseSupport = $this->actingAs($volunteer, 'volunteer')->get('/admin/our-supports');
        $responseSupport->assertRedirect(route('login'));

        $responseVolCard = $this->actingAs($volunteer, 'volunteer')->get('/admin/volunteer/view-card/888001');
        $responseVolCard->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_access_safe_admin_get_pages(): void
    {
        $admin = $this->admin;

        $safeAdminRoutes = [
            route('admin.dashboard'),
            route('admin.our_team.index'),
            route('admin.our_team.create'),
            route('admin.blogs.index'),
            route('admin.blogs.create'),
            route('admin.gallery.index'),
            route('admin.support.index'),
            route('admin.support.create'),
            route('admin.our_support.index'),
            route('admin.our_support.create'),
            route('admin.membership.ledger'),
            route('admin.membership.pending'),
            route('admin.volunteers.index'),
            route('admin.volunteer_events.index'),
            route('admin.rudrasena.index'),
            route('admin.local_gateways.index'),
            route('admin.exams.index'),
            route('admin.fundraising.index'),
            route('admin.fundraising.create'),
            route('admin.contacts.index'),
            route('admin.certificates.index'),
            route('admin.settings.index'),
            route('admin.banner.index'),
        ];

        foreach ($safeAdminRoutes as $url) {
            $response = $this->actingAs($admin, 'web')->get($url);
            $response->assertStatus(200);
        }
    }

    /**
     * =========================================================================
     * 2. ADMIN EDIT PAGES RENDERING & FORM WIRING
     * =========================================================================
     */
    public function test_our_team_edit_page_renders_successfully_with_member_data(): void
    {
        $member = OurTeam::create([
            'membership_id' => '915000000001',
            'name' => 'SRI KASI RAMA SWAMY',
            'cadre_level' => 'national_level',
            'designation' => 'National Committee President',
            'locality' => 'BHARATH',
            'image_path' => 'teams/sample_leader.jpg',
        ]);

        $response = $this->actingAs($this->admin, 'web')->get(route('admin.our_team.edit', $member->id));
        $response->assertStatus(200);
        $response->assertSee('SRI KASI RAMA SWAMY');
        $response->assertSee('National Committee President');
        $response->assertSee(route('admin.our_team.update', $member->id));
    }

    public function test_blog_edit_page_renders_successfully_via_canonical_and_alias_routes(): void
    {
        $blog = Blog::create([
            'title' => 'Sacred Temple Heritage',
            'content' => 'Comprehensive description of ancient temples and traditions.',
            'status' => 'active',
            'image_path' => 'blogs/main/sample.jpg',
            'thumbnail_path' => 'blogs/thumb/sample.jpg',
        ]);

        // Canonical route
        $resCanonical = $this->actingAs($this->admin, 'web')->get(route('admin.blogs.edit', $blog->id));
        $resCanonical->assertStatus(200);
        $resCanonical->assertSee('Sacred Temple Heritage');
        $resCanonical->assertSee(route('admin.blogs.update', $blog->id));

        // Legacy alias route
        $resAlias = $this->actingAs($this->admin, 'web')->get(route('admin.blog.edit', $blog->id));
        $resAlias->assertStatus(200);
        $resAlias->assertSee('Sacred Temple Heritage');
    }

    public function test_our_support_edit_page_renders_successfully_via_canonical_and_alias_routes(): void
    {
        $support = OurSupport::create([
            'name' => 'ANNAPURNA SEVA SCHEME',
            'sort_order' => 1,
            'short_info' => 'Providing daily meals to pilgrims and devotees.',
            'status' => 'show',
            'image_path' => 'supports/sample.jpg',
        ]);

        // Canonical route
        $resCanonical = $this->actingAs($this->admin, 'web')->get(route('admin.support.edit', $support->id));
        $resCanonical->assertStatus(200);
        $resCanonical->assertSee('ANNAPURNA SEVA SCHEME');
        $resCanonical->assertSee(route('admin.support.update', $support->id));

        // Legacy alias route
        $resAlias = $this->actingAs($this->admin, 'web')->get(route('admin.our_supports.edit', $support->id));
        $resAlias->assertStatus(200);
        $resAlias->assertSee('ANNAPURNA SEVA SCHEME');
    }

    public function test_blog_index_with_records_renders_and_contains_valid_delete_route(): void
    {
        $blog = Blog::create([
            'title' => 'Festivals of Sanatana Dharma',
            'content' => 'Overview of annual celebrations.',
            'status' => 'active',
            'image_path' => 'blogs/main/fest.jpg',
            'thumbnail_path' => 'blogs/thumb/fest.jpg',
        ]);

        $response = $this->actingAs($this->admin, 'web')->get(route('admin.blogs.index'));
        $response->assertStatus(200);
        $response->assertSee('Festivals of Sanatana Dharma');
        $response->assertSee(route('admin.blogs.delete', $blog->id));
    }

    /**
     * =========================================================================
     * 3. VOLUNTEER SECURITY & MIDDLEWARE STACK TESTS
     * =========================================================================
     */
    public function test_guest_is_denied_from_all_volunteer_dashboard_routes(): void
    {
        $volunteerDashboardUris = [
            '/volunteer/dashboard',
            '/volunteer/profile',
            '/volunteer/member-data',
            '/volunteer/events',
            '/volunteer/member-search',
            '/volunteer/dashboard/panchayat',
            '/volunteer/dashboard/village',
            '/volunteer/dashboard/mandal',
            '/volunteer/dashboard/assembly',
            '/volunteer/dashboard/district',
            '/volunteer/dashboard/state',
            '/volunteer/dashboard/national',
            '/volunteer/dashboard/global',
        ];

        foreach ($volunteerDashboardUris as $uri) {
            $response = $this->get($uri);
            $response->assertRedirect(route('volunteer.login'));
        }
    }

    public function test_admin_web_session_does_not_gain_volunteer_dashboard_access(): void
    {
        $admin = $this->admin;

        $response = $this->actingAs($admin, 'web')->get('/volunteer/dashboard');
        $response->assertRedirect(route('volunteer.login'));

        $responseVillage = $this->actingAs($admin, 'web')->get('/volunteer/dashboard/village');
        $responseVillage->assertRedirect(route('volunteer.login'));
    }

    public function test_unapproved_volunteer_is_denied_from_all_volunteer_dashboard_routes(): void
    {
        $pendingVol = $this->unapprovedVolunteer;

        $response = $this->actingAs($pendingVol, 'volunteer')->get('/volunteer/dashboard');
        $response->assertRedirect(route('volunteer.login'));

        $responseVillage = $this->actingAs($pendingVol, 'volunteer')->get('/volunteer/dashboard/village');
        $responseVillage->assertRedirect(route('volunteer.login'));
    }

    public function test_volunteer_requiring_password_change_is_redirected_to_change_password(): void
    {
        $passVol = $this->passwordChangeVolunteer;

        $response = $this->actingAs($passVol, 'volunteer')->get('/volunteer/dashboard');
        $response->assertRedirect(route('volunteer.change_password'));

        $responseVillage = $this->actingAs($passVol, 'volunteer')->get('/volunteer/dashboard/village');
        $responseVillage->assertRedirect(route('volunteer.change_password'));
    }

    public function test_approved_volunteer_can_access_dashboard_and_authorized_jurisdiction(): void
    {
        $vol = $this->approvedVolunteer;

        $response = $this->actingAs($vol, 'volunteer')->get(route('volunteer.dashboard'));
        $response->assertStatus(200);

        // National president can access national and global dashboards
        $resNat = $this->actingAs($vol, 'volunteer')->get(route('volunteer.dashboard.national'));
        $resNat->assertStatus(200);

        $resGlobal = $this->actingAs($vol, 'volunteer')->get(route('volunteer.dashboard.global'));
        $resGlobal->assertStatus(200);
    }

    public function test_unauthorized_drilldown_or_cadre_tampering_is_denied_with_403(): void
    {
        $vol = $this->approvedVolunteer; // national president

        // Attempting to access village dashboard without being panchayat president gives 403
        $responseVillage = $this->actingAs($vol, 'volunteer')->get('/volunteer/dashboard/village');
        $responseVillage->assertStatus(403);
    }

    /**
     * =========================================================================
     * 4. ROUTE INTEGRITY & CONTROLLER METHOD RESOLUTION
     * =========================================================================
     */
    public function test_all_routes_have_existing_controller_classes_and_action_methods(): void
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $action = $route->getActionName();

            if (is_string($action) && str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                $this->assertTrue(
                    class_exists($controller),
                    "Controller class [{$controller}] for route [{$route->uri()}] does not exist."
                );
                $this->assertTrue(
                    method_exists($controller, $method),
                    "Method [{$method}] on Controller [{$controller}] for route [{$route->uri()}] does not exist."
                );
            }
        }
    }

    public function test_no_duplicate_route_names_exist_in_route_collection(): void
    {
        $routes = Route::getRoutes();
        $nameCounts = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            if ($name) {
                $nameCounts[$name] = ($nameCounts[$name] ?? 0) + 1;
            }
        }

        foreach ($nameCounts as $name => $count) {
            $this->assertEquals(1, $count, "Route name [{$name}] is defined {$count} times (must be unique).");
        }
    }
}
