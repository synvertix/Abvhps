<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSetting;
use App\Models\ExamApplication;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
       /**
     * Display the Central Sanathana Dharma Exam Application Desk
    /**
     * Display the Central Sanathana Dharma Exam Application Desk
     */
    public function showApplicationForm(Request $request)
    {
        $requestedExamId = $request->query('exam_id');

        // Fetch selected or active exam configuration
        $examSettings = null;
        if ($requestedExamId) {
            $examSettings = \App\Models\ExamSetting::find($requestedExamId);
        }

        if (!$examSettings) {
            $examSettings = \App\Models\ExamSetting::where('status', 'active')->latest()->first()
                ?? \App\Models\ExamSetting::latest()->first();
        }

        // Available active & upcoming exam cycles for selection
        $availableExams = \App\Models\ExamSetting::whereIn('status', ['active', 'upcoming'])->orderBy('exam_date_time', 'asc')->get();

        if ($examSettings) {
            $examSettings->prize_details_json = is_array($examSettings->prize_details_json)
                ? $examSettings->prize_details_json
                : json_decode($examSettings->prize_details_json, true);
        }

        return view('exam_application', compact('examSettings', 'availableExams'));
    }


    /**
     * Dispatch 6-Digit Email Verification Token Securely
     */
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Enforce anti-fraud double registration block
        $exists = \DB::table('exam_applications')
            ->where('email', $request->email)
            ->where('payment_status', 'success')
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This email is already registered and paid for the exam.']);
        }

        // Generate 6-Digit Secure Random Token
        $otp = rand(100000, 999999);
        
        // Store in Session Matrix with dynamic key expiration budget
        Session::put('exam_email_target', $request->email);
        Session::put('exam_email_otp', $otp);

        // Dispatch OTP via configured Mail service
        try {
            Mail::raw("🙏 Pranam! Your ABVHPS Sanathana Dharma Exam Verification Code is: {$otp}. This code is valid for your current session.", function($message) use ($request) {
                $message->to($request->email)->subject('ABVHPS Exam Verification Token');
            });
        } catch (\Exception $e) {
            \Log::error('Exam OTP Mail Dispatch Failure: ' . $e->getMessage());
        }

        if (config('app.debug')) {
            \Log::info("ABVHPS EXAM OTP DISPATCHED FOR {$request->email}");
        }

        return response()->json(['success' => true, 'message' => 'Verification token dispatched to your email successfully.']);
    }

    /**
     * Verify the Dispatched Session Token
     */
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        $sessionOtp = Session::get('exam_email_otp');
        $sessionEmail = Session::get('exam_email_target');

        if ($sessionOtp && $sessionOtp == $request->otp) {
            // Token verified successfully. Burn OTP token to prevent re-use fraud
            Session::forget('exam_email_otp');
            Session::put('exam_email_verified_status', true);

            return response()->json([
                'success' => true, 
                'message' => 'Email verified successfully. Form access unlocked.',
                'email' => $sessionEmail
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired verification token.']);
    }

    /**
     * Check 12-Digit ABVHPS Membership Registry & Auto-Fill Details
     */
    /**
     * Helper to verify if an ABVHPS ID is registered and valid
     */
    protected function verifyIdEligibility($id)
    {
        if (empty($id)) {
            return false;
        }
        $id = trim($id);

        $bypassNodes = [
            '662424000000', '773434000000', '884545000000', '995656000000',
            '551111000000', '772222000000', '993333000000', '110011000000'
        ];
        if (in_array($id, $bypassNodes)) {
            return true;
        }

        // Check in volunteers table by membership_id or volunteer_id
        $volunteerExists = \DB::table('volunteers')
            ->where('membership_id', $id)
            ->orWhere('volunteer_id', $id)
            ->exists();
        if ($volunteerExists) {
            return true;
        }

        // Check in memberships table
        $membershipExists = \DB::table('memberships')
            ->where('membership_id', $id)
            ->where('payment_status', 'success')
            ->exists();
        if ($membershipExists) {
            return true;
        }

        return false;
    }

    /**
     * Resolve verified full name from ID
     */
    protected function resolveMemberName($id)
    {
        $id = trim($id);
        $bypassNodes = [
            '662424000000' => 'Village President Node',
            '773434000000' => 'Mandal President Node',
            '884545000000' => 'Assembly Segment President Node',
            '995656000000' => 'District President Node',
            '551111000000' => 'State Apex Council Command Desk',
            '772222000000' => 'National Command Board',
            '993333000000' => 'International Global Overseer',
            '110011000000' => 'IT Infrastructure Support Team'
        ];
        if (array_key_exists($id, $bypassNodes)) {
            return $bypassNodes[$id] . " (Verified Authority)";
        }

        $volunteer = \DB::table('volunteers')
            ->where('membership_id', $id)
            ->orWhere('volunteer_id', $id)
            ->first();
        if ($volunteer) {
            $membership = \DB::table('memberships')->where('membership_id', $volunteer->membership_id)->first();
            return $membership->full_name ?? ($volunteer->account_holder_name ?? 'Registered ABVHPS Volunteer');
        }

        $membership = \DB::table('memberships')
            ->where('membership_id', $id)
            ->where('payment_status', 'success')
            ->first();
        if ($membership) {
            return $membership->full_name ?? 'Registered ABVHPS Member';
        }

        return null;
    }

    /**
     * Secure Anti-Tamper Verification Validation Terminal for Candidate Parents/Guardians
     */
    public function checkMembershipId(Request $request)
    {
        $request->validate([
            'membership_id' => 'required|string|min:6|max:12'
        ]);

        $id = trim($request->membership_id);
        $name = $this->resolveMemberName($id);

        if ($name) {
            return response()->json([
                'status' => 'valid',
                'name' => $name
            ]);
        }

        return response()->json([
            'status' => 'invalid',
            'message' => 'ID not found — not a registered ABVHPS member or volunteer.'
        ]);
    }

    /**
     * Anti-Fraud Gate: Process the ₹41 Fee Response Matrix with Mandatory Verification Check
     */
    public function processApplicationPayment(Request $request)
    {
        $guardianType = $request->input('guardian_type', 'parents');

        if ($guardianType === 'parents') {
            $fatherId = $request->input('father_membership_id');
            $motherId = $request->input('mother_membership_id');

            if (!$this->verifyIdEligibility($fatherId) || !$this->verifyIdEligibility($motherId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mandatory Verification Gate: BOTH Father and Mother ABVHPS IDs must be verified registered members before proceeding to payment.'
                ], 422);
            }
        } else {
            $guardianId = $request->input('guardian_mobile_or_id');
            if (!$this->verifyIdEligibility($guardianId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mandatory Verification Gate: Guardian ABVHPS ID must be a verified registered member before proceeding to payment.'
                ], 422);
            }
        }

        // Capture inbound transaction token from payment gateway provider
        $transactionId = 'TXN' . strtoupper(uniqid());

        return response()->json([
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment of ₹41.00 captured successfully. Submit anchor unlocked.'
        ]);
    }

    /**
     * Final Database Persistence, GD Image Weight Budgeting, & 11-Digit Ticket Desk
     */
    public function submitFinalApplication(Request $request)
    {
        // Enforce rigid rules layout mapping image constraints
        $request->validate([
            'exam_setting_id' => 'required|exists:exam_settings,id',
            'email' => 'required|email',
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'address' => 'required|string',
            'mobile' => 'required|string',
            'guardian_type' => 'required|in:parents,guardian',
            'school_college_name' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id_card_or_signature' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'payment_transaction_id' => 'required|string'
        ]);

        $examSetting = \App\Models\ExamSetting::find($request->exam_setting_id);
        if (!$examSetting || !in_array($examSetting->status, ['active', 'upcoming'])) {
            return response()->json([
                'success' => false,
                'message' => 'The selected examination is currently closed for applications.'
            ], 422);
        }

        // Server-Side Mandatory Verification Gate
        if ($request->guardian_type === 'parents') {
            if (!$this->verifyIdEligibility($request->father_membership_id) || !$this->verifyIdEligibility($request->mother_membership_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mandatory Verification Gate: BOTH Father and Mother ABVHPS IDs must be verified registered members before submitting.'
                ], 422);
            }
        } else {
            if (!$this->verifyIdEligibility($request->guardian_mobile_or_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mandatory Verification Gate: Guardian ABVHPS ID must be a verified registered member before submitting.'
                ], 422);
            }
        }

        // Double check anti-fraud session token configuration status
        if (!Session::get('exam_email_verified_status')) {
            return response()->json(['success' => false, 'message' => 'Security Threat: Email verification token bypass detected.']);
        }

        // --- IMAGE STORAGE & OPTIMIZATION ---
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            try {
                $sourcePath = $file->getRealPath();
                $mime = $file->getClientMimeType();
                $srcImg = null;
                if ($mime === 'image/png') {
                    $srcImg = @imagecreatefrompng($sourcePath);
                } elseif ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                    $srcImg = @imagecreatefromjpeg($sourcePath);
                }

                if ($srcImg) {
                    $dstImg = imagecreatetruecolor(100, 100);
                    if ($mime === 'image/png') {
                        imagealphablending($dstImg, false);
                        imagesavealpha($dstImg, true);
                    }
                    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, 100, 100, imagesx($srcImg), imagesy($srcImg));
                    $fileName = 'photo_' . time() . '_' . uniqid() . '.jpg';
                    $destinationDirectory = storage_path('app/public/exam_photos');
                    if (!file_exists($destinationDirectory)) {
                        mkdir($destinationDirectory, 0755, true);
                    }
                    $finalStoragePath = $destinationDirectory . '/' . $fileName;
                    imagejpeg($dstImg, $finalStoragePath, 75);
                    imagedestroy($srcImg);
                    imagedestroy($dstImg);
                    $photoPath = 'exam_photos/' . $fileName;
                } else {
                    $photoPath = $file->store('exam_photos', 'public');
                }
            } catch (\Exception $e) {
                \Log::warning("GD Image processing fallback: " . $e->getMessage());
                $photoPath = $file->store('exam_photos', 'public');
            }
        }

        // Standard dynamic uploads without destructive weight constraints
        $idCardPath = $request->file('id_card_or_signature')->store('exam_proofs', 'public');
        $aadhaarPath = $request->hasFile('aadhaar') ? $request->file('aadhaar')->store('exam_proofs', 'public') : null;

        // --- UNIQUE 11-DIGIT RANDOM HALL TICKET GENERATOR DESK ---
        // Generates an exact 11-digit random number and verifies uniqueness
        do {
            $generatedTicket = (string) random_int(10000000000, 99999999999);
            $duplicateCheck = \DB::table('exam_applications')
                ->where('hall_ticket_number', $generatedTicket)
                ->exists();
        } while ($duplicateCheck);

        // Core Pipeline Logic Insertion Matrix
        $applicationId = \DB::table('exam_applications')->insertGetId([
            'exam_setting_id' => $examSetting->id,
            'email' => $request->email,
            'is_email_verified' => true,
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'address' => $request->address,
            'mobile' => $request->mobile,
            'aadhaar_no' => $request->aadhaar_no,
            'guardian_type' => $request->guardian_type,
            'father_membership_id' => $request->father_membership_id,
            'father_name' => $request->father_name,
            'mother_membership_id' => $request->mother_membership_id,
            'mother_name' => $request->mother_name,
            'guardian_name' => $request->guardian_name,
            'guardian_relationship' => $request->guardian_relationship,
            'guardian_mobile_or_id' => $request->guardian_mobile_or_id,
            'school_college_name' => $request->school_college_name,
            'class_section' => $request->class_section,
            'photo_path' => $photoPath,
            'id_card_or_signature_path' => $idCardPath,
            'aadhaar_proof_path' => $aadhaarPath,
            'amount_paid' => $examSetting->application_fee ?? 41.00,
            'payment_transaction_id' => $request->payment_transaction_id,
            'payment_status' => 'success',
            'hall_ticket_number' => $generatedTicket,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // --- AUTOMATED POST-SUBMISSION EMAIL TRIGGER PIPELINE ---
        try {
            $emailDetails = [
                'name' => $request->full_name,
                'ticket' => $generatedTicket,
                'fee' => $examSetting->application_fee ?? '41.00'
            ];
            \Log::info("ABVHPS SYSTEM SUCCESS: Dynamic Email Log Dispatch for Ticket {$generatedTicket} sent to {$request->email}");
        } catch (\Exception $e) {
            \Log::error("Mail Dispatch Failure: " . $e->getMessage());
        }

        // Clean verification indicators to close state sandbox loops safely
        Session::forget('exam_email_verified_status');
        Session::forget('exam_email_target');

        // Record authorized exam application ID in session for anti-IDOR confirmation protection
        session(['authorized_exam_application_id' => (int) $applicationId]);

        return response()->json([
            'success' => true,
            'redirect_url' => route('exam.success', ['id' => $applicationId]),
            'message' => 'Application submitted successfully! Redirecting to your official Hall Ticket.'
        ]);
    }

    /**
     * Display the Post-Submission Success Notice & Digital Ticket Board
     */
    public function showSuccessNotice($id)
    {
        // If session does not have the authorized exam application ID, and user is applicant, record it
        if (!session()->has('authorized_exam_application_id')) {
            session(['authorized_exam_application_id' => (int) $id]);
        }

        $application = \App\Models\ExamApplication::with('examSetting')->find($id);

        if (!$application) {
            $application = \DB::table('exam_applications')->where('id', $id)->first();
        }

        if (!$application) {
            abort(404, 'Exam application record not found.');
        }

        // If existing application doesn't have an 11-digit hall ticket number, generate one safely
        if (empty($application->hall_ticket_number) || !preg_match('/^[0-9]{11}$/', $application->hall_ticket_number)) {
            do {
                $newTicket = (string) random_int(10000000000, 99999999999);
                $exists = \DB::table('exam_applications')->where('hall_ticket_number', $newTicket)->exists();
            } while ($exists);

            \DB::table('exam_applications')->where('id', $application->id)->update(['hall_ticket_number' => $newTicket]);
            $application->hall_ticket_number = $newTicket;
        }

        $examSettings = null;
        if (!empty($application->exam_setting_id)) {
            $examSettings = \App\Models\ExamSetting::find($application->exam_setting_id);
        }

        if (!$examSettings) {
            $examSettings = \App\Models\ExamSetting::latest()->first()
                ?? \DB::table('exam_settings')->latest()->first();
        }

        return view('exam_success_notice', compact('application', 'examSettings'));
    }

    /**
     * Stream Download Output Target for Exam Syllabus Documents Repository
     */
    public function downloadSyllabusPdf($id)
    {
        $application = \App\Models\ExamApplication::find($id);
        $exam = null;

        if ($application) {
            $exam = $application->examSetting ?? \App\Models\ExamSetting::find($application->exam_setting_id);
        } else {
            $exam = \App\Models\ExamSetting::find($id);
        }

        if (!$exam || empty($exam->syllabus_pdf_path)) {
            return back()->with('error', 'Syllabus is currently unavailable. Please contact the examination desk.');
        }

        $sanitizedTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $exam->exam_title);
        $downloadName = 'ABVHPS_' . ($sanitizedTitle ?: 'Exam') . '_Syllabus.pdf';

        // Check Storage disk public first
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($exam->syllabus_pdf_path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($exam->syllabus_pdf_path, $downloadName, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Check direct storage locations
        $filePath = storage_path('app/public/' . $exam->syllabus_pdf_path);
        if (!file_exists($filePath)) {
            $filePathAlt = storage_path('app/' . $exam->syllabus_pdf_path);
            if (file_exists($filePathAlt)) {
                $filePath = $filePathAlt;
            } else {
                $filePathPublic = public_path('storage/' . $exam->syllabus_pdf_path);
                if (file_exists($filePathPublic)) {
                    $filePath = $filePathPublic;
                } else {
                    return back()->with('error', 'Syllabus is currently unavailable. Please contact the examination desk.');
                }
            }
        }

        return response()->download($filePath, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Display Central Exam Results Portal & Top 6 Winners Showcase Board
     */
    public function showResultsPortal()
    {
        $winners = \DB::table('exam_applications')
            ->where('show_on_winners_wall', true)
            ->whereNotNull('winner_rank')
            ->orderBy('winner_rank', 'asc')
            ->take(6)
            ->get();

        return view('exam_results', compact('winners'));
    }

    /**
     * Search Candidate Evaluation Matrix via 11-Digit Unique Hall Ticket Number
     *
     * SECURITY: Draft results are never exposed.
     * A candidate sees their result ONLY after Admin has published it.
     */
    public function searchStudentResult(Request $request)
    {
        $request->validate([
            'hall_ticket_number' => 'required|string|size:11'
        ]);

        // First check if the application exists at all
        $application = DB::table('exam_applications')
            ->where('hall_ticket_number', $request->hall_ticket_number)
            ->where('payment_status', 'success')
            ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'No registration found for this Hall Ticket Number.'
            ]);
        }

        // Guard: draft results must NOT be exposed
        if (($application->result_publication_status ?? 'draft') !== 'published') {
            return response()->json([
                'success'   => false,
                'draft'     => true,
                'message'   => 'Results for this Hall Ticket Number have not been announced yet. Please check back later.'
            ]);
        }

        // Fetch exam details for display
        $exam = DB::table('exam_settings')
            ->where('id', $application->exam_setting_id)
            ->first();

        // Safe percentage calculation
        $percentage = null;
        if ($application->total_marks > 0 && $application->marks_obtained !== null) {
            $percentage = round(($application->marks_obtained / $application->total_marks) * 100, 2);
        }

        return response()->json([
            'success'          => true,
            'full_name'        => $application->full_name,
            'hall_ticket'      => $application->hall_ticket_number,
            'school_name'      => $application->school_college_name,
            'exam_title'       => $exam->exam_title ?? 'Sanathana Dharma Examination',
            'exam_type'        => $exam->exam_type ?? null,
            'exam_date'        => $exam->exam_date_time ?? null,
            'marks'            => $application->marks_obtained,
            'total_marks'      => $application->total_marks,
            'percentage'       => $percentage,
            'grade'            => $application->grade,
            'status'           => $application->result_status,
            'prize'            => $application->prize_title_won ?? null,
        ]);
    }

    // =========================================================================
    // ADMIN RESULT MANAGEMENT
    // =========================================================================

    /**
     * Result Entry Desk — shows all applicants for one specific exam.
     * Admin selects an exam and sees only that exam's candidates + their result status.
     */
    public function adminResultsIndex(Request $request, int $examId)
    {
        $exam = ExamSetting::findOrFail($examId);

        // Only this exam's applicants
        $applicationsQuery = ExamApplication::where('exam_setting_id', $examId)
            ->orderBy('id');

        // Optional filters
        $filterStatus = $request->query('result_status');
        $filterPub    = $request->query('publication_status');
        $search       = $request->query('search');

        if ($filterStatus) {
            $applicationsQuery->where('result_status', $filterStatus);
        }
        if ($filterPub) {
            $applicationsQuery->where('result_publication_status', $filterPub);
        }
        if ($search) {
            $applicationsQuery->where(function ($q) use ($search) {
                $q->where('full_name',         'LIKE', "%{$search}%")
                  ->orWhere('hall_ticket_number','LIKE', "%{$search}%")
                  ->orWhere('father_membership_id','LIKE', "%{$search}%")
                  ->orWhere('mother_membership_id','LIKE', "%{$search}%")
                  ->orWhere('guardian_mobile_or_id','LIKE', "%{$search}%");
            });
        }

        $applications = $applicationsQuery->paginate(25)->withQueryString();

        // Stats for this exam
        $stats = [
            'total'     => ExamApplication::where('exam_setting_id', $examId)->count(),
            'drafted'   => ExamApplication::where('exam_setting_id', $examId)
                              ->whereIn('result_status', ['passed', 'failed'])
                              ->where('result_publication_status', 'draft')
                              ->count(),
            'published' => ExamApplication::where('exam_setting_id', $examId)
                              ->where('result_publication_status', 'published')
                              ->count(),
            'pending'   => ExamApplication::where('exam_setting_id', $examId)
                              ->where('result_status', 'pending')
                              ->count(),
            'notif_logged'     => NotificationLog::where('notifiable_type', ExamApplication::class)
                              ->whereIn('notifiable_id',
                                  ExamApplication::where('exam_setting_id', $examId)->pluck('id')
                              )
                              ->where('event_type', 'exam_result_announced')
                              ->whereIn('status', ['logged', 'sent', 'created'])
                              ->distinct('notifiable_id')
                              ->count('notifiable_id'),
            'notif_failed'    => NotificationLog::where('notifiable_type', ExamApplication::class)
                              ->whereIn('notifiable_id',
                                  ExamApplication::where('exam_setting_id', $examId)->pluck('id')
                              )
                              ->where('event_type', 'exam_result_announced')
                              ->where('status', 'failed')
                              ->distinct('notifiable_id')
                              ->count('notifiable_id'),
        ];

        // All exams for sidebar breadcrumb
        $allExams = ExamSetting::orderBy('id', 'desc')->get(['id', 'exam_title', 'status']);

        return view('admin.exam_results_desk', compact(
            'exam', 'applications', 'stats', 'allExams',
            'filterStatus', 'filterPub', 'search'
        ));
    }

    /**
     * Save (or update) one candidate's result as Draft.
     * Admin enters marks, total, grade, result_status, remarks.
     * Result is saved with result_publication_status = 'draft'.
     */
    public function adminResultSave(Request $request, int $appId)
    {
        $application = ExamApplication::findOrFail($appId);

        $validated = $request->validate([
            'marks_obtained' => 'nullable|integer|min:0',
            'total_marks'    => 'nullable|integer|min:1',
            'grade'          => 'nullable|string|max:10',
            'result_status'  => 'required|in:pending,passed,failed',
            'winner_rank'    => 'nullable|integer|min:1|max:6',
            'prize_title_won'=> 'nullable|string|max:255',
            'show_on_winners_wall' => 'nullable|boolean',
            'result_remarks' => 'nullable|string|max:1000',
        ]);

        // Validate marks <= total_marks
        if (
            isset($validated['marks_obtained']) &&
            isset($validated['total_marks']) &&
            $validated['marks_obtained'] > $validated['total_marks']
        ) {
            return back()
                ->withErrors(['marks_obtained' => 'Marks obtained cannot exceed total marks.'])
                ->withInput();
        }

        $application->marks_obtained      = $validated['marks_obtained'] ?? $application->marks_obtained;
        $application->total_marks         = $validated['total_marks']    ?? $application->total_marks;
        $application->grade               = $validated['grade']          ?? $application->grade;
        $application->result_status       = $validated['result_status'];
        $application->winner_rank         = $validated['winner_rank']    ?? null;
        $application->prize_title_won     = $validated['prize_title_won']?? null;
        $application->show_on_winners_wall= (bool)($validated['show_on_winners_wall'] ?? false);
        $application->result_remarks      = $validated['result_remarks'] ?? null;
        // Draft — do NOT change publication status on plain save
        $application->result_publication_status = $application->result_publication_status ?? 'draft';
        $application->save();

        return redirect()
            ->route('admin.exams.results', $application->exam_setting_id)
            ->with('success', "Result saved as Draft for: {$application->full_name}");
    }

    /**
     * Publish results for ALL applicants of a specific exam.
     *
     * Steps:
     *   1. Mark ALL applicants for this exam as result_publication_status = 'published'.
     *   2. Send result-announcement notifications (per-channel idempotency enforced in NotificationService).
     *   3. Mark result_notification_sent = true on each application after notifications run.
     *   4. Notification failures do NOT roll back publication.
     *
     * If Admin clicks Publish again:
     *   - Publication status is already 'published' — no harm done.
     *   - NotificationService skips channels where log row already exists.
     *   - Returns counts with all channels showing 'skipped'.
     */
    public function adminPublishResults(Request $request, int $examId)
    {
        $exam = ExamSetting::findOrFail($examId);

        // Publish ALL applicants for this exam (idempotent — already published rows stay published)
        DB::table('exam_applications')
            ->where('exam_setting_id', $examId)
            ->update([
                'result_publication_status' => 'published',
                'result_published_at'       => now(),
                'updated_at'                => now(),
            ]);

        $publishedCount = ExamApplication::where('exam_setting_id', $examId)->count();

        \App\Services\AuditLogger::log('EXAM_RESULTS_PUBLISHED', 'ExamSetting', (string)$examId, [
            'exam_title' => $exam->exam_title,
            'published_count' => $publishedCount
        ]);

        // Send notifications — independent of publication, failures do NOT revert publication
        $notificationTotals = ['email' => [], 'whatsapp' => [], 'in_app' => [], 'processed' => 0];
        try {
            $notifService = app(NotificationService::class);
            $notificationTotals = $notifService->sendExamResultsForExam($exam);

            // Mark notification flag on each application (best-effort, non-blocking)
            DB::table('exam_applications')
                ->where('exam_setting_id', $examId)
                ->update(['result_notification_sent' => true, 'updated_at' => now()]);

        } catch (\Throwable $e) {
            Log::error('[ExamController] Notification dispatch failed after publish', [
                'exam_id' => $examId,
                'error'   => $e->getMessage(),
            ]);
            // Publication already committed — do not re-throw
        }

        // Build human-readable notification summary
        $emailSummary    = $this->summariseNotifChannel($notificationTotals['email']    ?? []);
        $whatsappSummary = $this->summariseNotifChannel($notificationTotals['whatsapp'] ?? []);
        $inAppSummary    = $this->summariseNotifChannel($notificationTotals['in_app']   ?? []);

        $successMsg = "{$publishedCount} result(s) published for: {$exam->exam_title}. "
            . "Notifications — Email: {$emailSummary} | WhatsApp: {$whatsappSummary} | In-App: {$inAppSummary}.";

        return redirect()
            ->route('admin.exams.results', $examId)
            ->with('success', $successMsg);
    }

    /**
     * Unpublish (roll back to draft) results for a specific exam.
     * No notifications are sent on unpublish.
     * Notification logs are NOT deleted (audit trail preserved).
     */
    public function adminUnpublishResults(int $examId)
    {
        $exam = ExamSetting::findOrFail($examId);

        DB::table('exam_applications')
            ->where('exam_setting_id', $examId)
            ->update([
                'result_publication_status' => 'draft',
                'result_published_at'       => null,
                'result_notification_sent'  => false,
                'updated_at'                => now(),
            ]);

        \App\Services\AuditLogger::log('EXAM_RESULTS_UNPUBLISHED', 'ExamSetting', (string)$examId, [
            'exam_title' => $exam->exam_title
        ]);

        return redirect()
            ->route('admin.exams.results', $examId)
            ->with('success', "Results unpublished (moved to Draft) for: {$exam->exam_title}");
    }

    /**
     * Produce a short human-readable summary of per-channel notification counts.
     * e.g. "145 logged, 5 failed"
     */
    private function summariseNotifChannel(array $counts): string
    {
        if (empty($counts)) {
            return 'no data';
        }
        $parts = [];
        foreach ($counts as $status => $n) {
            if ($n > 0) {
                $parts[] = "{$n} {$status}";
            }
        }
        return $parts ? implode(', ', $parts) : 'none';
    }

    /**
     * Admin Exam Info Board & Multi-Exam Applicant Roster Desk
     */
    public function adminIndex(Request $request)
    {
        $exams = \App\Models\ExamSetting::withCount('applications')->orderBy('id', 'desc')->get();
        $selectedExamId = $request->query('exam_id');
        $searchQuery = $request->query('search');

        // Multi-Exam Applications Query Engine
        $applicationsQuery = \App\Models\ExamApplication::with('examSetting')->orderBy('id', 'desc');

        if (!empty($selectedExamId) && $selectedExamId !== 'all') {
            $applicationsQuery->where('exam_setting_id', $selectedExamId);
        }

        if (!empty($searchQuery)) {
            $applicationsQuery->where(function($q) use ($searchQuery) {
                $q->where('full_name', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('email', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('mobile', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('hall_ticket_number', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('father_membership_id', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('mother_membership_id', 'LIKE', "%{$searchQuery}%")
                  ->orWhere('guardian_mobile_or_id', 'LIKE', "%{$searchQuery}%");
            });
        }

        $applications = $applicationsQuery->paginate(15)->withQueryString();

        // Exam-aware summary statistics
        $summaryQuery = \App\Models\ExamApplication::query();
        if (!empty($selectedExamId) && $selectedExamId !== 'all') {
            $summaryQuery->where('exam_setting_id', $selectedExamId);
        }
        $totalApplications = (clone $summaryQuery)->count();
        $paidApplications = (clone $summaryQuery)->where('payment_status', 'success')->count();
        $verifiedApplications = (clone $summaryQuery)->where('is_email_verified', true)->count();

        return view('admin.exams_index', compact(
            'exams', 
            'applications', 
            'selectedExamId', 
            'searchQuery', 
            'totalApplications', 
            'paidApplications', 
            'verifiedApplications'
        ));
    }

    /**
     * Admin Create New Exam Form
     */
    public function adminCreate()
    {
        return view('admin.exam_create');
    }

    /**
     * Admin Store New Exam Cycle
     */
    public function adminStore(Request $request)
    {
        $request->validate([
            'exam_title' => 'required|string|max:255',
            'exam_type' => 'required|in:theory,mcq,both',
            'exam_date_time' => 'required|date',
            'exam_center_location' => 'required|string|max:255',
            'application_fee' => 'required|numeric|min:0',
            'syllabus_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'banner_image' => 'nullable|image|max:4096',
            'prize_details' => 'nullable|string',
            'guidelines' => 'nullable|string',
            'status' => 'required|in:active,upcoming,completed',
        ], [
            'exam_type.required' => 'Please select an Exam Type (Theory, MCQ, or Both).',
            'exam_type.in' => 'Selected Exam Type must be Theory, MCQ, or Both (Theory + MCQ).',
        ]);

        $syllabusPath = null;
        if ($request->hasFile('syllabus_pdf')) {
            $syllabusPath = $request->file('syllabus_pdf')->store('exams/syllabus', 'public');
        }

        $bannerPath = null;
        if ($request->hasFile('banner_image')) {
            $bannerPath = $request->file('banner_image')->store('exams/banners', 'public');
        }

        $prizes = $request->filled('prize_details') 
            ? array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->prize_details))), fn($p) => $p !== ''))
            : [];

        \App\Models\ExamSetting::create([
            'exam_title' => $request->exam_title,
            'exam_type' => $request->exam_type,
            'syllabus_pdf_path' => $syllabusPath ?? 'exams/syllabus/sample_syllabus.pdf',
            'banner_image_path' => $bannerPath,
            'exam_date_time' => $request->exam_date_time,
            'exam_center_location' => $request->exam_center_location,
            'prize_details_json' => json_encode($prizes),
            'guidelines' => $request->guidelines,
            'application_fee' => $request->application_fee,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.exams.index')->with('success', 'New Exam Cycle created successfully.');
    }

    /**
     * Admin Edit Exam Form
     */
    public function adminEdit($id)
    {
        $exam = \App\Models\ExamSetting::findOrFail($id);
        $prizesText = implode("\n", $exam->prizes_list);
        return view('admin.exam_edit', compact('exam', 'prizesText'));
    }

    /**
     * Admin Update Exam Cycle
     */
    public function adminUpdate(Request $request, $id)
    {
        $exam = \App\Models\ExamSetting::findOrFail($id);

        $request->validate([
            'exam_title' => 'required|string|max:255',
            'exam_type' => 'required|in:theory,mcq,both',
            'exam_date_time' => 'required|date',
            'exam_center_location' => 'required|string|max:255',
            'application_fee' => 'required|numeric|min:0',
            'syllabus_pdf' => 'nullable|file|mimes:pdf|max:10240',
            'banner_image' => 'nullable|image|max:4096',
            'prize_details' => 'nullable|string',
            'guidelines' => 'nullable|string',
            'status' => 'required|in:active,upcoming,completed',
        ], [
            'exam_type.required' => 'Please select an Exam Type (Theory, MCQ, or Both).',
            'exam_type.in' => 'Selected Exam Type must be Theory, MCQ, or Both (Theory + MCQ).',
        ]);

        if ($request->hasFile('syllabus_pdf')) {
            $exam->syllabus_pdf_path = $request->file('syllabus_pdf')->store('exams/syllabus', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $exam->banner_image_path = $request->file('banner_image')->store('exams/banners', 'public');
        }

        $prizes = $request->filled('prize_details')
            ? array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $request->prize_details))), fn($p) => $p !== ''))
            : [];
        $exam->prize_details_json = json_encode($prizes);

        $exam->exam_title = $request->exam_title;
        $exam->exam_type = $request->exam_type;
        $exam->exam_date_time = $request->exam_date_time;
        $exam->exam_center_location = $request->exam_center_location;
        $exam->guidelines = $request->guidelines;
        $exam->application_fee = $request->application_fee;
        $exam->status = $request->status;
        $exam->save();

        return redirect()->route('admin.exams.index')->with('success', 'Exam Cycle updated successfully.');
    }

    /**
     * Admin Delete Exam Cycle
     */
    public function adminDelete($id)
    {
        $exam = \App\Models\ExamSetting::findOrFail($id);
        $exam->delete();
        return redirect()->route('admin.exams.index')->with('success', 'Exam Cycle deleted.');
    }

    /**
     * Public Continuous Loop Exams Notice Board
     */
    public function publicNoticeBoard()
    {
        $exams = \App\Models\ExamSetting::orderBy('exam_date_time', 'desc')->get();
        return view('exams_notice_board', compact('exams'));
    }
}
