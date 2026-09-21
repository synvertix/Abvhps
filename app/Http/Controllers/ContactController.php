<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactAdminNotificationMail;

class ContactController extends Controller
{
    // ─────────────────────────────────────────────────────────────────
    // PUBLIC: Contact Us Page
    // ─────────────────────────────────────────────────────────────────
    public function showContactPage()
    {
        $contactPhone   = SiteSetting::get('contact_phone', '+91 9989980055');
        $contactEmail   = SiteSetting::get('contact_email', 'info@abvhps.org');
        $contactAddress = SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli, Porumamilla, Kadapa, A.P - 516193');

        return view('contact', compact('contactPhone', 'contactEmail', 'contactAddress'));
    }

    // ─────────────────────────────────────────────────────────────────
    // PUBLIC: Contact Form Submission
    // ─────────────────────────────────────────────────────────────────
    public function submitContact(Request $request)
    {
        // 1. Honeypot Anti-Bot Trap
        if (!empty($request->input('website_trap_honeypot'))) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message. Our administration will review your submission shortly.'
            ]);
        }

        // 2. Validate
        $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:3000|min:5',
        ]);

        $messageContent = $request->message;
        $nameContent    = $request->name;

        // 3. Link-Blocking Spam Filter
        $spamUrlPattern = '/(https?:\/\/|www\.|\.ru\/|\.xyz\/|\.top\/|<a\s+href|\[url=)/i';
        if (preg_match($spamUrlPattern, $messageContent) || preg_match($spamUrlPattern, $nameContent)) {
            return response()->json([
                'success' => false,
                'message' => 'External links and web addresses are not permitted in contact messages to prevent automated spam. Please remove any URLs and try again.'
            ], 422);
        }

        // 4. Store contact — database is the source of truth
        $contact = ContactMessage::create([
            'name'       => strip_tags($request->name),
            'email'      => strip_tags($request->email),
            'phone'      => strip_tags($request->phone ?? ''),
            'subject'    => strip_tags($request->subject ?? 'General Devotee Inquiry'),
            'message'    => strip_tags($request->message),
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'source'     => 'CONTACT_FORM',
            'source_url' => '/contact',
            'status'     => 'unread',
        ]);

        // 5. Optionally notify admin via email (non-blocking — failure is acceptable)
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
        } catch (\Exception $e) {
            // Mail failure must not prevent the success response — DB record already saved
        }

        return response()->json([
            'success' => true,
            'message' => '🙏 Thank you for reaching out to ABVHPS. Your message has been safely logged in our central audit desk.'
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: Contacts Inbox Index
    // ─────────────────────────────────────────────────────────────────
    public function adminIndex(Request $request)
    {
        $status = $request->input('status', 'all');
        $source = $request->input('source', 'all');
        $search = $request->input('search', '');

        $query = ContactMessage::query();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($source !== 'all') {
            $query->where('source', $source);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name',    'LIKE', "%{$search}%")
                  ->orWhere('email',   'LIKE', "%{$search}%")
                  ->orWhere('phone',   'LIKE', "%{$search}%")
                  ->orWhere('subject', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $messages = $query->orderBy('id', 'desc')->paginate(20);

        $stats = [
            'total'           => ContactMessage::count(),
            'total_messages'  => ContactMessage::count(),
            'unread'          => ContactMessage::where('status', 'unread')->count(),
            'unread_messages' => ContactMessage::where('status', 'unread')->count(),
            'read_messages'   => ContactMessage::whereIn('status', ['read', 'replied', 'closed', 'in_progress'])->count(),
            'in_progress'     => ContactMessage::where('status', 'in_progress')->count(),
            'replied'         => ContactMessage::where('status', 'replied')->count(),
            'closed'          => ContactMessage::where('status', 'closed')->count(),
        ];

        return view('admin.contacts_index', compact('messages', 'stats', 'status', 'source', 'search'));
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: View Single Contact (marks unread → read)
    // ─────────────────────────────────────────────────────────────────
    public function adminView($id)
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->status === 'unread') {
            $message->status  = 'read';
            $message->read_at = now();
            $message->save();
        }

        try {
            AuditLogger::log('CONTACT_VIEWED', 'ContactMessage', (string)$message->id);
        } catch (\Exception $e) {}

        return view('admin.contacts_view', compact('message'));
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: Update Status (AJAX or form POST)
    // ─────────────────────────────────────────────────────────────────
    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:unread,read,in_progress,replied,closed',
        ]);

        $message = ContactMessage::findOrFail($id);
        $oldStatus = $message->status;
        $message->status = $request->status;

        if ($request->status === 'replied' && !$message->replied_at) {
            $message->replied_at = now();
        }

        $message->save();

        try {
            AuditLogger::log('CONTACT_STATUS_CHANGED', 'ContactMessage', (string)$message->id, [
                'from' => $oldStatus,
                'to'   => $request->status,
            ]);
        } catch (\Exception $e) {}

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $message->status]);
        }

        return redirect()->route('admin.contacts.view', $id)->with('success', 'Status updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: Save Notes (AJAX or form POST)
    // ─────────────────────────────────────────────────────────────────
    public function adminSaveNotes(Request $request, $id)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);

        $message = ContactMessage::findOrFail($id);
        $message->admin_notes = $request->notes;
        $message->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.contacts.view', $id)->with('success', 'Notes saved.');
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: Delete
    // ─────────────────────────────────────────────────────────────────
    public function adminDelete($id)
    {
        $message = ContactMessage::findOrFail($id);

        try {
            AuditLogger::log('CONTACT_DELETED', 'ContactMessage', (string)$message->id, [
                'name'    => $message->name,
                'subject' => $message->subject,
            ]);
        } catch (\Exception $e) {}

        $message->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'Contact inquiry removed from audit log.');
    }
}
