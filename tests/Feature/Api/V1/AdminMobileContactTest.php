<?php

namespace Tests\Feature\Api\V1;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminMobileContactTest extends TestCase
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

    public function test_admin_can_fetch_and_update_contact_messages()
    {
        $admin = $this->createAdminUser();
        Sanctum::actingAs($admin, ['account:admin', 'admin:dashboard']);

        $msg = ContactMessage::create([
            'name'    => 'DEVOTEE INQUIRER',
            'email'   => 'inquirer@example.com',
            'subject' => 'Volunteering Query',
            'message' => 'How can I volunteer in my local Mandal?',
            'status'  => 'unread',
        ]);

        $response = $this->postJson("/api/v1/admin/contacts/{$msg->id}/status", [
            'status'      => 'read',
            'admin_notes' => 'Replied via phone call.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_messages', ['id' => $msg->id, 'status' => 'read']);
    }
}
