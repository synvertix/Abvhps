<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Volunteer;
use App\Models\Membership;

class MobileApiSecurityAndIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setupVolunteer(): array
    {
        $member = Membership::create([
            'membership_id' => '123456789012',
            'phone'         => '9876543210',
            'full_name'     => 'Volunteer Rama',
            'is_completed'  => true,
            'state'         => 'Telangana',
            'district'      => 'Hyderabad',
        ]);

        $volunteer = Volunteer::create([
            'membership_id'             => $member->membership_id,
            'volunteer_id'              => '200001',
            'volunteer_login_id'        => '200001',
            'phone'                     => '9876543210',
            'email'                     => 'rama.vol@example.com',
            'qualification'             => 'Post Graduate',
            'voter_id_number'           => 'XYZ9876543',
            'bank_name'                 => 'HDFC Bank',
            'account_holder_name'       => 'Volunteer Rama',
            'account_number'            => '987654321012',
            'ifsc_code'                 => 'HDFC0001234',
            'branch_name'               => 'Hyderabad Main',
            'nominee_name'              => 'Lakshmi',
            'nominee_relation'          => 'Mother',
            'nominee_phone'             => '9876543212',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('secret123'),
            'status'                    => 'approved',
            'is_active'                 => true,
            'must_change_password'      => false,
            'cadre'                     => 'Volunteer',
            'cadre_level'               => 'volunteer',
            'geo_mapping_status'        => 'verified',
        ]);

        $token = $volunteer->createToken('Device 1', [
            'mobile',
            'account:volunteer',
            'volunteer:profile',
            'volunteer:dashboard',
        ])->plainTextToken;

        return [$volunteer, $token];
    }

    protected function setupMember(): array
    {
        $member = Membership::create([
            'membership_id' => '987654321098',
            'phone'         => '9123456789',
            'full_name'     => 'Member Krishna',
            'is_completed'  => true,
            'state'         => 'Andhra Pradesh',
            'district'      => 'Krishna',
            'payment_status'=> 'success',
            'payment_amount'=> 100.00,
        ]);

        $token = $member->createToken('Device 2', [
            'mobile',
            'account:member',
            'member:profile',
            'member:card',
        ])->plainTextToken;

        return [$member, $token];
    }

    public function test_volunteer_token_cannot_access_member_endpoints(): void
    {
        [$volunteer, $volToken] = $this->setupVolunteer();

        $response = $this->withHeader('Authorization', 'Bearer ' . $volToken)
            ->getJson('/api/v1/member/profile');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);

        $cardResponse = $this->withHeader('Authorization', 'Bearer ' . $volToken)
            ->getJson('/api/v1/member/card');

        $cardResponse->assertStatus(403);
    }

    public function test_member_token_cannot_access_volunteer_endpoints(): void
    {
        [$member, $memberToken] = $this->setupMember();

        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->getJson('/api/v1/volunteer/profile');

        $profileResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
            ]);

        $dashResponse = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->getJson('/api/v1/volunteer/dashboard');

        $dashResponse->assertStatus(403);
    }

    public function test_neither_volunteer_nor_member_token_grants_admin_access(): void
    {
        [$volunteer, $volToken] = $this->setupVolunteer();
        [$member, $memberToken] = $this->setupMember();

        // Web admin route should reject unauthenticated web session (redirect or 401/403)
        $volAdmin = $this->withHeader('Authorization', 'Bearer ' . $volToken)
            ->get('/admin');
        $this->assertTrue(in_array($volAdmin->status(), [302, 401, 403, 404]));

        $memAdmin = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->get('/admin');
        $this->assertTrue(in_array($memAdmin->status(), [302, 401, 403, 404]));
    }

    public function test_multi_device_tokens_and_independent_logout(): void
    {
        [$volunteer, $tokenA] = $this->setupVolunteer();

        // Create second token on Device B
        $tokenB = $volunteer->createToken('Device 2', [
            'mobile',
            'account:volunteer',
            'volunteer:profile',
        ])->plainTextToken;

        // Logout on Device A
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);

        // Reset auth guard state and check Device A token is now revoked (401)
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/me')
            ->assertStatus(401);

        // Reset auth guard state and check Device B token is STILL VALID
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/v1/me')
            ->assertStatus(200);

        // Logout-All on Device B revokes all remaining tokens
        $logoutAllResponse = $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->postJson('/api/v1/auth/logout-all');

        $logoutAllResponse->assertStatus(200);

        // Device B token is now also 401
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $tokenB)
            ->getJson('/api/v1/me')
            ->assertStatus(401);
    }

    public function test_privacy_sensitive_fields_are_never_exposed_in_api_json(): void
    {
        [$volunteer, $volToken] = $this->setupVolunteer();
        [$member, $memberToken] = $this->setupMember();

        $volResponse = $this->withHeader('Authorization', 'Bearer ' . $volToken)
            ->getJson('/api/v1/volunteer/profile');

        $volJson = $volResponse->json('data');
        $this->assertIsArray($volJson);
        $this->assertArrayNotHasKey('password', $volJson);
        $this->assertArrayNotHasKey('remember_token', $volJson);
        $this->assertArrayNotHasKey('bank_name', $volJson);
        $this->assertArrayNotHasKey('account_number', $volJson);
        $this->assertArrayNotHasKey('ifsc_code', $volJson);

        \Illuminate\Support\Facades\Auth::forgetGuards();
        $memResponse = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->getJson('/api/v1/member/profile');

        $memResponse->assertStatus(200);
        $memJson = $memResponse->json('data');
        $this->assertIsArray($memJson);
        $this->assertArrayNotHasKey('aadhaar_number', $memJson);
        $this->assertArrayNotHasKey('payment_id', $memJson);
        $this->assertArrayNotHasKey('payment_order_id', $memJson);
    }

    public function test_token_expiration_behavior_after_configured_ttl(): void
    {
        // Freeze time at current moment
        $startTime = \Carbon\Carbon::create(2026, 8, 27, 12, 0, 0);
        \Carbon\Carbon::setTestNow($startTime);

        [$member, $token] = $this->setupMember();

        // 1. Immediately valid
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertStatus(200);

        // 2. Advance 89 days (less than 90-day TTL) -> still valid
        \Carbon\Carbon::setTestNow($startTime->copy()->addDays(89));
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertStatus(200);

        // 3. Advance to 90 days + 1 minute (exceeds 129,600 minutes) -> expired (401)
        \Carbon\Carbon::setTestNow($startTime->copy()->addDays(90)->addMinute());
        \Illuminate\Support\Facades\Auth::forgetGuards();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me')
            ->assertStatus(401);

        // Reset test time
        \Carbon\Carbon::setTestNow();
    }

    public function test_exact_token_abilities_verification_for_all_account_types(): void
    {
        [$volunteer, $volToken] = $this->setupVolunteer();
        [$member, $memToken] = $this->setupMember();

        // Member token abilities
        $memTokenRecord = $member->tokens()->first();
        $this->assertEquals([
            'mobile',
            'account:member',
            'member:profile',
            'member:card',
        ], $memTokenRecord->abilities);

        // Normal volunteer token abilities
        $volTokenRecord = $volunteer->tokens()->first();
        $this->assertEquals([
            'mobile',
            'account:volunteer',
            'volunteer:profile',
            'volunteer:dashboard',
        ], $volTokenRecord->abilities);

        // Restricted volunteer token abilities
        $restrictedVolunteer = Volunteer::create([
            'membership_id'             => '123456789019',
            'volunteer_id'              => '200099',
            'volunteer_login_id'        => '200099',
            'phone'                     => '9876543299',
            'email'                     => 'vol99@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'XYZ9876599',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Vol 99',
            'account_number'            => '987654329999',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Guntur',
            'nominee_name'              => 'Mother',
            'nominee_relation'          => 'Mother',
            'nominee_phone'             => '9876543212',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('secret123'),
            'status'                    => 'approved',
            'is_active'                 => true,
            'must_change_password'      => true,
            'cadre'                     => 'Volunteer',
            'cadre_level'               => 'volunteer',
            'geo_mapping_status'        => 'verified',
        ]);

        $resLogin = $this->postJson('/api/v1/auth/volunteer/login', [
            'login_id'    => '200099',
            'password'    => 'secret123',
            'device_name' => 'Restricted Device',
        ]);
        $resLogin->assertStatus(200);

        $restTokenRecord = $restrictedVolunteer->tokens()->first();
        $this->assertEquals([
            'mobile',
            'account:volunteer',
            'volunteer:change-password',
        ], $restTokenRecord->abilities);
    }
}

