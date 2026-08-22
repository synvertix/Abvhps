<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MembershipAdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'ADMIN TEST',
            'email' => 'admin@test.com',
            'password' => bcrypt('123456789')
        ]);

        $this->member = Membership::create([
            'membership_id' => '676700037791',
            'phone' => '7676676766',
            'payment_status' => 'success',
            'payment_id' => 'TXN-7520357D',
            'aadhaar_number' => '698574589658',
            'full_name' => 'SRINIVASA RAO',
            'father_or_husband_name' => 'kasinath',
            'gotram' => 'kasi',
            'occupation' => 'business',
            'blood_group' => 'A+',
            'email' => 'kasi@gmail.com',
            'pincode' => '516193',
            'grama_panchayat' => 'Porumamilla',
            'mandal' => 'Porumamilla',
            'assembly_segment' => 'badvel',
            'district' => 'YSR Kadapa',
            'state' => 'Andhra Pradesh',
            'country' => 'India',
            'is_completed' => 1
        ]);
    }

    public function test_admin_can_access_membership_ledger(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.membership.ledger'));
        $response->assertStatus(200);
        $response->assertSee('Approved Lifetime Membership Ledger');
        $response->assertSee($this->member->full_name);
    }

    public function test_admin_can_view_member_profile(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.membership.view', $this->member->id));
        $response->assertStatus(200);
        $response->assertSee($this->member->full_name);
        $response->assertSee($this->member->phone);
        $response->assertSee('Member Dossier');
    }

    public function test_admin_can_view_member_id_card(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.membership.idcard', $this->member->id));
        $response->assertStatus(200);
        $response->assertSee($this->member->full_name);
        $response->assertSee('Print ID Card');
    }

    public function test_admin_can_view_edit_profile_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.membership.edit', $this->member->id));
        $response->assertStatus(200);
        $response->assertSee($this->member->full_name);
        $response->assertSee('Edit Member Profile');
    }

    public function test_admin_can_update_member_profile(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.membership.update', $this->member->id), [
            'full_name' => 'SRINIVASA RAO',
            'phone' => '7676676766',
            'aadhaar_number' => '698574589658',
            'father_or_husband_name' => 'kasinath',
            'gotram' => 'kasi',
            'occupation' => 'business',
            'blood_group' => 'O+',
            'email' => 'kasi@gmail.com',
            'pincode' => '516193',
            'grama_panchayat' => 'Porumamilla',
            'mandal' => 'Porumamilla',
            'assembly_segment' => 'badvel',
            'district' => 'YSR Kadapa',
            'state' => 'Andhra Pradesh',
            'country' => 'India',
        ]);

        $response->assertRedirect(route('admin.membership.ledger'));
        $this->assertDatabaseHas('memberships', [
            'id' => $this->member->id,
            'blood_group' => 'O+',
            'full_name' => 'SRINIVASA RAO'
        ]);
    }

    public function test_admin_can_delete_member(): void
    {
        // Create a temporary member to test delete
        $tempMember = Membership::create([
            'membership_id' => '999988887777',
            'phone' => '9876543210',
            'payment_status' => 'success',
            'payment_id' => 'TXN-TESTDELETE',
            'aadhaar_number' => '123456789012',
            'full_name' => 'TEMP DELETE USER',
            'father_or_husband_name' => 'TEMP FATHER',
            'gotram' => 'TEMP GOTRAM',
            'occupation' => 'TESTING',
            'pincode' => '516193',
            'grama_panchayat' => 'TEMP GP',
            'mandal' => 'TEMP MANDAL',
            'district' => 'TEMP DISTRICT',
            'state' => 'Andhra Pradesh',
            'country' => 'India',
            'is_completed' => 1
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.membership.delete', $tempMember->id));
        $response->assertRedirect(route('admin.membership.ledger'));
        $this->assertDatabaseMissing('memberships', [
            'id' => $tempMember->id
        ]);
    }

    public function test_pending_membership_shows_in_pending_grid_and_not_in_ledger(): void
    {
        $pendingMember = Membership::create([
            'membership_id' => '112233445566',
            'phone' => '9888877777',
            'payment_status' => 'success',
            'payment_id' => 'TXN-PENDING01',
            'is_completed' => 0
        ]);

        // 1. Check Pending Grid shows the pending member and placeholder
        $responsePending = $this->actingAs($this->admin)->get(route('admin.membership.pending'));
        $responsePending->assertStatus(200);
        $responsePending->assertSee('Pending Incomplete Membership Applications');
        $responsePending->assertSee('9888877777');
        $responsePending->assertSee('1122 3344 5566');
        $responsePending->assertSee('Details Not Submitted');
        $responsePending->assertSee('TXN-PENDING01');

        // 2. Check Approved Ledger does NOT show this pending member
        $responseLedger = $this->actingAs($this->admin)->get(route('admin.membership.ledger'));
        $responseLedger->assertStatus(200);
        $responseLedger->assertDontSee('9888877777');
    }

    public function test_razorpay_initiate_creates_pending_membership(): void
    {
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_id', 'rzp_test_123');
        \Illuminate\Support\Facades\Config::set('services.razorpay.key_secret', 'rzp_secret_123');

        $phone = '9111122222';

        \Illuminate\Support\Facades\Http::fake([
            'https://api.razorpay.com/v1/orders' => \Illuminate\Support\Facades\Http::response([
                'id'       => 'order_SIM_INIT',
                'amount'   => 10000,
                'currency' => 'INR',
            ], 200),
        ]);

        $paymentResponse = $this->withSession(['verified_membership_phone' => $phone])
                                ->postJson('/membership/payment/razorpay/initiate');

        $paymentResponse->assertOk();
        $paymentResponse->assertJson(['success' => true]);

        $created = Membership::where('phone', $phone)->first();
        $this->assertNotNull($created);
        $this->assertEquals('pending', $created->payment_status);
        $this->assertEquals(0, (int)$created->is_completed);

        // Mark verified to simulate paid pending member
        $created->update([
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_SIM_INIT',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
        ]);

        // Verify it appears in pending admin grid
        $adminResp = $this->actingAs($this->admin)->get(route('admin.membership.pending'));
        $adminResp->assertStatus(200);
        $adminResp->assertSee($phone);
    }

    public function test_verify_otp_resumes_paid_pending_member_at_application(): void
    {
        $phone = '9333344444';
        
        // Member paid with real verified Razorpay audit fields but dropped off before filling application form
        Membership::create([
            'membership_id'       => '556677889900',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_RESUME99',
            'payment_order_id'    => 'order_RESUME99',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'is_completed'        => 0
        ]);

        \Illuminate\Support\Facades\DB::table('phone_verifications')->insert([
            'phone' => $phone,
            'otp' => '654321',
            'is_verified' => false,
            'expired_at' => now()->addMinutes(5),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // User enters OTP again
        $otpResponse = $this->post('/membership/verify-otp', [
            'phone' => $phone,
            'otp' => '654321'
        ]);

        // Should directly resume at /membership/application, NOT /membership/payment
        $otpResponse->assertRedirect('/membership/application');
        $otpResponse->assertSessionHas('verified_membership_phone', $phone);
    }

    public function test_member_registration_saves_selected_gender_and_displays_in_admin(): void
    {
        $phone = '9876501234';
        
        // Paid pending member with verified Aadhaar and real Razorpay audit fields
        Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_FEMALE01',
            'payment_order_id'    => 'order_FEMALE01',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'is_completed'        => 0,
            'is_aadhaar_verified' => true,
        ]);

        // Submit form with Female gender
        $response = $this->withSession(['verified_membership_phone' => $phone])
                         ->post('/submit-membership', [
                             'aadhaar_number' => '234567890123',
                             'full_name' => 'LAKSHMI DEVI',
                             'gender' => 'Female',
                             'dob' => '1995-03-20',
                             'father_or_husband_name' => 'VENKATESH',
                             'gotram' => 'Kashyapa',
                             'occupation' => 'Teacher',
                             'pincode' => '516193',
                             'grama_panchayat' => 'Porumamilla',
                             'mandal' => 'Porumamilla',
                             'district' => 'YSR Kadapa',
                             'state' => 'Andhra Pradesh',
                             'photo' => \Illuminate\Http\UploadedFile::fake()->image('lakshmi.jpg', 100, 100)
                         ]);

        $response->assertRedirect('/membership/view-card');

        // Check database has Female
        $saved = Membership::where('phone', $phone)->first();
        $this->assertNotNull($saved);
        $this->assertEquals('Female', $saved->gender);
        $this->assertEquals('1995-03-20', $saved->dob);
        $this->assertEquals('LAKSHMI DEVI', $saved->full_name);

        // Check admin profile view renders Female
        $adminView = $this->actingAs($this->admin)->get(route('admin.membership.view', $saved->id));
        $adminView->assertStatus(200);
        $adminView->assertSee('Female');
        $adminView->assertSee('1995-03-20');
        $adminView->assertSee('LAKSHMI DEVI');
    }

    public function test_member_registration_requires_gender_selection(): void
    {
        $phone = '9876505678';
        
        Membership::create([
            'membership_id'       => '123456789013',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_NOGENDER01',
            'payment_order_id'    => 'order_NOGENDER01',
            'payment_amount'      => 100.00,
            'payment_verified_at' => now(),
            'is_completed'        => 0,
            'is_aadhaar_verified' => true,
        ]);

        // Submit form without gender
        $response = $this->withSession(['verified_membership_phone' => $phone])
                         ->post('/submit-membership', [
                             'aadhaar_number' => '123456789013',
                             'full_name' => 'RAMESH KUMAR',
                             'dob' => '1992-06-15',
                             'father_or_husband_name' => 'SURESH',
                             'gotram' => 'Bharadwaja',
                             'occupation' => 'Engineer',
                             'pincode' => '516193',
                             'grama_panchayat' => 'Porumamilla',
                             'mandal' => 'Porumamilla',
                             'district' => 'YSR Kadapa',
                             'state' => 'Andhra Pradesh',
                             'photo' => \Illuminate\Http\UploadedFile::fake()->image('ramesh.jpg', 100, 100)
                         ]);

        $response->assertSessionHasErrors(['gender']);
    }
}
