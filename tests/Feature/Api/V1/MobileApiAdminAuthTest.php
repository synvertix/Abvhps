<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class MobileApiAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create authorized administrator
        $this->adminUser = User::create([
            'name'     => 'Central Admin',
            'email'    => 'admin@abvhps.org',
            'password' => Hash::make('AdminSecret123!'),
        ]);
    }

    public function test_valid_admin_can_authenticate_and_receive_sanctum_token(): void
    {
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email'       => 'admin@abvhps.org',
            'password'    => 'AdminSecret123!',
            'device_name' => 'ABVHPS Flutter App (Pixel 8)',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'account_type' => 'admin',
                    'profile'      => [
                        'id'    => $this->adminUser->id,
                        'name'  => 'Central Admin',
                        'email' => 'admin@abvhps.org',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.token'));

        // Verify sensitive fields are NOT in JSON
        $this->assertArrayNotHasKey('password', $response->json('data.profile'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.profile'));

        // Verify web session was NOT created (API only)
        $this->assertGuest('web');
    }

    public function test_invalid_admin_password_returns_422(): void
    {
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email'       => 'admin@abvhps.org',
            'password'    => 'WrongPassword!',
            'device_name' => 'Flutter Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid administrator email or security credentials.',
            ]);
    }

    public function test_nonexistent_admin_email_returns_422(): void
    {
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email'       => 'nonexistent@abvhps.org',
            'password'    => 'AnyPassword123!',
            'device_name' => 'Flutter Test',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid administrator email or security credentials.',
            ]);
    }

    public function test_admin_login_rate_limiting(): void
    {
        RateLimiter::clear('api_admin_login:admin@abvhps.org|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/admin/login', [
                'email'       => 'admin@abvhps.org',
                'password'    => 'WrongPassword',
                'device_name' => 'Flutter Test',
            ]);
            $response->assertStatus(422);
        }

        // 6th attempt throttled
        $response = $this->postJson('/api/v1/auth/admin/login', [
            'email'       => 'admin@abvhps.org',
            'password'    => 'WrongPassword',
            'device_name' => 'Flutter Test',
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_token_abilities_and_me_endpoint(): void
    {
        $token = $this->adminUser->createToken('Mobile Test', [
            'mobile',
            'account:admin',
            'admin:profile',
            'admin:dashboard',
        ])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data'    => [
                    'account_type' => 'admin',
                    'profile'      => [
                        'id'    => $this->adminUser->id,
                        'name'  => 'Central Admin',
                        'email' => 'admin@abvhps.org',
                    ],
                    'capabilities' => [
                        'is_admin'          => true,
                        'can_manage_system' => true,
                    ],
                ],
            ]);
    }

    public function test_admin_token_cannot_access_volunteer_or_member_protected_endpoints(): void
    {
        $token = $this->adminUser->createToken('Mobile Test', [
            'mobile',
            'account:admin',
            'admin:profile',
        ])->plainTextToken;

        // Volunteer dashboard blocked
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/volunteer/dashboard')
            ->assertStatus(403);

        // Member profile blocked
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/member/profile')
            ->assertStatus(403);
    }

    public function test_admin_logout_and_logout_all(): void
    {
        $token1 = $this->adminUser->createToken('Device 1', ['mobile', 'account:admin'])->plainTextToken;
        $token2 = $this->adminUser->createToken('Device 2', ['mobile', 'account:admin'])->plainTextToken;

        $this->assertEquals(2, $this->adminUser->tokens()->count());

        // Single device logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(1, $this->adminUser->fresh()->tokens()->count());

        // Logout all
        $response = $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->postJson('/api/v1/auth/logout-all');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $this->adminUser->fresh()->tokens()->count());
    }
}
