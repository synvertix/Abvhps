<?php

namespace Tests\Feature\Api\V1;

use App\Models\ExamApplication;
use App\Models\ExamSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileExamTest extends TestCase
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

    public function test_admin_can_manage_exam_cycles()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $response = $this->postJson('/api/v1/admin/exams', [
            'exam_title'      => 'SANATHANA DHARMA EXAM 2026',
            'exam_type'       => 'State Level',
            'exam_date_time'  => '2026-10-15 10:00:00',
            'application_fee' => 100,
            'status'          => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('exam_settings', ['exam_title' => 'SANATHANA DHARMA EXAM 2026']);
    }
}
