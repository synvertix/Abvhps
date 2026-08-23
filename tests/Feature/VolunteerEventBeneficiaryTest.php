<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;
use App\Models\Volunteer;
use App\Models\Membership;
use App\Models\VolunteerEvent;
use App\Models\VolunteerEventMember;
use InvalidArgumentException;

class VolunteerEventBeneficiaryTest extends TestCase
{
    use RefreshDatabase;

    protected Volunteer $volunteer;
    protected Membership $member1;
    protected Membership $member2;
    protected Membership $incompleteMember;
    protected VolunteerEvent $event;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->member1 = Membership::create([
            'membership_id'  => '123412341234',
            'phone'          => '9800000001',
            'full_name'      => 'KESHAVA RAO',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 1,
        ]);

        $this->member2 = Membership::create([
            'membership_id'  => '567856785678',
            'phone'          => '9800000002',
            'full_name'      => 'LAKSHMI DEVI',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 1,
        ]);

        $this->incompleteMember = Membership::create([
            'membership_id'  => '999988887777',
            'phone'          => '9800000003',
            'full_name'      => 'INCOMPLETE MEMBER',
            'payment_status' => 'success',
            'district'       => 'YSR Kadapa',
            'state'          => 'Andhra Pradesh',
            'is_completed'   => 0, // Incomplete
        ]);

        $this->volunteer = Volunteer::create([
            'membership_id'             => '123412341234',
            'phone'                     => '9800000001',
            'qualification'             => 'Graduate',
            'voter_id_number'           => 'ABC1234567',
            'email'                     => 'keshava@abvhps.org',
            'password'                  => Hash::make('password123'),
            'must_change_password'      => false,
            'status'                    => 'approved',
            'is_active'                 => true,
            'volunteer_id'              => '888111',
            'volunteer_login_id'        => '888111',
            'bank_name'                 => 'SBI',
            'account_holder_name'       => 'KESHAVA RAO',
            'account_number'            => '1122334455',
            'ifsc_code'                 => 'SBIN0001234',
            'branch_name'               => 'Badvel',
            'nominee_name'              => 'Lakshmi',
            'nominee_relation'          => 'Wife',
            'nominee_phone'             => '9876543219',
            'document_declaration_path' => 'v1_decl.pdf',
            'document_voter_path'       => 'v1_voter.pdf',
            'document_bank_path'        => 'v1_bank.pdf',
        ]);

        $this->event = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer->id,
            'title'        => 'Annadanam Seva Camp',
            'event_type'   => 'Annadanam',
            'event_date'   => now()->format('Y-m-d'),
            'status'       => 'completed',
        ]);
    }

    protected function createTestPhoto(int $w = 600, int $h = 400): UploadedFile
    {
        $img = imagecreatetruecolor($w, $h);
        $bg = imagecolorallocate($img, 180, 80, 40);
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);
        imagestring($img, 5, 10, 10, "PROOF " . rand(100, 999), imagecolorallocate($img, 255, 255, 255));

        $tmp = tempnam(sys_get_temp_dir(), 'proof_t_');
        imagejpeg($img, $tmp, 90);
        imagedestroy($img);

        return new UploadedFile($tmp, 'cam_proof.jpg', 'image/jpeg', null, true);
    }

    public function test_valid_member_can_be_added_with_real_foreign_key_and_compressed_proof()
    {
        $photo = $this->createTestPhoto(800, 600);
        $this->assertGreaterThan(2048, $photo->getSize());

        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->post("/volunteer/events/{$this->event->id}/add-member", [
                'membership_id'        => '567856785678',
                'participation_type'   => 'beneficiary',
                'participation_status' => 'benefited',
                'benefit_details'      => 'Received holy annadanam prasadam meal packet',
                'notes'                => 'Elderly devotee',
                'proof_image'          => $photo,
            ]);

        $response->assertRedirect("/volunteer/events/{$this->event->id}");

        $this->assertDatabaseHas('volunteer_event_members', [
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'beneficiary',
            'participation_status'  => 'benefited',
            'benefit_details'       => 'Received holy annadanam prasadam meal packet',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        $record = VolunteerEventMember::where('volunteer_event_id', $this->event->id)
            ->where('membership_record_id', $this->member2->id)
            ->first();

        $this->assertEquals($this->member2->id, $record->membership->id);
        $this->assertNotNull($record->proof_image_path);
        $this->assertLessThanOrEqual(2048, $record->proof_image_size_bytes);

        // Strict storage verification: disk file must exist and be <= 2048 bytes
        $this->assertTrue(Storage::disk('public')->exists($record->proof_image_path));
        $actualBytes = Storage::disk('public')->size($record->proof_image_path);
        $this->assertGreaterThan(0, $actualBytes);
        $this->assertLessThanOrEqual(2048, $actualBytes);
        $this->assertEquals($record->proof_image_size_bytes, $actualBytes);
    }

    public function test_paid_but_incomplete_member_cannot_be_added()
    {
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->post("/volunteer/events/{$this->event->id}/add-member", [
                'membership_id'        => '999988887777',
                'participation_type'   => 'beneficiary',
                'participation_status' => 'benefited',
            ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('volunteer_event_members', [
            'volunteer_event_id'   => $this->event->id,
            'membership_record_id' => $this->incompleteMember->id,
        ]);
    }

    public function test_browser_cannot_spoof_membership_record_id()
    {
        // Browser submits valid membership_id 567856785678 but tries to inject membership_record_id of member1
        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->post("/volunteer/events/{$this->event->id}/add-member", [
                'membership_id'        => '567856785678',
                'membership_record_id' => $this->member1->id, // Spoof attempt
                'participation_type'   => 'participant',
                'participation_status' => 'participated',
            ]);

        $response->assertRedirect();
        // Server MUST bind strictly to member2 ($this->member2->id) based on verified membership_id
        $this->assertDatabaseHas('volunteer_event_members', [
            'volunteer_event_id'   => $this->event->id,
            'membership_record_id' => $this->member2->id,
            'membership_id'        => '567856785678',
        ]);
    }

    public function test_duplicate_member_in_same_event_is_prevented_by_unique_constraint()
    {
        VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'participant',
            'participation_status'  => 'participated',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        $response = $this->actingAs($this->volunteer, 'volunteer')
            ->post("/volunteer/events/{$this->event->id}/add-member", [
                'membership_id'        => '567856785678',
                'participation_type'   => 'beneficiary',
                'participation_status' => 'benefited',
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals(1, VolunteerEventMember::where('volunteer_event_id', $this->event->id)->where('membership_record_id', $this->member2->id)->count());

        // Also test direct DB level unique violation throws exception
        $this->expectException(QueryException::class);
        VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'participant',
            'participation_status'  => 'participated',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);
    }

    public function test_model_level_2kb_invariant_rejects_larger_byte_assignments()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('proof_image_size_bytes (2049) cannot exceed 2048 bytes.');

        VolunteerEventMember::create([
            'volunteer_event_id'     => $this->event->id,
            'membership_record_id'   => $this->member2->id,
            'membership_id'          => '567856785678',
            'participation_type'     => 'beneficiary',
            'participation_status'   => 'benefited',
            'proof_image_path'       => 'proof_images/test.webp',
            'proof_image_size_bytes' => 2049, // Exceeds 2048
            'added_by_volunteer_id'  => $this->volunteer->id,
        ]);
    }

    public function test_different_members_may_participate_in_same_event()
    {
        VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member1->id,
            'membership_id'         => '123412341234',
            'participation_type'    => 'volunteer_support',
            'participation_status'  => 'participated',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'beneficiary',
            'participation_status'  => 'benefited',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        $this->assertEquals(2, $this->event->eventMembers()->count());
    }

    public function test_same_member_may_participate_in_different_events()
    {
        $event2 = VolunteerEvent::create([
            'volunteer_id' => $this->volunteer->id,
            'title'        => 'Goshala Seva Drive',
            'event_type'   => 'Goshala seva',
            'event_date'   => now()->addDays(5)->format('Y-m-d'),
            'status'       => 'upcoming',
        ]);

        VolunteerEventMember::create([
            'volunteer_event_id'    => $this->event->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'beneficiary',
            'participation_status'  => 'benefited',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        VolunteerEventMember::create([
            'volunteer_event_id'    => $event2->id,
            'membership_record_id'  => $this->member2->id,
            'membership_id'         => '567856785678',
            'participation_type'    => 'participant',
            'participation_status'  => 'registered',
            'added_by_volunteer_id' => $this->volunteer->id,
        ]);

        $this->assertEquals(2, $this->member2->eventParticipations()->count());
    }

    public function test_updating_member_proof_deletes_old_image_only_after_successful_db_update()
    {
        $oldPhoto = $this->createTestPhoto(400, 300);
        $this->actingAs($this->volunteer, 'volunteer')
            ->post("/volunteer/events/{$this->event->id}/add-member", [
                'membership_id'        => '567856785678',
                'participation_type'   => 'beneficiary',
                'participation_status' => 'benefited',
                'proof_image'          => $oldPhoto,
            ]);

        $memberLink = VolunteerEventMember::where('volunteer_event_id', $this->event->id)
            ->where('membership_record_id', $this->member2->id)
            ->first();

        $oldStoredPath = $memberLink->proof_image_path;
        $this->assertTrue(Storage::disk('public')->exists($oldStoredPath));

        // Now update with a new proof image
        $newPhoto = $this->createTestPhoto(500, 400);
        $this->actingAs($this->volunteer, 'volunteer')
            ->put("/volunteer/events/{$this->event->id}/members/{$memberLink->id}", [
                'participation_type'   => 'beneficiary',
                'participation_status' => 'benefited',
                'proof_image'          => $newPhoto,
            ]);

        $memberLink->refresh();
        $newStoredPath = $memberLink->proof_image_path;

        // Old file deleted, new file persisted
        $this->assertNotEquals($oldStoredPath, $newStoredPath);
        $this->assertFalse(Storage::disk('public')->exists($oldStoredPath));
        $this->assertTrue(Storage::disk('public')->exists($newStoredPath));
    }
}
