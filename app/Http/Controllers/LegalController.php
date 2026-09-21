<?php

namespace App\Http\Controllers;

use App\Mail\ContactAdminNotificationMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Public legal documents and the data-rights request form.
 * The wording lives in resources/views/legal/*.blade.php; organisation facts come from config/abvhps.php
 * and the Site Settings (phone, e-mail, address) so the pages never disagree with the rest of the site.
 */
class LegalController extends Controller
{
    /** Request types accepted by the data-rights form. */
    public const REQUEST_TYPES = [
        'deletion'   => 'Delete my account / personal data',
        'access'     => 'Get a copy of the personal data you hold about me',
        'correction' => 'Correct or update my personal data',
        'consent'    => 'Withdraw my consent for a specific use',
        'other'      => 'Other privacy request or complaint',
    ];

    public function index()
    {
        return view('legal.index', $this->context());
    }

    public function privacy()
    {
        return view('legal.privacy', $this->context());
    }

    public function terms()
    {
        return view('legal.terms', $this->context());
    }

    public function refund()
    {
        return view('legal.refund', $this->context());
    }

    public function donationPayments()
    {
        return view('legal.donation-payments', $this->context());
    }

    public function accountDeletion()
    {
        return view('legal.account-deletion', $this->context() + ['requestTypes' => self::REQUEST_TYPES]);
    }

    /**
     * Stores a privacy / deletion request as a Contact Message (source DATA_REQUEST) so it shows up in the
     * existing admin "Contact Messages" desk and is e-mailed to the organisation like any other enquiry.
     */
    public function submitDataRequest(Request $request)
    {
        // Honeypot: bots fill hidden fields, people do not.
        if (!empty($request->input('website_trap_honeypot'))) {
            return redirect()->route('legal.account_deletion')->with('request_sent', true);
        }

        $data = $request->validate([
            'request_type'  => 'required|in:' . implode(',', array_keys(self::REQUEST_TYPES)),
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|max:150',
            'phone'         => 'nullable|string|max:20',
            'membership_id' => 'nullable|string|max:40',
            'details'       => 'nullable|string|max:2000',
            'confirm'       => 'accepted',
        ], [
            'confirm.accepted' => 'Please confirm that the details are yours so we can verify the request.',
        ]);

        $label = self::REQUEST_TYPES[$data['request_type']];
        $body = "Request type: {$label}\n"
              . 'Registered phone: ' . ($data['phone'] ?? '-') . "\n"
              . 'Membership / volunteer ID: ' . ($data['membership_id'] ?? '-') . "\n\n"
              . 'Details: ' . ($data['details'] ?? '-');

        $contact = ContactMessage::create([
            'name'       => strip_tags($data['name']),
            'email'      => strip_tags($data['email']),
            'phone'      => strip_tags($data['phone'] ?? ''),
            'subject'    => '[DATA REQUEST] ' . $label,
            'message'    => strip_tags($body),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'source'     => 'DATA_REQUEST',
            'source_url' => '/account-deletion',
            'status'     => 'unread',
        ]);

        try {
            $adminEmail = SiteSetting::get('contact_email', 'info@abvhps.org');
            if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Mail::to($adminEmail)->send(new ContactAdminNotificationMail([
                    'name'         => $contact->name,
                    'email'        => $contact->email,
                    'phone'        => $contact->phone,
                    'subject'      => $contact->subject,
                    'message'      => $contact->message,
                    'source'       => $contact->source,
                    'submitted_at' => now()->format('d M Y, h:i A') . ' IST',
                ]));
            }
        } catch (\Throwable $e) {
            // The request is already saved; a mail failure must not lose it.
            Log::warning('Data request e-mail notification failed: ' . $e->getMessage());
        }

        return redirect()->route('legal.account_deletion')->with('request_sent', true);
    }

    /** Facts shared by every legal page. */
    private function context(): array
    {
        $legal = config('abvhps.legal');
        $email = SiteSetting::get('contact_email', 'info@abvhps.org');

        return [
            'legal'   => $legal,
            'contact' => [
                'email'   => $email,
                'phone'   => SiteSetting::get('contact_phone', '+91 9989980055'),
                'address' => SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193'),
            ],
            'officer' => [
                'name'  => $legal['grievance_officer_name'] ?? null,
                'email' => $legal['grievance_officer_email'] ?: $email,
            ],
        ];
    }
}
