<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class AdminMobileRudrasenaTest extends TestCase
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

    public function test_admin_can_fetch_rudrasena_roster(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        DB::table('rudrasena_members')->insert([
            'membership_id'                 => '999900001111',
            'full_name'                     => 'RUDRA COMMANDER',
            'email'                         => 'rudra@abvhps.org',
            'mobile'                        => '9988776655',
            'dob'                           => '1995-05-15',
            'age'                           => 30,
            'gotram'                        => 'Siva Gotram',
            'blood_group'                   => 'O+',
            'nominee_name'                  => 'SITA COMMANDER',
            'nominee_relation'              => 'Wife',
            'nominee_age'                   => 28,
            'nominee_contact'               => '9876543210',
            'bank_holder_name'              => 'Rudra Commander',
            'bank_account_number'           => '123456789012',
            'bank_ifsc_code'                 => 'SBIN0001234',
            'bank_name_branch'              => 'SBI Main Branch',
            'document_health_declaration'   => 'health.jpg',
            'document_family_declaration'   => 'family.jpg',
            'document_id_proof'             => 'id.jpg',
            'document_bank_proof'           => 'bank.jpg',
            'volunteer_type'                => 'Field Commander',
            'assigned_cadder'               => 'Dal Leader',
            'assigned_locality'             => 'State HQ',
            'status'                        => 'pending',
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        $response = $this->getJson('/api/v1/admin/rudrasena');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'RUDRA COMMANDER');
    }

    public function test_rudrasena_approval_enforces_24_44_age_invariant(): void
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['admin:dashboard', 'account:admin']);

        // Insert too young member (18 years old)
        $youngId = DB::table('rudrasena_members')->insertGetId([
            'membership_id'                 => '111100001111',
            'full_name'                     => 'YOUNG CADET',
            'email'                         => 'young@abvhps.org',
            'mobile'                        => '9988776611',
            'dob'                           => now()->subYears(18)->format('Y-m-d'),
            'age'                           => 18,
            'gotram'                        => 'Siva Gotram',
            'blood_group'                   => 'O+',
            'nominee_name'                  => 'PARENT CADET',
            'nominee_relation'              => 'Father',
            'nominee_age'                   => 45,
            'nominee_contact'               => '9876543211',
            'bank_holder_name'              => 'Young Cadet',
            'bank_account_number'           => '123456789013',
            'bank_ifsc_code'                 => 'SBIN0001234',
            'bank_name_branch'              => 'SBI Main Branch',
            'document_health_declaration'   => 'health.jpg',
            'document_family_declaration'   => 'family.jpg',
            'document_id_proof'             => 'id.jpg',
            'document_bank_proof'           => 'bank.jpg',
            'assigned_cadder'               => 'Dal Member',
            'assigned_locality'             => 'Local GP',
            'status'                        => 'pending',
            'created_at'                    => now(),
            'updated_at'                    => now(),
        ]);

        $response = $this->postJson("/api/v1/admin/rudrasena/{$youngId}/status", [
            'status'            => 'verified',
            'assigned_cadder'   => 'Dal Member',
            'assigned_locality' => 'Local GP',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
