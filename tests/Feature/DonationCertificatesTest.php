<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Donation;
use App\Models\TaxCertificate;

class DonationCertificatesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Active/current compliance certificates visible on payment_status for paid donation
     */
    public function test_active_certificates_visible_for_paid_donation(): void
    {
        $today = now();

        // 1. Valid Active Certificate
        TaxCertificate::create([
            'title'            => '12A Income Tax Exemption',
            'certificate_type' => '12A',
            'document_number'  => 'AAETA1234F',
            'valid_from'       => $today->copy()->subMonths(6)->toDateString(),
            'valid_to'         => $today->copy()->addYears(2)->toDateString(),
            'file_path'        => 'certifications/12a_order.pdf',
            'is_active'        => true,
        ]);

        // 2. Expired Certificate
        TaxCertificate::create([
            'title'            => 'Expired Old 80G Certificate',
            'certificate_type' => '80G',
            'document_number'  => 'OLD80G',
            'valid_from'       => $today->copy()->subYears(3)->toDateString(),
            'valid_to'         => $today->copy()->subMonth()->toDateString(),
            'file_path'        => 'certifications/expired.pdf',
            'is_active'        => true,
        ]);

        // 3. Future Dated Certificate
        TaxCertificate::create([
            'title'            => 'Future CSR Approval',
            'certificate_type' => 'CSR-1',
            'document_number'  => 'CSR999',
            'valid_from'       => $today->copy()->addMonth()->toDateString(),
            'valid_to'         => $today->copy()->addYears(3)->toDateString(),
            'file_path'        => 'certifications/future.pdf',
            'is_active'        => true,
        ]);

        // 4. Inactive Certificate
        TaxCertificate::create([
            'title'            => 'Inactive Draft Certificate',
            'certificate_type' => 'FCRA',
            'valid_from'       => $today->copy()->subMonths(1)->toDateString(),
            'valid_to'         => $today->copy()->addYear()->toDateString(),
            'file_path'        => 'certifications/inactive.pdf',
            'is_active'        => false,
        ]);

        // Create Paid Donation
        $donation = Donation::create([
            'name'           => 'Devotee Donor',
            'contact'        => '9876543210',
            'phone'          => '9876543210',
            'email'          => 'donor@example.com',
            'amount'         => 5001.00,
            'payment_status' => 'paid',
            'payment_gateway'=> 'cashfree',
            'receipt_number' => 'ABVHPS-TEST-001',
            'paid_at'        => now(),
        ]);

        $response = $this->withSession(['authorized_donation_ids' => [$donation->id]])
            ->get(route('donations.status', $donation->id));

        $response->assertStatus(200);
        $response->assertSee('ABVHPS Compliance Certificates');
        $response->assertSee('12A Income Tax Exemption');
        $response->assertDontSee('Expired Old 80G Certificate');
        $response->assertDontSee('Future CSR Approval');
        $response->assertDontSee('Inactive Draft Certificate');
        $response->assertDontSee('Your 80G Certificate'); // Correct organization phrasing
    }

    /**
     * Test: Pending/Failed donation does NOT show compliance certificates section
     */
    public function test_pending_and_failed_donations_do_not_show_certificates(): void
    {
        TaxCertificate::create([
            'title'            => '12A Income Tax Exemption',
            'certificate_type' => '12A',
            'valid_from'       => now()->subMonth()->toDateString(),
            'valid_to'         => now()->addYear()->toDateString(),
            'file_path'        => 'certifications/12a.pdf',
            'is_active'        => true,
        ]);

        // Pending Donation
        $pendingDonation = Donation::create([
            'name'           => 'Devotee Donor',
            'contact'        => '9876543210',
            'phone'          => '9876543210',
            'amount'         => 1000.00,
            'payment_status' => 'pending',
            'payment_gateway'=> 'cashfree',
        ]);

        $response = $this->withSession(['authorized_donation_ids' => [$pendingDonation->id]])
            ->get(route('donations.status', $pendingDonation->id));

        $response->assertStatus(200);
        $response->assertDontSee('ABVHPS Compliance Certificates');
    }

    /**
     * Test: ₹ prefix UI markup alignment on donations page
     */
    public function test_donation_page_rupee_symbol_alignment_markup(): void
    {
        $response = $this->get('/donations');
        $response->assertStatus(200);

        // Wrapper must use flex alignment without absolute positioning hacks
        $response->assertSee('flex items-center w-full bg-gray-50 border-2 border-gray-200 rounded-2xl', false);
        $response->assertSee('aria-hidden="true"', false);
        $response->assertDontSee('absolute inset-y-0 left-0', false);
        $response->assertDontSee('pointer-events-none text-gray-500 font-black text-base pt-2', false);
    }
}
