<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Volunteer;
use App\Models\Membership;

class MobileApiVolunteerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function createApprovedVolunteer(array $attributes = []): Volunteer
    {
        $member = Membership::create([
            'membership_id' => '123456789012',
            'phone'         => '9876543210',
            'full_name'     => 'Sri Rama',
            'is_completed'  => true,
        ]);

        return Volunteer::create(array_merge([
            'membership_id'             => $member->membership_id,
            'volunteer_id'              => '100001',
            'volunteer_login_id'        => '100001',
            'phone'                     => '9876543210',
            'email'                     => 'rama@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Sri Rama',
            'account_number'            => '1234567890',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Guntur Main',
            'nominee_name'              => 'Sita',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('password123'),
            'status'                    => 'approved',
            'is_active'                 => true,
            'must_change_password'      => false,
            'cadre'                     => 'Volunteer',
            'cadre_level'               => 'volunteer',
            'geo_mapping_status'        => 'verified',
        ], $attributes));
    }

    public function test_approved_active_volunteer_can_login_and_receive_sanctum_token(): void
    {
        $volunteer = $this->createApprovedVolunteer();

        $response = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'account_type'          => 'volunteer',
                    'must_change_password'  => false,
                    'profile' => [
                        'volunteer_id' => '100001',
                        'full_name'    => 'Sri Rama',
                    ],
                ],
            ]);

        $token = $response->json('data.token');
        $this->assertNotEmpty($token);

        // Assert no password hash or internal secret in output
        $this->assertArrayNotHasKey('password', $response->json('data.profile'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data.profile'));
    }

    public function test_pending_volunteer_is_blocked_from_login(): void
    {
        $volunteer = $this->createApprovedVolunteer([
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_rejected_volunteer_is_blocked_from_login(): void
    {
        $volunteer = $this->createApprovedVolunteer([
            'status' => 'rejected',
        ]);

        $response = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_inactive_volunteer_is_blocked_from_login(): void
    {
        $volunteer = $this->createApprovedVolunteer([
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_invalid_password_returns_422(): void
    {
        $volunteer = $this->createApprovedVolunteer();

        $response = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'wrongpassword',
            'device_name' => 'Pixel 8',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The provided password is incorrect.',
            ]);
    }

    public function test_device_name_validation_on_volunteer_login(): void
    {
        $volunteer = $this->createApprovedVolunteer();

        // Missing device_name -> 422
        $res1 = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id' => '100001',
            'password' => 'password123',
        ]);
        $res1->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);

        // device_name > 100 chars -> 422
        $res2 = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => str_repeat('a', 101),
        ]);
        $res2->assertStatus(422)
            ->assertJsonValidationErrors(['device_name']);
    }

    public function test_must_change_password_flag_issues_restricted_token_and_blocks_dashboard(): void
    {
        $volunteer = $this->createApprovedVolunteer([
            'must_change_password' => true,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Samsung Galaxy Tab',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'must_change_password' => true,
                ],
            ]);

        $token = $loginResponse->json('data.token');

        // Verify token name matches device_name
        $tokenRecord = $volunteer->tokens()->latest('id')->first();
        $this->assertNotNull($tokenRecord);
        $this->assertEquals('Samsung Galaxy Tab', $tokenRecord->name);

        // Assert exact restricted token abilities
        $this->assertEquals([
            'mobile',
            'account:volunteer',
            'volunteer:change-password',
        ], $tokenRecord->abilities);

        $this->assertTrue($tokenRecord->can('volunteer:change-password'));
        $this->assertFalse($tokenRecord->can('volunteer:profile'));
        $this->assertFalse($tokenRecord->can('volunteer:dashboard'));

        // Accessing profile with restricted token should fail (403)
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/volunteer/profile');
        $profileResponse->assertStatus(403);

        // Accessing dashboard with restricted token should fail (403)
        $dashResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/volunteer/dashboard');
        $dashResponse->assertStatus(403)
            ->assertJson([
                'success'              => false,
                'must_change_password' => true,
            ]);

        // Calling change-password endpoint should succeed
        $pwResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/volunteer/change-password', [
                'current_password'          => 'password123',
                'new_password'              => 'NewStrongPassword@2026',
                'new_password_confirmation' => 'NewStrongPassword@2026',
                'device_name'               => 'Samsung Galaxy Tab',
            ]);

        $pwResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'must_change_password' => false,
                ],
            ]);

        $newToken = $pwResponse->json('data.token');
        $this->assertNotEmpty($newToken);

        // Old restricted token is now revoked (401)
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertStatus(401);

        // Assert replacement token has full volunteer abilities
        $newTokenRecord = $volunteer->tokens()->latest('id')->first();
        $this->assertNotNull($newTokenRecord);
        $this->assertEquals('Samsung Galaxy Tab', $newTokenRecord->name);
        $this->assertEquals([
            'mobile',
            'account:volunteer',
            'volunteer:profile',
            'volunteer:dashboard',
        ], $newTokenRecord->abilities);

        // Now dashboard access with new replacement token succeeds (200)
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $dashAfter = $this->withHeader('Authorization', 'Bearer ' . $newToken)
            ->getJson('/api/v1/volunteer/dashboard');

        $dashAfter->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    public function test_volunteer_multi_device_tokens_and_independent_logout(): void
    {
        $volunteer = $this->createApprovedVolunteer();

        // Login Device 1
        $login1 = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Workstation Mobile',
        ]);
        $token1 = $login1->json('data.token');

        // Login Device 2
        $login2 = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Field Tablet',
        ]);
        $token2 = $login2->json('data.token');

        // Check tokens exist and names correspond to devices
        $tokens = $volunteer->tokens()->get();
        $this->assertCount(2, $tokens);
        $this->assertTrue($tokens->pluck('name')->contains('Workstation Mobile'));
        $this->assertTrue($tokens->pluck('name')->contains('Field Tablet'));

        // Logout on Device 1
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $logout1 = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/v1/auth/logout');
        $logout1->assertStatus(200);

        // Token 1 revoked
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->getJson('/api/v1/me')
            ->assertStatus(401);

        // Token 2 remains valid
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token2)
            ->getJson('/api/v1/me')
            ->assertStatus(200);
    }

    public function test_admin_deactivating_volunteer_immediately_blocks_existing_token(): void
    {
        $volunteer = $this->createApprovedVolunteer();

        $loginResponse = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '100001',
            'password'    => 'password123',
            'device_name' => 'Pixel 8',
        ]);

        $token = $loginResponse->json('data.token');

        // Verify active token works
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/volunteer/profile')
            ->assertStatus(200);

        // Admin changes status to rejected in database
        $volunteer->status = 'rejected';
        $volunteer->save();

        // Existing token is immediately rejected on next request
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/volunteer/profile')
            ->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);
    }
}
