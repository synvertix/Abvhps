<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use Laravel\Sanctum\Sanctum;

class AdminMobileVolunteerTest extends TestCase
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

    public function test_admin_can_fetch_volunteers_list_and_stats(): void
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

        Volunteer::create([
            'membership_id'             => $m->membership_id,
            'volunteer_id'              => '100001',
            'email'                     => 'volunteer1@abvhps.org',
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

        $response = $this->getJson('/api/v1/admin/volunteers');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.total_records', 1)
            ->assertJsonPath('data.0.volunteer_id', '100001');
    }

    public function test_admin_can_update_volunteer_cadre_and_status(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        $v = Volunteer::create([
            'membership_id'             => '123412341234',
            'email'                     => 'volunteer2@abvhps.org',
            'phone'                     => '9111222333',
            'cadre'                     => 'Volunteer',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'XYZ9876543',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Volunteer 2',
            'account_number'            => '1234567891',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Hyderabad',
            'nominee_name'              => 'Nominee',
            'nominee_relation'          => 'Father',
            'nominee_phone'             => '9876543212',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'status'                    => 'pending',
            'is_active'                 => false,
        ]);

        $response = $this->postJson("/api/v1/admin/volunteers/{$v->id}/cadre", [
            'status'      => 'approved',
            'cadre_level' => 'volunteer',
            'cadre'       => 'District Volunteer Leader',
            'locality'    => 'Hyderabad District',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('volunteers', [
            'id'     => $v->id,
            'status' => 'approved',
            'cadre'  => 'District Volunteer Leader',
        ]);
    }
}
