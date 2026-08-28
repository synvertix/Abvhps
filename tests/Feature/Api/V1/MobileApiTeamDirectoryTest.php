<?php

namespace Tests\Feature\Api\V1;

use App\Models\Membership;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiTeamDirectoryTest extends TestCase
{
    use RefreshDatabase;

    private function createVolunteer(array $attributes = []): Volunteer
    {
        static $seq = 1;
        $idStr = str_pad((string)$seq, 6, '0', STR_PAD_LEFT);
        $seq++;

        $defaults = [
            'membership_id'             => '100000' . $idStr,
            'volunteer_id'              => 'V' . substr($idStr, -5),
            'volunteer_login_id'        => 'V' . substr($idStr, -5),
            'phone'                     => '98' . str_pad((string)$seq, 8, '0', STR_PAD_LEFT),
            'email'                     => "volunteer_{$seq}@example.com",
            'qualification'             => 'Graduate',
            'voter_id_number'           => "VTR{$idStr}",
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Volunteer Name',
            'account_number'            => '123456789' . $seq,
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Main Branch',
            'nominee_name'              => 'Nominee',
            'nominee_relation'          => 'Brother',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('Secret123!'),
            'status'                    => 'approved',
            'is_active'                 => true,
            'cadre'                     => 'Volunteer',
            'cadre_level'               => 'volunteer',
        ];

        return Volunteer::create(array_merge($defaults, $attributes));
    }

    public function test_team_endpoint_returns_only_approved_volunteers_with_public_allowlist(): void
    {
        Membership::create([
            'membership_id'   => '100000000001',
            'full_name'       => 'Venkatesh Rao',
            'phone'           => '9876543210',
            'email'           => 'venkatesh@example.com',
            'aadhaar_no'      => '123456789012',
            'state'           => 'Andhra Pradesh',
            'district'        => 'Kadapa',
            'mandal'          => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddy Palli',
            'photo_path'      => 'members/venkat.jpg',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        $this->createVolunteer([
            'membership_id'      => '100000000001',
            'volunteer_id'       => 'VOL-1001',
            'volunteer_login_id' => 'VOL-1001',
            'phone'              => '9876543210',
            'email'              => 'venkatesh@example.com',
            'voter_id_number'    => 'VTR1000001',
            'status'             => 'approved',
            'is_active'          => true,
            'cadre'              => 'Mandal President',
            'cadre_level'        => 'mandal_president',
            'state'              => 'Andhra Pradesh',
            'district'           => 'Kadapa',
            'mandal'             => 'Porumamilla',
            'grama_panchayat'    => 'Akkalareddy Palli',
        ]);

        $this->createVolunteer([
            'membership_id'      => '100000000002',
            'volunteer_id'       => 'VOL-1002',
            'volunteer_login_id' => 'VOL-1002',
            'status'             => 'pending',
            'is_active'          => false,
        ]);

        $response = $this->getJson(route('api.v1.team'));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.volunteer_id', 'VOL-1001')
            ->assertJsonPath('data.0.name', 'Venkatesh Rao')
            ->assertJsonPath('data.0.cadre_label', 'Mandal President')
            ->assertJsonPath('data.0.state', 'Andhra Pradesh')
            ->assertJsonPath('data.0.district', 'Kadapa');

        // Zero Private Field Leaks Verification
        $rawJson = $response->getContent();
        $this->assertStringNotContainsString('9876543210', $rawJson);
        $this->assertStringNotContainsString('venkatesh@example.com', $rawJson);
        $this->assertStringNotContainsString('123456789012', $rawJson);
        $this->assertStringNotContainsString('VTR1000001', $rawJson);
        $this->assertStringNotContainsString('Secret123!', $rawJson);
        $this->assertStringNotContainsString('password', $rawJson);
    }

    public function test_team_endpoint_filters_by_district_and_search_query(): void
    {
        Membership::create([
            'membership_id'   => '100000000010',
            'full_name'       => 'Suresh Reddy',
            'phone'           => '9000000001',
            'district'        => 'Kadapa',
            'state'           => 'Andhra Pradesh',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        $this->createVolunteer([
            'membership_id'      => '100000000010',
            'volunteer_id'       => 'VOL-2001',
            'volunteer_login_id' => 'VOL-2001',
            'status'             => 'approved',
            'is_active'          => true,
            'district'           => 'Kadapa',
            'state'              => 'Andhra Pradesh',
        ]);

        Membership::create([
            'membership_id'   => '100000000011',
            'full_name'       => 'Anil Kumar',
            'phone'           => '9000000002',
            'district'        => 'Chittoor',
            'state'           => 'Andhra Pradesh',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        $this->createVolunteer([
            'membership_id'      => '100000000011',
            'volunteer_id'       => 'VOL-2002',
            'volunteer_login_id' => 'VOL-2002',
            'status'             => 'approved',
            'is_active'          => true,
            'district'           => 'Chittoor',
            'state'              => 'Andhra Pradesh',
        ]);

        $filterRes = $this->getJson(route('api.v1.team', ['district' => 'Kadapa']));
        $filterRes->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Suresh Reddy');

        $searchRes = $this->getJson(route('api.v1.team', ['search' => 'Anil']));
        $searchRes->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Anil Kumar');
    }
}
