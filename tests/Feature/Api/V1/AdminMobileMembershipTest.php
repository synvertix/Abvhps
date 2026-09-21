<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Membership;
use Laravel\Sanctum\Sanctum;

class AdminMobileMembershipTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'name'     => 'Commander Admin',
            'email'    => 'admin@abvhps.org',
            'password' => \Illuminate\Support\Facades\Hash::make('AdminSecret123!'),
        ]);
    }

    private function createMemberUser(): User
    {
        return User::create([
            'name'     => 'Member User',
            'email'    => 'member@abvhps.org',
            'password' => \Illuminate\Support\Facades\Hash::make('MemberSecret123!'),
        ]);
    }

    public function test_guest_cannot_access_memberships_api(): void
    {
        $response = $this->getJson('/api/v1/admin/memberships');
        $response->assertStatus(401);
    }

    public function test_member_cannot_access_admin_memberships_api(): void
    {
        $memberUser = $this->createMemberUser();
        Sanctum::actingAs($memberUser, ['member:profile']);

        $response = $this->getJson('/api/v1/admin/memberships');
        $response->assertStatus(403);
    }

    public function test_admin_can_fetch_approved_memberships_list(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        Membership::create([
            'membership_id'     => '922493121520',
            'full_name'         => 'RAMA RAO',
            'phone'             => '9989980055',
            'payment_status'    => 'success',
            'is_completed'      => true,
            'district'          => 'HYDERABAD',
            'grama_panchayat'   => 'CENTRAL GP',
            'mandal'            => 'HYDERABAD',
            'state'             => 'TELANGANA',
        ]);

        $response = $this->getJson('/api/v1/admin/memberships');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.full_name', 'RAMA RAO');
    }

    public function test_admin_can_search_memberships(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        Membership::create([
            'membership_id'  => '111122223333',
            'full_name'      => 'SITA DEVI',
            'phone'          => '9876543210',
            'payment_status' => 'success',
            'is_completed'   => true,
            'district'       => 'VIJAYAWADA',
        ]);

        Membership::create([
            'membership_id'  => '444455556666',
            'full_name'      => 'LAKSHMAN',
            'phone'          => '9123456789',
            'payment_status' => 'success',
            'is_completed'   => true,
            'district'       => 'GUNTUR',
        ]);

        $response = $this->getJson('/api/v1/admin/memberships?search=SITA');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'SITA DEVI');
    }

    public function test_admin_can_fetch_pending_memberships(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        Membership::create([
            'phone'                => '9900112233',
            'payment_status'       => 'success',
            'is_completed'         => false,
            'payment_id'           => 'pay_test_123',
            'payment_completed_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/memberships/pending');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.phone', '9900112233');
    }
}
