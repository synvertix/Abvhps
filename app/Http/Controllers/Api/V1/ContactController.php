<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\ContactAdminNotificationMail;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Get public contact information and channels.
     */
    public function show(): JsonResponse
    {
        $contact = [
            'phone'           => SiteSetting::get('contact_phone', '+91 8884933379'),
            'email'           => SiteSetting::get('contact_email', 'info@abvhps.org'),
            'address'         => SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193'),
            'whatsapp_number' => SiteSetting::getWhatsAppNumber(),
            'whatsapp_url'    => SiteSetting::getWhatsAppUrl(),
            'social_links'    => array_values(SiteSetting::getActiveSocialLinks()),
        ];

        return response()->json([
            'success' => true,
            'data'    => $contact,
            'message' => null,
        ]);
    }

    /**
     * Submit a contact / inquiry message from the mobile app.
     */
    public function submit(Request $request): JsonResponse
    {
        // 1. Honeypot Anti-Bot Trap
        if (!empty($request->input('website_trap_honeypot'))) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message. Our administration will review your submission shortly.',
            ]);
        }

        // 2. Validation
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:3000|min:5',
        ]);

        $messageContent = (string) $request->input('message');
        $nameContent    = (string) $request->input('name');

        // 3. Link-Blocking Spam Filter
        $spamUrlPattern = '/(https?:\/\/|www\.|\.ru\/|\.xyz\/|\.top\/|<a\s+href|\[url=)/i';
        if (preg_match($spamUrlPattern, $messageContent) || preg_match($spamUrlPattern, $nameContent)) {
            return response()->json([
                'success' => false,
                'message' => 'External links and web addresses are not permitted in contact messages to prevent automated spam. Please remove any URLs and try again.',
            ], 422);
        }

        // 4. Store Contact in Database
        $contact = ContactMessage::create([
            'name'       => strip_tags((string) $request->input('name')),
            'email'      => strip_tags((string) $request->input('email')),
            'phone'      => strip_tags((string) $request->input('phone', '')),
            'subject'    => strip_tags((string) $request->input('subject', 'Mobile App Inquiry')),
            'message'    => strip_tags((string) $request->input('message')),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) ($request->userAgent() ?? 'ABVHPS Mobile App'), 0, 500),
            'source'     => 'MOBILE_APP',
            'source_url' => '/api/v1/contact',
            'status'     => 'unread',
        ]);

        // 5. Non-blocking Admin Email Notification
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
                    'submitted_at' => $contact->created_at ? $contact->created_at->format('d M Y, h:i A') . ' IST' : now()->format('d M Y, h:i A') . ' IST',
                ]));
            }
        } catch (\Throwable $e) {
            // Mail failure is non-fatal — DB record safely preserved
        }

        return response()->json([
            'success' => true,
            'message' => '🙏 Thank you for reaching out to ABVHPS. Your message has been safely logged with our coordination desk.',
        ]);
    }
}
