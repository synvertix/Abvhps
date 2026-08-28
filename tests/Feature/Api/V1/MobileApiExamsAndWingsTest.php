<?php

namespace Tests\Feature\Api\V1;

use App\Models\ExamApplication;
use App\Models\ExamSetting;
use App\Models\Membership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiExamsAndWingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_exams_index_returns_notice_board_cycles(): void
    {
        $exam = ExamSetting::create([
            'exam_title'           => 'Sanathana Dharma Pratibha Exam 2026',
            'exam_type'            => 'both',
            'exam_date_time'       => '2026-11-15 10:00:00',
            'exam_center_location' => 'Sri Rama Mandiram, Kadapa',
            'application_fee'      => 41.00,
            'status'               => 'active',
            'syllabus_pdf_path'    => 'exams/syllabus/sd_exam_2026.pdf',
            'prize_details_json'   => json_encode(['1st Prize: ₹25,000', '2nd Prize: ₹15,000']),
        ]);

        $response = $this->getJson(route('api.v1.exams.index'));
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $exam->id)
            ->assertJsonPath('data.0.exam_title', 'Sanathana Dharma Pratibha Exam 2026')
            ->assertJsonPath('data.0.exam_type_label', 'Both (Theory + MCQ)')
            ->assertJsonPath('data.0.prizes.0', '1st Prize: ₹25,000');
    }

    public function test_winners_endpoint_requires_both_published_status_and_winners_wall_flag(): void
    {
        $exam = ExamSetting::create([
            'exam_title'           => 'Sanathana Dharma State Exam',
            'exam_type'            => 'theory',
            'exam_date_time'       => '2026-05-10 10:00:00',
            'exam_center_location' => 'Tirupati',
            'application_fee'      => 41.00,
            'status'               => 'completed',
            'syllabus_pdf_path'    => 'exams/syllabus/sd_exam.pdf',
            'prize_details_json'   => json_encode(['Gold Medal']),
        ]);

        // Published winner -> Should be returned
        $winnerPublished = ExamApplication::create([
            'exam_setting_id'           => $exam->id,
            'email'                     => 'winner@example.com',
            'mobile'                    => '9876543210',
            'full_name'                 => 'Pooja Sharma',
            'hall_ticket_number'        => '10000000001',
            'payment_status'            => 'success',
            'marks_obtained'            => 98,
            'total_marks'               => 100,
            'grade'                     => 'A+',
            'result_status'             => 'passed',
            'winner_rank'               => 1,
            'show_on_winners_wall'      => true,
            'result_publication_status' => 'published',
            'prize_title_won'           => 'Gold Medal & ₹25,000 Cash Award',
        ]);

        // Draft winner -> MUST NOT BE RETURNED
        ExamApplication::create([
            'exam_setting_id'           => $exam->id,
            'email'                     => 'draft@example.com',
            'mobile'                    => '9876543211',
            'full_name'                 => 'Secret Draft Winner',
            'hall_ticket_number'        => '10000000002',
            'payment_status'            => 'success',
            'marks_obtained'            => 95,
            'total_marks'               => 100,
            'winner_rank'               => 2,
            'show_on_winners_wall'      => true,
            'result_publication_status' => 'draft',
        ]);

        $response = $this->getJson(route('api.v1.exams.winners'));
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'Pooja Sharma')
            ->assertJsonPath('data.0.winner_rank', 1)
            ->assertJsonPath('data.0.prize_title_won', 'Gold Medal & ₹25,000 Cash Award');

        $this->assertStringNotContainsString('Secret Draft Winner', (string) $response->getContent());
    }

    public function test_result_search_suppresses_draft_results_and_validates_11_digit_ticket(): void
    {
        $exam = ExamSetting::create([
            'exam_title'           => 'Dharma Pariksha 2026',
            'exam_type'            => 'theory',
            'exam_date_time'       => '2026-08-01 10:00:00',
            'exam_center_location' => 'Kurnool',
            'application_fee'      => 41.00,
            'status'               => 'completed',
            'syllabus_pdf_path'    => 'exams/syllabus/dharma_pariksha.pdf',
            'prize_details_json'   => json_encode([]),
        ]);

        $publishedApp = ExamApplication::create([
            'exam_setting_id'           => $exam->id,
            'email'                     => 'pooja@example.com',
            'mobile'                    => '9876543210',
            'aadhaar_no'                => '123456789012',
            'full_name'                 => 'Pooja Sharma',
            'hall_ticket_number'        => '12345678901',
            'payment_status'            => 'success',
            'marks_obtained'            => 92,
            'total_marks'               => 100,
            'grade'                     => 'A+',
            'result_status'             => 'passed',
            'result_publication_status' => 'published',
        ]);

        $draftApp = ExamApplication::create([
            'exam_setting_id'           => $exam->id,
            'email'                     => 'draft@example.com',
            'mobile'                    => '9876543212',
            'full_name'                 => 'Draft Student',
            'hall_ticket_number'        => '99999999999',
            'payment_status'            => 'success',
            'marks_obtained'            => 88,
            'total_marks'               => 100,
            'result_publication_status' => 'draft',
        ]);

        // 1. Invalid ticket format
        $invalidRes = $this->postJson(route('api.v1.exams.search'), ['hall_ticket_number' => '123']);
        $invalidRes->assertStatus(422);

        // 2. Published ticket search -> Success with safe fields only
        $searchRes = $this->postJson(route('api.v1.exams.search'), ['hall_ticket_number' => '12345678901']);
        $searchRes->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.full_name', 'Pooja Sharma')
            ->assertJsonPath('data.marks_obtained', 92)
            ->assertJsonPath('data.percentage', 92)
            ->assertJsonPath('data.grade', 'A+')
            ->assertJsonPath('data.status', 'passed');

        // Zero Private Field Leaks
        $rawJson = (string) $searchRes->getContent();
        $this->assertStringNotContainsString('pooja@example.com', $rawJson);
        $this->assertStringNotContainsString('9876543210', $rawJson);
        $this->assertStringNotContainsString('123456789012', $rawJson);

        // 3. Draft ticket search -> Suppressed
        $draftRes = $this->postJson(route('api.v1.exams.search'), ['hall_ticket_number' => '99999999999']);
        $draftRes->assertOk()
            ->assertJsonPath('success', false)
            ->assertJsonPath('is_draft', true);
        $this->assertStringNotContainsString('88', (string) $draftRes->getContent());
    }

    public function test_wings_endpoints_return_official_wing_data(): void
    {
        $listRes = $this->getJson(route('api.v1.wings.index'));
        $listRes->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('data.0.slug', 'rudrasena')
            ->assertJsonPath('data.1.slug', 'kala-brundam')
            ->assertJsonPath('data.2.slug', 'grama-seva-dal')
            ->assertJsonPath('data.3.slug', 'organic-farmers');

        $singleRes = $this->getJson(route('api.v1.wings.show', ['slug' => 'rudrasena']));
        $singleRes->assertOk()
            ->assertJsonPath('data.name', 'Rudrasena Dal')
            ->assertJsonPath('data.min_age', 24)
            ->assertJsonPath('data.max_age', 44);

        $invalidSlug = $this->getJson('/api/v1/wings/unknown-wing');
        $invalidSlug->assertNotFound();
    }

    public function test_rudrasena_eligibility_enforces_authoritative_24_to_44_age_rule(): void
    {
        // 1. Eligible Member (Age 30)
        $eligibleMember = Membership::create([
            'membership_id'   => '123456789001',
            'full_name'       => 'Anand Varma',
            'phone'           => '9876543201',
            'dob'             => now()->subYears(30)->format('Y-m-d'),
            'state'           => 'Andhra Pradesh',
            'district'        => 'Kadapa',
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        // 2. Ineligible Underage Member (Age 20)
        $underageMember = Membership::create([
            'membership_id'   => '123456789002',
            'full_name'       => 'Young Boy',
            'phone'           => '9876543202',
            'dob'             => now()->subYears(20)->format('Y-m-d'),
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        // 3. Ineligible Overage Member (Age 50)
        $overageMember = Membership::create([
            'membership_id'   => '123456789003',
            'full_name'       => 'Senior Devotee',
            'phone'           => '9876543203',
            'dob'             => now()->subYears(50)->format('Y-m-d'),
            'status'          => 'approved',
            'payment_status'  => 'success',
            'is_completed'    => true,
        ]);

        // Test Eligible Member
        $res1 = $this->postJson(route('api.v1.wings.rudrasena.verify'), ['membership_id' => '123456789001']);
        $res1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('eligible', true)
            ->assertJsonPath('age', 30)
            ->assertJsonPath('data.full_name', 'Anand Varma');

        // Test Underage Member
        $res2 = $this->postJson(route('api.v1.wings.rudrasena.verify'), ['membership_id' => '123456789002']);
        $res2->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('age', 20);
        $this->assertStringContainsString('24 to 44 years', (string) $res2->json('message'));

        // Test Overage Member
        $res3 = $this->postJson(route('api.v1.wings.rudrasena.verify'), ['membership_id' => '123456789003']);
        $res3->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('eligible', false)
            ->assertJsonPath('age', 50);

        // Test Nonexistent Membership ID
        $res4 = $this->postJson(route('api.v1.wings.rudrasena.verify'), ['membership_id' => '999999999999']);
        $res4->assertNotFound()
            ->assertJsonPath('eligible', false);
    }
}
