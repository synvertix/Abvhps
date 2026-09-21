<?php

namespace Tests\Feature;

use App\Mail\ContactAdminNotificationMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'legal.privacy'           => 'Privacy Policy',
        'legal.terms'             => 'Terms &amp; Conditions',
        'legal.refund'            => 'Refund &amp; Cancellation Policy',
        'legal.donation_payments' => 'Donation &amp; Payment Policy',
        'legal.account_deletion'  => 'Account &amp; Data Deletion',
    ];

    public function test_every_legal_page_renders_with_organisation_details_and_grievance_officer(): void
    {
        SiteSetting::set('contact_email', 'info@abvhps.org');
        SiteSetting::set('contact_phone', '+91 9989980055');

        foreach (self::PAGES as $route => $title) {
            $html = $this->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString($title, $html, "{$route} title");
            $this->assertStringContainsString('Akhanda Bharatha Viswa Hindu Parirakshana Samiti', $html);
            $this->assertStringContainsString('Registration No. 20/2023', $html);
            $this->assertStringContainsString('Grievance Officer', $html);
            $this->assertStringContainsString('info@abvhps.org', $html);
            $this->assertStringContainsString('+91 9989980055', $html);
            $this->assertStringContainsString('Version 1.0', $html);
            $this->assertStringContainsString('id="legal-toc"', $html);
        }

        $this->get(route('legal.index'))->assertOk()->assertSee('Privacy Policy')->assertSee('Account &amp; Data Deletion', false);
    }

    public function test_pages_are_public_and_indexable(): void
    {
        $this->get(route('legal.privacy'))->assertOk()->assertDontSee('noindex');
        $this->get('/privacy')->assertRedirect(route('legal.privacy'))->assertStatus(301);
        $this->get('/terms')->assertRedirect(route('legal.terms'));
        $this->get('/delete-account')->assertRedirect(route('legal.account_deletion'));
    }

    public function test_privacy_policy_is_honest_about_what_we_actually_do(): void
    {
        $html = html_entity_decode($this->get(route('legal.privacy'))->getContent(), ENT_QUOTES);

        // Every processor / data flow that exists in the code base is disclosed
        foreach (['Razorpay', 'Cashfree', 'DigiLocker', 'Fast2SMS', 'Amazon Web Services', 'PAN', 'Aadhaar'] as $needle) {
            $this->assertStringContainsString($needle, $html, "Privacy Policy must mention {$needle}");
        }
        foreach (['Data Fiduciary', 'Data Principal', 'Data Protection Board of India', 'withdraw consent', 'erase'] as $needle) {
            $this->assertStringContainsString($needle, $html, "Privacy Policy must cover {$needle}");
        }
        // No over-promises
        $this->assertStringNotContainsStringIgnoringCase('never be transferred to third parties', $html);
        $this->assertStringNotContainsStringIgnoringCase('under any circumstances', $html);
        $this->assertStringNotContainsStringIgnoringCase('highly encrypted', $html);
        $this->assertStringContainsString('cannot promise absolute security', $html);
    }

    public function test_terms_cover_each_activity_and_law(): void
    {
        $html = html_entity_decode($this->get(route('legal.terms'))->getContent(), ENT_QUOTES);

        foreach (['Membership', 'Volunteers and wings', 'Rudrasena', 'Kala Brundam', 'Grama Seva Dal', 'Organic Farmers', 'Exams', 'unpaid', 'laws of India', 'Kadapa, Andhra Pradesh'] as $needle) {
            $this->assertStringContainsString($needle, $html, "Terms must cover {$needle}");
        }
    }

    public function test_refund_and_payment_policies_state_the_rules_and_gateways(): void
    {
        $refund = html_entity_decode($this->get(route('legal.refund'))->getContent(), ENT_QUOTES);
        foreach (['not refundable', 'duplicate', '5–7 working days', 'original payment method', 'transaction ID', '7 days', '10 working days'] as $needle) {
            $this->assertStringContainsString($needle, $refund, "Refund policy must mention {$needle}");
        }

        $pay = html_entity_decode($this->get(route('legal.donation_payments'))->getContent(), ENT_QUOTES);
        foreach (['Razorpay', 'Cashfree', '80G', '12A', 'PAN', 'PCI-DSS', 'signature', 'Foreign Contribution (Regulation) Act', 'never ask'] as $needle) {
            $this->assertStringContainsString($needle, $pay, "Donation & Payment policy must mention {$needle}");
        }
        // Registration / 80G numbers are managed as uploaded certificates, never hard-coded
        $this->assertStringContainsString('/compliance-certificates', $pay);
    }

    public function test_footer_links_to_all_policies_on_every_page(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        foreach (['legal.privacy', 'legal.terms', 'legal.refund', 'legal.donation_payments', 'legal.account_deletion', 'legal.index'] as $route) {
            $this->assertStringContainsString(route($route), $html, "Footer must link to {$route}");
        }
    }

    public function test_forms_show_the_consent_notice_and_the_false_privacy_claim_is_gone(): void
    {
        foreach (['/rudrasena-apply' => 'wing', '/kala-brundam-apply' => 'wing', '/grama-seva-dal-apply' => 'wing', '/organic-farmers-apply' => 'wing', '/donations' => 'donation', '/contact' => 'contact'] as $url => $type) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('data-legal-notice="' . $type . '"', $html, "{$url} must show the {$type} consent notice");
            $this->assertStringContainsString(route('legal.privacy'), $html);
            $this->assertStringContainsString(route('legal.terms'), $html);
        }

        // Views that need a session are checked at source level
        foreach (['membership_application' => 'membership', 'volunteer_application' => 'volunteer', 'exam_application' => 'exam', 'membership_payment' => 'payment'] as $view => $type) {
            $source = File::get(resource_path("views/{$view}.blade.php"));
            $this->assertStringContainsString("@include('partials.legal-consent', ['type' => '{$type}'])", $source, "{$view} must include the {$type} notice");
        }

        $membership = File::get(resource_path('views/membership_application.blade.php'));
        $this->assertStringNotContainsString('will never be transferred to third parties', $membership);
        $this->assertStringNotContainsString('highly encrypted', $membership);
        $this->assertStringContainsString('Digital Personal Data Protection Act, 2023', $membership);
    }

    public function test_data_request_is_stored_as_a_contact_message_and_emailed(): void
    {
        Mail::fake();

        $this->from(route('legal.account_deletion'))->post(route('legal.data_request'), [
            'request_type'  => 'deletion',
            'name'          => 'Ravi Kumar',
            'email'         => 'ravi@example.com',
            'phone'         => '9876543210',
            'membership_id' => '123456789012',
            'details'       => 'Please delete my account.',
            'confirm'       => '1',
        ])->assertRedirect(route('legal.account_deletion'))->assertSessionHas('request_sent');

        $message = ContactMessage::firstOrFail();
        $this->assertSame('DATA_REQUEST', $message->source);
        $this->assertStringStartsWith('[DATA REQUEST]', $message->subject);
        $this->assertStringContainsString('Delete my account / personal data', $message->message);
        $this->assertStringContainsString('123456789012', $message->message);

        Mail::assertSent(ContactAdminNotificationMail::class);
        $this->get(route('legal.account_deletion'))->assertSee('We have received your request');
    }

    public function test_data_request_validation_and_bot_protection(): void
    {
        Mail::fake();

        $this->post(route('legal.data_request'), ['request_type' => 'hack', 'name' => '', 'email' => 'nope'])
            ->assertSessionHasErrors(['request_type', 'name', 'email', 'confirm']);

        // honeypot: silently accepted but nothing stored
        $this->post(route('legal.data_request'), ['website_trap_honeypot' => 'spam', 'name' => 'Bot'])->assertRedirect();
        $this->assertSame(0, ContactMessage::count());
        Mail::assertNothingSent();
    }

    public function test_data_request_is_rate_limited(): void
    {
        Mail::fake();
        $payload = ['request_type' => 'access', 'name' => 'A B', 'email' => 'a@example.com', 'confirm' => '1'];

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('legal.data_request'), $payload)->assertRedirect();
        }
        $this->post(route('legal.data_request'), $payload)->assertStatus(429);
    }

    public function test_sitemap_lists_the_legal_pages(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        foreach (['/legal<', '/privacy-policy<', '/terms-and-conditions<', '/refund-cancellation-policy<', '/donation-payment-policy<', '/account-deletion<'] as $loc) {
            $this->assertStringContainsString($loc, $xml);
        }
    }

    public function test_legal_labels_are_translated(): void
    {
        $html = $this->withSession(['locale' => 'te'])->get('/')->getContent();
        $this->assertStringContainsString('గోప్యతా విధానం', $html);
        $this->assertStringContainsString('నా డేటా తొలగించండి', $html);
    }
}
