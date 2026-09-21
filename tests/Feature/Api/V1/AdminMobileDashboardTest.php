<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Services\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMobileDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Commander Admin',
            'email' => 'admin@abvhps.org',
            'password' => Hash::make('AdminSecret123!'),
        ]);

        $tokenResult = $this->adminUser->createToken('mobile-admin-test', [
            'mobile',
            'account:admin',
            'admin:profile',
            'admin:dashboard',
        ]);
        $this->adminToken = $tokenResult->plainTextToken;
    }

    private static int $volCount = 1;

    protected function createVolunteer(Membership $member, array $attributes = []): Volunteer
    {
        $count = self::$volCount++;
        return Volunteer::create(array_merge([
            'membership_id'             => $member->membership_id,
            'volunteer_id'              => (string)(100000 + $count),
            'volunteer_login_id'        => (string)(100000 + $count),
            'phone'                     => $member->phone,
            'email'                     => "vol{$count}@example.com",
            'qualification'             => 'Graduate',
            'voter_id_number'           => "ABC123456{$count}",
            'bank_name'                 => 'SBI',
            'account_holder_name'       => $member->full_name,
            'account_number'            => "123456789{$count}",
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Main',
            'nominee_name'              => 'Nominee',
            'nominee_relation'          => 'Family',
            'nominee_phone'             => '9876543299',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('VolSecret123!'),
            'status'                    => 'approved',
            'must_change_password'      => false,
        ], $attributes));
    }

    public function test_guest_is_rejected_with_401(): void
    {
        $response = $this->getJson('/api/v1/admin/dashboard');
        $response->assertStatus(401);
    }

    public function test_member_token_is_rejected_with_403(): void
    {
        $member = Membership::create([
            'membership_id' => '123456789012',
            'full_name' => 'Member Test',
            'phone' => '9876543210',
            'is_completed' => true,
            'status' => 'approved',
        ]);

        $memberToken = $member->createToken('member-token', ['mobile', 'account:member', 'member:profile', 'member:card'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_volunteer_token_is_rejected_with_403(): void
    {
        $member = Membership::create([
            'membership_id' => '123456789012',
            'full_name' => 'Volunteer Member',
            'phone' => '9876543210',
            'is_completed' => true,
            'status' => 'approved',
        ]);

        $volunteer = $this->createVolunteer($member);

        $volunteerToken = $volunteer->createToken('vol-token', ['mobile', 'account:volunteer', 'volunteer:profile', 'volunteer:dashboard'])->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $volunteerToken)
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_authorized_admin_receives_structured_dashboard_metrics(): void
    {
        // Seed test records
        $m1 = Membership::create([
            'membership_id' => '123456789012',
            'full_name' => 'Member Test 1',
            'phone' => '9876543210',
            'is_completed' => true,
        ]);

        $volMember = Membership::create([
            'membership_id' => '123456789013',
            'full_name' => 'Vol Member',
            'phone' => '9876543211',
            'is_completed' => true,
        ]);

        $this->createVolunteer($volMember, [
            'volunteer_id' => '100001',
            'volunteer_login_id' => '100001',
            'status' => 'approved',
        ]);

        $volMember2 = Membership::create([
            'membership_id' => '123456789014',
            'full_name' => 'Vol Member 2',
            'phone' => '9876543212',
            'is_completed' => true,
            'status' => 'approved',
        ]);

        $this->createVolunteer($volMember2, [
            'volunteer_id' => '100002',
            'volunteer_login_id' => '100002',
            'voter_id_number' => 'ABC1234568',
            'account_number' => '1234567891',
            'status' => 'pending',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'administrator' => [
                        'name' => 'Commander Admin',
                        'email' => 'admin@abvhps.org',
                    ],
                    'summary' => [
                        'total_profiles' => 3,
                        'volunteers' => 1,
                        'pending_actions' => 1, // 0 pending memberships + 1 pending volunteer
                    ],
                    'wings' => [
                        'central_base' => 3,
                    ],
                    'pending' => [
                        'memberships' => 0,
                        'volunteers' => 1,
                    ],
                    'system' => [
                        'application' => 'Running',
                        'database' => 'Connected',
                        'storage' => 'Writable',
                    ],
                ],
            ]);

        // Assert sensitive fields are completely absent
        $json = $response->json();
        $this->assertArrayNotHasKey('password', $json['data']['administrator']);
        $this->assertArrayNotHasKey('remember_token', $json['data']['administrator']);
    }

    public function test_web_and_api_dashboard_metric_parity(): void
    {
        $service = app(AdminDashboardService::class);
        $directMetrics = $service->getDashboardMetrics();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/v1/admin/dashboard');

        $response->assertStatus(200);
        $apiData = $response->json('data');

        // Compare exact equality between Service/Web base calculations and API payload
        $this->assertEquals((int)$directMetrics['stats']['total_members'], $apiData['summary']['total_profiles']);
        $this->assertEquals((int)$directMetrics['stats']['total_volunteers'], $apiData['summary']['volunteers']);
        $this->assertEquals((int)(($directMetrics['stats']['pending_memberships'] ?? 0) + ($directMetrics['stats']['pending_volunteers'] ?? 0)), $apiData['summary']['pending_actions']);
        $this->assertEquals((float)$directMetrics['stats']['total_funds_raised'], $apiData['summary']['funds_raised']);

        $this->assertEquals((int)$directMetrics['stats']['total_exams'], $apiData['exams']['total']);
        $this->assertEquals((int)$directMetrics['stats']['total_blogs'], $apiData['content']['blogs']);
    }
}
