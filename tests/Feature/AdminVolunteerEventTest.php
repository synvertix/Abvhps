<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;

class AdminVolunteerEventTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Volunteer $volunteer;
    protected Membership $member;
    protected VolunteerEvent $event;
    protected VolunteerEventMember $eventMember;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->admin = User::factory()->create();

        $this->member = Membership::create([
            'membership_id'  => '999911112222',
            'phone'          => '9876500001',
            'full_name'      => 'NAGARAJU SHARMA',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 1,
        ]);

        $this->volunteer = Volunteer::create([
            'membership_id'             => '999911112222',
            'phone'                     => '9876500001',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'email'                     => 'nagaraju@abvhps.org',
            'password'                  => Hash::make('password123'),
            'must_change_password'      => false,
            'status'                    => 'approved',
            'is_active'                 => true,
            'volunteer_id'              => '543210',
            'volunteer_login_id'        => '543210',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'NAGARAJU SHARMA',
            'account_number'            => '1122334455',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Badvel',
            'nominee_name'              => 'Lakshmi',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543219',
            'document_declaration_path' => 'v3_decl.pdf',
            'document_voter_path'       => 'v3_voter.pdf',
            'document_bank_path'        => 'v3_bank.pdf',
        ]);

        $this->event = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer->id,
            'title'        => 'Sri Rama Navami Annadanam Seva',
            'event_type'   => 'Annadanam',
            'event_date'   => now()->format('Y-m-d'),
            'venue'        => 'Temple Premises',
            'mandal'       => 'Badvel',
            'district'     => 'YSR Kadapa',
            'status'       => 'completed',
            'outcome'      => 'Successfully served 400 meals.',
        ]);

        $this->eventMember = VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member->id,
            'membership_id'         => '999911112222',
            'participation_type'    => 'beneficiary',
            'participation_status'  => 'benefited',
            'benefit_details'       => 'Annadanam Prasadam kit received',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);
    }

    protected function createTestPhoto(): UploadedFile
    {
        $img = imagecreatetruecolor(400, 300);
        $bg = imagecolorallocate($img, 200, 120, 60);
        imagefilledrectangle($img, 0, 0, 400, 300, $bg);
        $tmp = tempnam(sys_get_temp_dir(), 'admin_p_');
        imagejpeg($img, $tmp, 90);
        imagedestroy($img);

        return new UploadedFile($tmp, 'admin_proof.jpg', 'image/jpeg', null, true);
    }

    public function test_admin_can_view_volunteer_events_index()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get('/admin/volunteer-events');

        $response->assertStatus(200);
        $response->assertSee('Sri Rama Navami Annadanam Seva');
        $response->assertSee('543210');
    }

    public function test_admin_can_view_event_detail_with_participating_members()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get("/admin/volunteer-events/{$this->event->id}");

        $response->assertStatus(200);
        $response->assertSee('Sri Rama Navami Annadanam Seva');
        $response->assertSee('NAGARAJU SHARMA');
        $response->assertSee('9999 1111 2222');
        $response->assertSee('Annadanam Prasadam kit received');
    }

    public function test_member_admin_profile_displays_event_participation_history()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get("/admin/membership/view/{$this->member->id}");

        $response->assertStatus(200);
        $response->assertSee('Event Participation &amp; Benefits History', false);
        $response->assertSee('Sri Rama Navami Annadanam Seva');
        $response->assertSee('Annadanam Prasadam kit received');
    }

    public function test_volunteer_admin_profile_displays_event_statistics()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get("/admin/volunteers/view/{$this->volunteer->id}");

        $response->assertStatus(200);
        $response->assertSee('Volunteer Service &amp; Event Statistics', false);
        $response->assertSee('Sri Rama Navami Annadanam Seva');
    }

    public function test_admin_proof_replacement_enforces_strict_2kb_rule()
    {
        $photo = $this->createTestPhoto();

        $response = $this->actingAs($this->admin, 'web')
            ->post("/admin/volunteer-events/{$this->event->id}/members/{$this->eventMember->id}/replace-proof", [
                'proof_image' => $photo,
            ]);

        $response->assertRedirect("/admin/volunteer-events/{$this->event->id}");

        $this->eventMember->refresh();
        $this->assertNotNull($this->eventMember->proof_image_path);
        $this->assertLessThanOrEqual(2048, $this->eventMember->proof_image_size_bytes);

        $diskBytes = Storage::disk('public')->size($this->eventMember->proof_image_path);
        $this->assertGreaterThan(0, $diskBytes);
        $this->assertLessThanOrEqual(2048, $diskBytes);
    }
}
