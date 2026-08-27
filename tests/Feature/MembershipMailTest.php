<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\Membership;
use App\Models\NotificationLog;
use App\Mail\MembershipWelcomeMail;
use App\Services\RazorpayPaymentService;

class MembershipMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createVerifiedPaidMember(string $phone = '9876543210'): Membership
    {
        $member = Membership::create([
            'membership_id'       => '123456789012',
            'phone'               => $phone,
            'payment_status'      => 'success',
            'payment_gateway'     => 'razorpay',
            'payment_id'          => 'pay_test_' . $phone,
            'payment_order_id'    => 'order_test_' . $phone,
            'payment_amount'      => RazorpayPaymentService::MEMBERSHIP_AMOUNT_RUPEES,
            'payment_verified_at' => now(),
        ]);

        $member->identity_verified                  = true;
        $member->identity_verification_method        = 'pan';
        $member->identity_verification_provider      = 'cashfree';
        $member->identity_verified_name             = 'SRI RAMA DEVOTEE';
        $member->identity_document_last4            = '1234';
        $member->identity_verified_at              = now();
        $member->save();

        return $member;
    }

    /**
     * Test: Successful application submission sends MembershipWelcomeMail once
     */
    public function test_successful_application_dispatches_membership_welcome_email(): void
    {
        Mail::fake();

        $this->createVerifiedPaidMember('9876543210');
        $photo = UploadedFile::fake()->image('photo.jpg');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'full_name'              => 'SRI RAMA DEVOTEE',
                'gender'                 => 'Male',
                'dob'                    => '1992-04-12',
                'father_or_husband_name' => 'Dasharatha',
                'gotram'                 => 'Raghu Vamsha',
                'occupation'             => 'Dharma Seva',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Akkalareddy Palli',
                'mandal'                 => 'Porumamilla',
                'district'               => 'Kadapa',
                'state'                  => 'Andhra Pradesh',
                'email'                  => 'member@example.com',
                'photo'                  => $photo,
            ]);

        $response->assertRedirect('/membership/view-card');

        Mail::assertSent(MembershipWelcomeMail::class, function ($mail) {
            $this->assertEquals('SRI RAMA DEVOTEE', $mail->memberData['full_name']);
            $this->assertEquals('123456789012', $mail->memberData['membership_id']);
            $this->assertArrayNotHasKey('pan_number', $mail->memberData);
            $this->assertArrayNotHasKey('aadhaar_number', $mail->memberData);
            $this->assertArrayNotHasKey('dob', $mail->memberData);

            return $mail->hasTo('member@example.com');
        });

        // Assert NotificationLog created
        $log = NotificationLog::where('event_type', 'membership_welcome')
            ->where('recipient_email', 'member@example.com')
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('sent', $log->status);

        // Repeated submission does NOT send a second email
        $response2 = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'full_name'              => 'SRI RAMA DEVOTEE',
                'gender'                 => 'Male',
                'dob'                    => '1992-04-12',
                'father_or_husband_name' => 'Dasharatha',
                'gotram'                 => 'Raghu Vamsha',
                'occupation'             => 'Dharma Seva',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Akkalareddy Palli',
                'mandal'                 => 'Porumamilla',
                'district'               => 'Kadapa',
                'state'                  => 'Andhra Pradesh',
                'email'                  => 'member@example.com',
                'photo'                  => $photo,
            ]);

        Mail::assertSent(MembershipWelcomeMail::class, 1);
    }

    /**
     * Test: Mail transport failure does NOT roll back membership completion
     */
    public function test_mail_failure_does_not_rollback_membership_completion(): void
    {
        // Mock Mail facade to throw an Exception on send
        Mail::shouldReceive('to')
            ->once()
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('SMTP Connection Timeout'));

        $member = $this->createVerifiedPaidMember('9876543210');
        $photo = UploadedFile::fake()->image('photo.jpg');

        $response = $this->withSession(['verified_membership_phone' => '9876543210'])
            ->post('/submit-membership', [
                'full_name'              => 'SRI RAMA DEVOTEE',
                'gender'                 => 'Male',
                'dob'                    => '1992-04-12',
                'father_or_husband_name' => 'Dasharatha',
                'gotram'                 => 'Raghu Vamsha',
                'occupation'             => 'Dharma Seva',
                'pincode'                => '516193',
                'grama_panchayat'        => 'Akkalareddy Palli',
                'mandal'                 => 'Porumamilla',
                'district'               => 'Kadapa',
                'state'                  => 'Andhra Pradesh',
                'email'                  => 'member@example.com',
                'photo'                  => $photo,
            ]);

        // Request still succeeds
        $response->assertRedirect('/membership/view-card');

        $member->refresh();
        // Membership completion remains intact
        $this->assertTrue((bool) $member->is_completed);
        $this->assertNull($member->welcome_email_sent_at); // Not marked as successfully sent

        // Log record has status failed so retry is possible via claim()
        $log = NotificationLog::where('event_type', 'membership_welcome')
            ->where('notifiable_id', $member->id)
            ->first();
        $this->assertNotNull($log);
        $this->assertEquals('failed', $log->status);
        $this->assertNull($log->sent_at);
    }
}
