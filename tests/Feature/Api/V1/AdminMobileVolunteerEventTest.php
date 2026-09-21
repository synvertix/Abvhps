<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerEvent;
use App\Models\Membership;
use Laravel\Sanctum\Sanctum;

class AdminMobileVolunteerEventTest extends TestCase
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

    public function test_admin_can_fetch_volunteer_events_list_and_stats(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        $m = Membership::create([
            'membership_id'  => '999988887777',
            'full_name'      => 'BHARAT KUMAR',
            'phone'          => '9888777666',
            'payment_status' => 'success',
            'is_completed'   => true,
        ]);

        $v = Volunteer::create([
            'membership_id'             => $m->membership_id,
            'volunteer_id'              => '100001',
            'email'                     => 'vol@abvhps.org',
            'phone'                     => '9888777666',
            'cadre'                     => 'Mandal President',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Bharat Kumar',
            'account_number'            => '1234567890',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Hyderabad',
            'nominee_name'              => 'Nominee',
            'nominee_relation'          => 'Mother',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'status'                    => 'approved',
            'is_active'                 => true,
        ]);

        VolunteerEvent::create([
            'volunteer_id' => $v->id,
            'title'        => 'Grama Seva Camp',
            'event_type'   => 'Community Service',
            'event_date'   => now()->format('Y-m-d'),
            'venue'        => 'Village GP Center',
            'district'     => 'HYDERABAD',
            'mandal'       => 'HYDERABAD',
            'status'       => 'completed',
        ]);

        $response = $this->getJson('/api/v1/admin/volunteer-events');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.total_events', 1)
            ->assertJsonPath('data.0.title', 'Grama Seva Camp');
    }
}
