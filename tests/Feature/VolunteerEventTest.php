<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\VolunteerEvent;

class VolunteerEventTest extends TestCase
{
    use RefreshDatabase;

    protected Volunteer $volunteer1;
    protected Volunteer $volunteer2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create approved volunteer 1
        $member1 = Membership::create([
            'membership_id'  => '111122223333',
            'phone'          => '9876543210',
            'full_name'      => 'SRINIVASA RAO',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 1,
        ]);

        $this->volunteer1 = Volunteer::create([
            'membership_id'             => '111122223333',
            'phone'                     => '9876543210',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'email'                     => 'volunteer1@abvhps.org',
            'password'                  => Hash::make('password123'),
            'must_change_password'      => false,
            'status'                    => 'approved',
            'is_active'                 => true,
            'volunteer_id'              => '583214',
            'volunteer_login_id'        => '583214',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'SRINIVASA RAO',
            'account_number'            => '1122334455',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Badvel',
            'nominee_name'              => 'Lakshmi',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543219',
            'document_declaration_path' => 'vol1_decl.pdf',
            'document_voter_path'       => 'vol1_voter.pdf',
            'document_bank_path'        => 'vol1_bank.pdf',
        ]);

        // Create approved volunteer 2
        $member2 = Membership::create([
            'membership_id'  => '444455556666',
            'phone'          => '9876543211',
            'full_name'      => 'VENKATA SUBBAIAH',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 1,
        ]);

        $this->volunteer2 = Volunteer::create([
            'membership_id'             => '444455556666',
            'phone'                     => '9876543211',
            'qualification'             => 'Post Graduate',
            'voter_id_number'           => 'XYZ9876543',
            'email'                     => 'volunteer2@abvhps.org',
            'password'                  => Hash::make('password123'),
            'must_change_password'      => false,
            'status'                    => 'approved',
            'is_active'                 => true,
            'volunteer_id'              => '741905',
            'volunteer_login_id'        => '741905',
            'bank_name'                 => 'HDFC',
            'account_holder_name'       => 'VENKATA SUBBAIAH',
            'account_number'            => '9988776655',
            'ifsc_code'                 => 'HDFC0001234',
            'branch_name'               => 'Kadapa',
            'nominee_name'              => 'Sujatha',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543218',
            'document_declaration_path' => 'vol2_decl.pdf',
            'document_voter_path'       => 'vol2_voter.pdf',
            'document_bank_path'        => 'vol2_bank.pdf',
        ]);
    }

    public function test_unauthenticated_visitor_cannot_access_volunteer_events()
    {
        $response = $this->get('/volunteer/events');
        $response->assertRedirect('/volunteer/login');
    }

    public function test_volunteer_can_create_own_event_with_valid_event_type()
    {
        $response = $this->actingAs($this->volunteer1, 'volunteer')
            ->post('/volunteer/events', [
                'title'       => 'Free Annadanam Program at Badvel',
                'event_type'  => 'Annadanam',
                'description' => 'Serving holy prasadam to 500 devotees.',
                'event_date'  => now()->addDays(2)->format('Y-m-d'),
                'start_time'  => '10:00 AM',
                'end_time'    => '02:00 PM',
                'venue'       => 'Sri Rama Temple Hall',
                'village'     => 'Badvel',
                'mandal'      => 'Badvel',
                'district'    => 'YSR Kadapa',
                'state'       => 'Andhra Pradesh',
                'status'      => 'upcoming',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('volunteer_events', [
            'volunteer_id' => $this->volunteer1->id,
            'title'        => 'Free Annadanam Program at Badvel',
            'event_type'   => 'Annadanam',
            'status'       => 'upcoming',
        ]);
    }

    public function test_creating_event_with_arbitrary_event_type_is_rejected()
    {
        $response = $this->actingAs($this->volunteer1, 'volunteer')
            ->post('/volunteer/events', [
                'title'       => 'Invalid Event Type Injection',
                'event_type'  => 'Unapproved Arbitrary Hack',
                'event_date'  => now()->addDays(2)->format('Y-m-d'),
                'status'      => 'upcoming',
            ]);

        $response->assertSessionHasErrors(['event_type']);
        $this->assertDatabaseMissing('volunteer_events', [
            'title' => 'Invalid Event Type Injection',
        ]);
    }

    public function test_volunteer_can_view_own_event()
    {
        $event = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer1->id,
            'title'        => 'Goshala Seva Drive',
            'event_type'   => 'Goshala seva',
            'event_date'   => now()->format('Y-m-d'),
            'status'       => 'completed',
        ]);

        $response = $this->actingAs($this->volunteer1, 'volunteer')
            ->get("/volunteer/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertSee('Goshala Seva Drive');
    }

    public function test_volunteer_cannot_view_or_edit_another_volunteer_event()
    {
        $eventOfVol2 = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer2->id,
            'title'        => 'Volunteer 2 Private Camp',
            'event_type'   => 'Medical/service camp',
            'event_date'   => now()->addDays(5)->format('Y-m-d'),
            'status'       => 'upcoming',
        ]);

        // Vol 1 tries to view Vol 2's event
        $viewResponse = $this->actingAs($this->volunteer1, 'volunteer')
            ->get("/volunteer/events/{$eventOfVol2->id}");
        $viewResponse->assertStatus(403);

        // Vol 1 tries to edit Vol 2's event
        $editResponse = $this->actingAs($this->volunteer1, 'volunteer')
            ->get("/volunteer/events/{$eventOfVol2->id}/edit");
        $editResponse->assertStatus(403);

        // Vol 1 tries to update Vol 2's event
        $updateResponse = $this->actingAs($this->volunteer1, 'volunteer')
            ->put("/volunteer/events/{$eventOfVol2->id}", [
                'title'      => 'Malicious Hijack',
                'event_type' => 'Medical/service camp',
                'event_date' => now()->format('Y-m-d'),
                'status'     => 'upcoming',
            ]);
        $updateResponse->assertStatus(403);

        $this->assertDatabaseMissing('volunteer_events', [
            'id'    => $eventOfVol2->id,
            'title' => 'Malicious Hijack',
        ]);
    }

    public function test_volunteer_can_update_own_upcoming_event()
    {
        $event = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer1->id,
            'title'        => 'Original Seva Title',
            'event_type'   => 'Awareness program',
            'event_date'   => now()->addDays(3)->format('Y-m-d'),
            'status'       => 'upcoming',
        ]);

        $response = $this->actingAs($this->volunteer1, 'volunteer')
            ->put("/volunteer/events/{$event->id}", [
                'title'      => 'Updated Seva Title with New Timings',
                'event_type' => 'Awareness program',
                'event_date' => now()->addDays(4)->format('Y-m-d'),
                'start_time' => '11:00 AM',
                'status'     => 'upcoming',
            ]);

        $response->assertRedirect("/volunteer/events/{$event->id}");
        $this->assertDatabaseHas('volunteer_events', [
            'id'         => $event->id,
            'title'      => 'Updated Seva Title with New Timings',
            'start_time' => '11:00 AM',
        ]);
    }

    public function test_updating_event_with_arbitrary_event_type_is_rejected()
    {
        $event = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer1->id,
            'title'        => 'Valid Title',
            'event_type'   => 'Awareness program',
            'event_date'   => now()->addDays(3)->format('Y-m-d'),
            'status'       => 'upcoming',
        ]);

        $response = $this->actingAs($this->volunteer1, 'volunteer')
            ->put("/volunteer/events/{$event->id}", [
                'title'      => 'Valid Title',
                'event_type' => 'Unapproved Fake Type',
                'event_date' => now()->addDays(3)->format('Y-m-d'),
                'status'     => 'upcoming',
            ]);

        $response->assertSessionHasErrors(['event_type']);
        $event->refresh();
        $this->assertEquals('Awareness program', $event->event_type);
    }
}
