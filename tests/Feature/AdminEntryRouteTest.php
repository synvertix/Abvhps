<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminEntryRouteTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'ADMIN TEST OFFICER',
            'email' => 'admin@abvhps.org',
            'password' => bcrypt('AdminPassword123')
        ]);
    }

    /**
     * TEST 1 — Logged out GET /admin redirects to /admin/login
     */
    public function test_unauthenticated_get_admin_redirects_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $followResponse = $this->get(route('login'));
        $followResponse->assertStatus(200);
        $followResponse->assertSee('Administrative Gate Entrance');
    }

    /**
     * TEST 2 — Logged out direct dashboard GET /admin/dashboard redirects to /admin/login
     */
    public function test_unauthenticated_get_admin_dashboard_redirects_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $this->assertGuest('web');
    }

    /**
     * TEST 3 — Successful login redirects to admin.dashboard
     */
    public function test_successful_admin_login_redirects_to_dashboard(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'email' => 'admin@abvhps.org',
            'password' => 'AdminPassword123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin, 'web');
    }

    /**
     * TEST 4 — Already logged in GET /admin redirects via login route to admin.dashboard
     */
    public function test_authenticated_get_admin_redirects_to_dashboard(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/admin');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $follow = $this->actingAs($this->admin, 'web')->get(route('login'));
        $follow->assertStatus(302);
        $follow->assertRedirect(route('admin.dashboard'));

        $dashboardResponse = $this->actingAs($this->admin, 'web')->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
    }

    /**
     * TEST 5 — Logout invalidates session and redirects to login, after which GET /admin shows login page again
     */
    public function test_admin_logout_redirects_to_login_and_locks_gateways(): void
    {
        $this->actingAs($this->admin, 'web');

        $response = $this->post(route('admin.logout'));
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertGuest('web');

        $adminResponse = $this->get('/admin');
        $adminResponse->assertStatus(302);
        $adminResponse->assertRedirect(route('login'));
    }
}
