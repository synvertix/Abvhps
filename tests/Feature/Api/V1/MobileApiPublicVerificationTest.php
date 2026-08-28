<?php

namespace Tests\Feature\Api\V1;

use App\Models\ExamApplication;
use App\Models\ExamSetting;
use App\Models\Membership;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiPublicVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_membership_qr_returns_allowlisted_public_status(): void
    {
        Membership::create([
            'membership_id'   => '100000000050',
            'full_name'       => 'Narasimha Murthy',
            'phone'           => '9988776655',
            'aadhaar_no'      => '987654321098',
            'state'           => 'Andhra Pradesh',
            'district'        => 'Kadapa',
            'mandal'          => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddy Palli',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
            'blood_group'     => 'O+',
        ]);

        $response = $this->getJson(route('api.v1.verify', [
            'type' => 'membership',
            'id'   => '100000000050',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_valid', true)
            ->assertJsonPath('entity_type', 'ABVHPS Life Member')
            ->assertJsonPath('name', 'Narasimha Murthy')
            ->assertJsonPath('status', 'ACTIVE & VERIFIED')
            ->assertJsonPath('is_approved', true);

        // Zero Private Field Leaks
        $rawJson = $response->getContent();
        $this->assertStringNotContainsString('9988776655', $rawJson);
        $this->assertStringNotContainsString('987654321098', $rawJson);
    }

    public function test_verify_volunteer_qr_returns_cadre_and_jurisdiction(): void
    {
        Membership::create([
            'membership_id'   => '100000000060',
            'full_name'       => 'Praveen Sharma',
            'phone'           => '9876543210',
            'state'           => 'Andhra Pradesh',
            'district'        => 'Kadapa',
            'mandal'          => 'Porumamilla',
            'grama_panchayat' => 'Akkalareddy Palli',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        Volunteer::create([
            'membership_id'             => '100000000060',
            'volunteer_id'              => 'VOL-3001',
            'volunteer_login_id'        => 'VOL-3001',
            'phone'                     => '9876543210',
            'email'                     => 'praveen@example.com',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'VTR3000001',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'Praveen Sharma',
            'account_number'            => '1234567890',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Main Branch',
            'nominee_name'              => 'Nominee',
            'nominee_relation'          => 'Brother',
            'nominee_phone'             => '9876543211',
            'document_declaration_path' => 'doc1.pdf',
            'document_voter_path'       => 'doc2.pdf',
            'document_bank_path'        => 'doc3.pdf',
            'password'                  => Hash::make('Pass123!'),
            'status'                    => 'approved',
            'is_active'                 => true,
            'cadre'                     => 'District President',
            'cadre_level'               => 'district_president',
            'district'                  => 'Kadapa',
            'state'                     => 'Andhra Pradesh',
        ]);

        $response = $this->getJson(route('api.v1.verify', [
            'type' => 'volunteer',
            'id'   => 'VOL-3001',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_valid', true)
            ->assertJsonPath('entity_type', 'Authorized Volunteer')
            ->assertJsonPath('name', 'Praveen Sharma')
            ->assertJsonPath('cadre', 'District President');
    }

    public function test_verify_exam_hall_ticket_returns_exam_and_schedule(): void
    {
        $exam = ExamSetting::create([
            'exam_title'           => 'State Dharma Test',
            'exam_type'            => 'theory',
            'exam_date_time'       => '2026-12-01 10:00:00',
            'exam_center_location' => 'Govt High School, Porumamilla',
            'application_fee'      => 41.00,
            'status'               => 'active',
            'syllabus_pdf_path'    => 'exams/syllabus/state_test.pdf',
            'prize_details_json'   => json_encode([]),
        ]);

        ExamApplication::create([
            'exam_setting_id'    => $exam->id,
            'email'              => 'student@example.com',
            'mobile'             => '9876543210',
            'full_name'          => 'Kalyan Kumar',
            'hall_ticket_number' => '11223344556',
            'payment_status'     => 'success',
        ]);

        $response = $this->getJson(route('api.v1.verify', [
            'type' => 'exam',
            'id'   => '11223344556',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_valid', true)
            ->assertJsonPath('name', 'Kalyan Kumar')
            ->assertJsonPath('cadre', 'State Dharma Test');
    }

    public function test_verify_organic_farmer_and_wings_groups(): void
    {
        DB::table('organic_farmers')->insert([
            'farmer_registration_id' => 'OF-100200',
            'membership_id'          => '100000000099',
            'farmer_name'            => 'Ramanaidu',
            'farmer_mobile'          => '9876543299',
            'status'                 => 'approved',
            'land_size_acres'        => 5.5,
            'water_source'           => 'Borewell',
            'indigenous_cows_count'  => 4,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        $response = $this->getJson(route('api.v1.verify', [
            'type' => 'organic-farmers',
            'id'   => 'OF-100200',
        ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('is_valid', true)
            ->assertJsonPath('entity_type', 'Organic Farmers Group')
            ->assertJsonPath('official_id', 'OF-100200');
    }

    public function test_verify_nonexistent_entity_returns_404(): void
    {
        $response = $this->getJson(route('api.v1.verify', [
            'type' => 'membership',
            'id'   => '999999999999',
        ]));

        $response->assertNotFound()
            ->assertJsonPath('is_valid', false);
    }
}
