<?php

namespace Tests\Feature\Api\V1;

use App\Models\TaxCertificate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileCertificateTest extends TestCase
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

    public function test_admin_can_toggle_tax_certificate_visibility()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $cert = TaxCertificate::create([
            'title'            => '12A EXEMPTION CERTIFICATE',
            'certificate_type' => 'Section 12A',
            'document_number'  => '12A-TEST-001',
            'file_path'        => 'certifications/12A.pdf',
            'is_active'        => true,
        ]);

        $response = $this->postJson("/api/v1/admin/tax-certificates/{$cert->id}/toggle");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('tax_certificates', ['id' => $cert->id, 'is_active' => false]);
    }
}
