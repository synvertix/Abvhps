<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Membership;
use App\Services\Fast2SmsService;
use App\Services\RazorpayPaymentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class MembershipController extends Controller
{
    // 1. Show the Mobile OTP input screen
    public function showOtpForm()
    {
        return view('membership_otp');
    }

    // 2. Generate and Send OTP via Fast2SMS
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10'
        ]);

        $phone = $request->input('phone');
        $otp = random_int(100000, 999999);
        $expiredAt = Carbon::now()->addMinutes(5);

        DB::table('phone_verifications')->updateOrInsert(
            ['phone' => $phone],
            [
                'otp' => $otp,
                'is_verified' => false,
                'expired_at' => $expiredAt,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Dispatch real OTP through Fast2SMS gateway (DLT / OTP route)
        $smsResult = Fast2SmsService::sendOtp($phone, $otp);

        if (!$smsResult['success'] && ($smsResult['status'] ?? '') !== 'skipped') {
            return redirect()->back()
                ->with('error', 'SMS delivery failure: ' . ($smsResult['message'] ?? 'Gateway rejected request') . '. Please verify your mobile number or try again.');
        }

        return redirect()->back()
            ->with('otp_sent_to', $phone)
            ->with('success', 'OTP sent successfully. Please check your registered mobile number.');
    }

    // 3. Verify OTP & Check Payment Status
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
            'phone' => 'required|digits:10'
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');

        $verification = DB::table('phone_verifications')
            ->where('phone', $phone)
            ->where('otp', $otp)
            ->where('is_verified', false)
            ->where('expired_at', '>', Carbon::now())
            ->first();

        if (!$verification) {
            return redirect('/membership')
                ->with('otp_sent_to', $phone)
                ->with('error', 'Invalid or Expired OTP code. Please try again.');
        }

        // Burn the OTP to enforce strict single-use verification
        DB::table('phone_verifications')->where('phone', $phone)->update([
            'is_verified' => true,
            'expired_at' => Carbon::now()->subMinute(),
            'updated_at' => now(),
        ]);

        session(['verified_membership_phone' => $phone]);

        $member = Membership::where('phone', $phone)->first();

        if ($member && (bool) $member->is_completed) {
            return redirect('/membership/view-card')->with('success', 'Welcome back! Your completed membership ID card is ready.');
        }

        if (self::hasVerifiedMembershipPayment($member)) {
            if (self::hasVerifiedMembershipIdentity($member)) {
                return redirect('/membership/application')->with('success', 'Welcome back! Your payment & identity are verified.');
            }
            return redirect('/membership/identity')->with('success', 'Welcome back! Your payment is already verified. Please complete identity verification.');
        }

        return redirect('/membership/payment');
    }

    /**
     * Helper check: Does member have a genuine server-verified Razorpay payment record?
     */
    public static function hasVerifiedMembershipPayment(?Membership $member): bool
    {
        if (!$member) {
            return false;
        }

        $hasAuditFields = $member->payment_status === 'success'
            && $member->payment_gateway === 'razorpay'
            && !empty($member->payment_order_id)
            && !empty($member->payment_id)
            && !is_null($member->payment_verified_at);

        if (!$hasAuditFields) {
            return false;
        }

        $amount = (float) $member->payment_amount;

        // Canonical current amount (₹100.00) unlocks the current registration/application flow
        if ($amount === (float) RazorpayPaymentService::MEMBERSHIP_AMOUNT_RUPEES) {
            return true;
        }

        // Historical temporary test fee (₹1.00) is accepted ONLY for already completed historical records
        if ($amount === (float) RazorpayPaymentService::LEGACY_TEST_MEMBERSHIP_AMOUNT_RUPEES && (bool) $member->is_completed) {
            return true;
        }

        return false;
    }

    /**
     * Helper check: Does member have valid application access?
     * Either completed historical membership (is_completed === true) OR genuine verified Razorpay payment.
     */
    public static function hasValidMembershipAccess(?Membership $member): bool
    {
        if (!$member) {
            return false;
        }

        return (bool) $member->is_completed || self::hasVerifiedMembershipPayment($member);
    }

    /**
     * Helper check: Delegates to canonical Membership::hasVerifiedIdentity() model method.
     */
    public static function hasVerifiedMembershipIdentity(?Membership $member): bool
    {
        return $member?->hasVerifiedIdentity() ?? false;
    }

    // 4. Display the Membership Payment Screen
    public function showPaymentPage()
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $member = Membership::where('phone', $phone)->first();
        if ($member && (bool) $member->is_completed) {
            return redirect('/membership/view-card');
        }

        if (self::hasVerifiedMembershipPayment($member)) {
            return redirect('/membership/application');
        }

        return view('membership_payment');
    }

    /**
     * 5. Initiate Razorpay Order for ₹100 Membership Payment.
     */
    public function initiateRazorpayPayment(Request $request)
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $member = Membership::firstOrCreate(['phone' => $phone]);

        if (self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'success'      => true,
                'already_paid' => true,
                'redirect_url' => '/membership/application',
            ]);
        }

        $internalRef = 'ABVHPS_MEM_' . (string) Str::uuid();
        $razorpayService = new RazorpayPaymentService();
        $orderResult = $razorpayService->createMembershipOrder($internalRef, $phone);

        if (!$orderResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $orderResult['message'] ?? 'Failed to initialize Razorpay payment order.',
            ], 422);
        }

        $member->update([
            'payment_status'   => 'pending',
            'payment_gateway'  => 'razorpay',
            'payment_order_id' => $orderResult['order_id'],
            'payment_amount'   => RazorpayPaymentService::MEMBERSHIP_AMOUNT_RUPEES,
        ]);

        return response()->json([
            'success'      => true,
            'key_id'       => $orderResult['key_id'],
            'order_id'     => $orderResult['order_id'],
            'amount_paise' => RazorpayPaymentService::MEMBERSHIP_AMOUNT_PAISE,
            'currency'     => 'INR',
        ]);
    }

    /**
     * 5b. Verify Razorpay Payment Signature and Confirm Captured Payment Facts.
     */
    public function verifyRazorpayPayment(Request $request)
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $validated = $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'razorpay_order_id'    => 'nullable|string',
        ]);

        $member = Membership::where('phone', $phone)->first();

        if (!$member || empty($member->payment_order_id)) {
            return response()->json([
                'success' => false,
                'message' => 'No pending payment order found for this membership session.',
            ], 422);
        }

        // Browser order ID match check if provided
        if (!empty($validated['razorpay_order_id']) && $validated['razorpay_order_id'] !== $member->payment_order_id) {
            $maskedPhone = 'XXXXXX' . substr($phone, -4);
            Log::warning("Razorpay verify order ID mismatch for phone {$maskedPhone}: browser {$validated['razorpay_order_id']} vs DB {$member->payment_order_id}");
            return response()->json([
                'success' => false,
                'message' => 'Payment order ID mismatch.',
            ], 422);
        }

        $serverOrderId = $member->payment_order_id;
        $razorpayService = new \App\Services\RazorpayPaymentService();
        $verifyResult = $razorpayService->verifyMembershipPayment(
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
            $serverOrderId
        );

        if (!$verifyResult['success']) {
            return response()->json([
                'success' => false,
                'message' => $verifyResult['message'] ?? 'Payment verification failed.',
            ], 422);
        }

        $paymentId = $validated['razorpay_payment_id'];

        try {
            DB::transaction(function () use ($member, $paymentId) {
                $lockedMember = Membership::where('id', $member->id)->lockForUpdate()->first();

                // Reject reuse of the same Razorpay payment ID across different memberships with locking
                $duplicateExists = Membership::where('payment_id', $paymentId)
                    ->where('id', '!=', $lockedMember->id)
                    ->lockForUpdate()
                    ->exists();

                if ($duplicateExists) {
                    throw new \Exception('Payment ID has already been used for another membership.');
                }

                // Generate 12-digit membership ID idempotently if not present
                if (empty($lockedMember->membership_id)) {
                    do {
                        $randomId = (string) random_int(100000000000, 999999999999);
                        $exists = Membership::where('membership_id', $randomId)->exists();
                    } while ($exists);
                    $lockedMember->membership_id = $randomId;
                }

                $lockedMember->payment_status      = 'success';
                $lockedMember->payment_gateway     = 'razorpay';
                $lockedMember->payment_id          = $paymentId;
                $lockedMember->payment_amount      = RazorpayPaymentService::MEMBERSHIP_AMOUNT_RUPEES;
                $lockedMember->payment_verified_at = Carbon::now();
                $lockedMember->save();
            });
        } catch (\Throwable $e) {
            Log::error("Razorpay membership completion transaction failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Database error confirming payment.',
            ], 422);
        }

        return response()->json([
            'success'      => true,
            'redirect_url' => '/membership/application',
        ]);
    }

    // 5c. Render Identity Verification Page
    public function showIdentityPage()
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $member = Membership::where('phone', $phone)->first();

        if (!self::hasValidMembershipAccess($member)) {
            return redirect('/membership/payment')->with('error', 'Please complete membership payment before identity verification.');
        }

        if (self::hasVerifiedMembershipIdentity($member)) {
            return redirect('/membership/application')->with('info', 'Your identity is already verified.');
        }

        return view('membership_identity', compact('member', 'phone'));
    }

    /**
     * Atomically persist verified identity on member only if not already verified.
     *
     * SECURITY INVARIANTS:
     * 1. Cashfree HTTP call is made BEFORE acquiring the DB row lock.
     * 2. DB::transaction with lockForUpdate ensures the first successful verification is permanent.
     * 3. Concurrent or delayed verifications cannot overwrite an already verified identity.
     *
     * @return array ['persisted' => bool, 'member' => Membership]
     */
    private function persistVerifiedIdentityIfUnverified(
        int $memberId,
        string $method,
        string $provider,
        ?string $refId,
        string $verifiedName,
        ?string $last4 = null,
        ?string $verificationId = null,
        array $extraAttributes = []
    ): array {
        return DB::transaction(function () use ($memberId, $method, $provider, $refId, $verifiedName, $last4, $verificationId, $extraAttributes) {
            $lockedMember = Membership::lockForUpdate()->findOrFail($memberId);

            if ($lockedMember->hasVerifiedIdentity()) {
                return [
                    'persisted' => false,
                    'member'    => $lockedMember,
                ];
            }

            if (!empty($extraAttributes)) {
                $lockedMember->fill($extraAttributes);
            }

            $now = Carbon::now('Asia/Kolkata');

            $lockedMember->identity_verified                  = true;
            $lockedMember->identity_verification_method        = $method;
            $lockedMember->identity_verification_provider      = $provider;
            $lockedMember->identity_verification_reference_id = $refId;
            $lockedMember->identity_verification_id           = $verificationId;
            $lockedMember->identity_verified_name             = $verifiedName;
            $lockedMember->identity_document_last4            = $last4;
            $lockedMember->identity_verified_at               = $now;

            if ($method === 'aadhaar') {
                $lockedMember->is_aadhaar_verified      = true;
                $lockedMember->aadhaar_verification_ref = $refId;
                $lockedMember->aadhaar_verified_at      = $now;
                $lockedMember->full_name                = $verifiedName;
            }

            $lockedMember->save();

            return [
                'persisted' => true,
                'member'    => $lockedMember,
            ];
        });
    }

    /**
     * Verify PAN for Membership Identity.
     */
    public function verifyPanIdentity(Request $request)
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $member = Membership::where('phone', $phone)->first();
        if (!$member || !self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership payment is required before identity verification.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $member->identity_verified_name ?? $member->full_name,
                'message'       => 'Identity is already successfully verified.',
            ]);
        }

        $validated = $request->validate([
            'pan_number' => 'required|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/i',
        ]);

        $pan = strtoupper(trim($validated['pan_number']));
        $secureIdService = new \App\Services\CashfreeSecureIdService();
        $result = $secureIdService->verifyPan($pan);

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'PAN verification failed. Please check the PAN number and try again.',
            ], 422);
        }

        $verifiedName = $result['verified_name'] ?? null;
        if (empty($verifiedName)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Provider could not verify full legal name on the document.',
            ], 422);
        }

        $saveResult = $this->persistVerifiedIdentityIfUnverified(
            $member->id,
            'pan',
            'cashfree',
            $result['reference_id'] ?? null,
            $verifiedName,
            substr($pan, -4),
            null
        );

        if (!$saveResult['persisted']) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $saveResult['member']->identity_verified_name ?? $saveResult['member']->full_name,
                'message'       => 'Identity is already verified with another document.',
            ]);
        }

        return response()->json([
            'status'        => 'success',
            'verified_name' => $verifiedName,
            'message'       => 'PAN verified successfully!',
        ]);
    }

    /**
     * Verify Voter ID for Membership Identity.
     */
    public function verifyVoterIdIdentity(Request $request)
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $member = Membership::where('phone', $phone)->first();
        if (!$member || !self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership payment is required before identity verification.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $member->identity_verified_name ?? $member->full_name,
                'message'       => 'Identity is already successfully verified.',
            ]);
        }

        $validated = $request->validate([
            'voter_id' => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9\/-]+$/',
        ]);

        $epic = strtoupper(trim($validated['voter_id']));
        $verificationId = 'ABV_' . (string) Str::uuid();

        $secureIdService = new \App\Services\CashfreeSecureIdService();
        $result = $secureIdService->verifyVoterId($epic, $verificationId);

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Voter ID verification failed. Please check the EPIC number and try again.',
            ], 422);
        }

        $verifiedName = $result['verified_name'] ?? null;
        if (empty($verifiedName)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Provider could not verify full legal name on the document.',
            ], 422);
        }

        $saveResult = $this->persistVerifiedIdentityIfUnverified(
            $member->id,
            'voter_id',
            'cashfree',
            $result['reference_id'] ?? null,
            $verifiedName,
            substr($epic, -4),
            $verificationId
        );

        if (!$saveResult['persisted']) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $saveResult['member']->identity_verified_name ?? $saveResult['member']->full_name,
                'message'       => 'Identity is already verified with another document.',
            ]);
        }

        return response()->json([
            'status'        => 'success',
            'verified_name' => $verifiedName,
            'message'       => 'Voter ID verified successfully!',
        ]);
    }

    /**
     * Verify Driving Licence for Membership Identity.
     */
    public function verifyDrivingLicenceIdentity(Request $request)
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $member = Membership::where('phone', $phone)->first();
        if (!$member || !self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership payment is required before identity verification.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $member->identity_verified_name ?? $member->full_name,
                'message'       => 'Identity is already successfully verified.',
            ]);
        }

        $validated = $request->validate([
            'dl_number' => 'required|string|min:5|max:30',
            'dob'       => 'required|date_format:Y-m-d|before:today',
        ]);

        $dlNumber = strtoupper(trim($validated['dl_number']));
        $dob      = trim($validated['dob']);
        $verificationId = 'ABV_' . (string) Str::uuid();

        $secureIdService = new \App\Services\CashfreeSecureIdService();
        $result = $secureIdService->verifyDrivingLicence($dlNumber, $dob, $verificationId);

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Driving Licence verification failed. Please check the details and try again.',
            ], 422);
        }

        $verifiedName = $result['verified_name'] ?? null;
        if (empty($verifiedName)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Provider could not verify full legal name on the document.',
            ], 422);
        }

        $saveResult = $this->persistVerifiedIdentityIfUnverified(
            $member->id,
            'driving_licence',
            'cashfree',
            $result['reference_id'] ?? null,
            $verifiedName,
            substr($dlNumber, -4),
            $verificationId
        );

        if (!$saveResult['persisted']) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $saveResult['member']->identity_verified_name ?? $saveResult['member']->full_name,
                'message'       => 'Identity is already verified with another document.',
            ]);
        }

        return response()->json([
            'status'        => 'success',
            'verified_name' => $verifiedName,
            'message'       => 'Driving Licence verified successfully!',
        ]);
    }

    /**
     * Verify Passport for Membership Identity.
     */
    public function verifyPassportIdentity(Request $request)
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        $member = Membership::where('phone', $phone)->first();
        if (!$member || !self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership payment is required before identity verification.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $member->identity_verified_name ?? $member->full_name,
                'message'       => 'Identity is already successfully verified.',
            ]);
        }

        $validated = $request->validate([
            'file_number' => 'required|string|min:8|max:20',
            'dob'         => 'required|date_format:Y-m-d|before:today',
        ]);

        $fileNumber = strtoupper(trim($validated['file_number']));
        $dob        = trim($validated['dob']);
        $verificationId = 'ABV_' . (string) Str::uuid();

        $secureIdService = new \App\Services\CashfreeSecureIdService();
        $result = $secureIdService->verifyPassport($fileNumber, $dob, $verificationId);

        if (!$result['success']) {
            return response()->json([
                'status'  => 'error',
                'message' => $result['message'] ?? 'Passport verification failed. Please check the details and try again.',
            ], 422);
        }

        $verifiedName = $result['verified_name'] ?? null;
        if (empty($verifiedName)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Provider could not verify full legal name on the document.',
            ], 422);
        }

        $saveResult = $this->persistVerifiedIdentityIfUnverified(
            $member->id,
            'passport',
            'cashfree',
            $result['reference_id'] ?? null,
            $verifiedName,
            substr($fileNumber, -4),
            $verificationId
        );

        if (!$saveResult['persisted']) {
            return response()->json([
                'status'        => 'already_verified',
                'verified_name' => $saveResult['member']->identity_verified_name ?? $saveResult['member']->full_name,
                'message'       => 'Identity is already verified with another document.',
            ]);
        }

        return response()->json([
            'status'        => 'success',
            'verified_name' => $verifiedName,
            'message'       => 'Passport verified successfully!',
        ]);
    }

    // 6. Show Registration Application Form (Linking directly to original layout file)
    public function showApplicationForm()
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $member = Membership::where('phone', $phone)->first();

        if (!self::hasValidMembershipAccess($member)) {
            return redirect('/membership/payment')->with('error', 'Please complete the membership payment first.');
        }

        if (!self::hasVerifiedMembershipIdentity($member)) {
            return redirect('/membership/identity')->with('warning', 'Please complete identity verification before accessing the application form.');
        }

        // 4-4-4 formatted layout with spaces (e.g., 4318 2764 1156)
        $formattedId = implode(' ', str_split($member->membership_id, 4));

        return view('membership_application', compact('formattedId', 'phone', 'member'));
    }

    /**
     * 6b. Verify Aadhaar via Backend Security Pipeline
     * Returns actual verified applicant data when available or validates format
     * Never returns fake fallback/default applicant names.
     */
    /**
     * 6b. Verify Aadhaar & Name via Cashfree Secure ID Pipeline
     *
     * Exact Flow:
     * 1. Validate Aadhaar format & user-entered full name.
     * 2. Authorize requester against active session member record.
     * 3. Call Cashfree Secure ID Verification API server-to-server.
     * 4. Retrieve authoritative verified identity from Cashfree.
     * 5. Perform strict server-side normalized name comparison.
     * 6. If match: Persist verified Cashfree identity & return auto-population data.
     * 7. If mismatch: Reject verification, do NOT save unverified identity, return name mismatch response.
     */
    /**
     * @deprecated Legacy unrouted offline Aadhaar verification method. Active flow uses DigiLocker startAadhaarVerification.
     */
    public function verifyAadhaar(Request $request)
    {
        $validated = $request->validate([
            'aadhaar_number' => 'required|digits:12',
            'full_name'      => 'required|string|min:2|max:255',
        ]);

        $aadhaar     = (string) $validated['aadhaar_number'];
        $enteredName = (string) $validated['full_name'];

        // Strict Aadhaar format check: First digit cannot be 0 or 1 per UIDAI specifications
        if ($aadhaar[0] === '0' || $aadhaar[0] === '1') {
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Invalid Aadhaar number format. Aadhaar numbers cannot start with 0 or 1.'
            ], 422);
        }

        $phone = session('verified_membership_phone');

        if (!$phone) {
            Log::warning("Aadhaar Verification: Missing active phone session.");
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Active membership session not found. Please verify your phone number first.'
            ], 401);
        }

        $maskedPhone   = 'XXXXXX' . substr($phone, -4);
        $maskedAadhaar = 'XXXX-XXXX-' . substr($aadhaar, -4);

        $member = Membership::where('phone', $phone)->first();

        if (!$member) {
            Log::warning("Aadhaar Verification: Member record not found for {$maskedPhone}.");
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Membership record not found for this phone number.'
            ], 404);
        }

        if (!self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Membership payment is required before Aadhaar verification.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            return response()->json([
                'status'              => 'success',
                'is_name_matched'     => true,
                'is_aadhaar_verified' => true,
                'message'             => 'Identity is already verified with an official document.',
            ]);
        }

        // Server-controlled verification reference
        $verificationId = 'ABVHPS_AADHAAR_' . $member->id . '_' . time();

        // 3. Dispatch to Cashfree Secure ID Service
        $secureIdService = new \App\Services\CashfreeSecureIdService();
        $cfResult = $secureIdService->verifyAadhaar($aadhaar, $verificationId, $enteredName);

        if (!$cfResult['success']) {
            Log::warning("Aadhaar Verification: Cashfree gateway verification failed for {$maskedPhone}.", [
                'status'  => $cfResult['status'] ?? 'FAILED',
                'message' => $cfResult['message'] ?? 'Gateway error',
            ]);

            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => $cfResult['message'] ?? 'Aadhaar verification failed. Please check the Aadhaar number and try again.',
            ], 422);
        }

        // 4. Extract authoritative verified name from Cashfree response
        $verifiedName = $cfResult['verified_name'] ?? ($cfResult['data']['name'] ?? null);

        if (empty($verifiedName)) {
            Log::error("Aadhaar Verification: Cashfree response missing verified name for {$maskedPhone}.");
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Aadhaar verification succeeded, but verified name could not be retrieved from provider records.',
            ], 422);
        }

        // 5. Strict server-side Name Comparison
        $isNameMatched = \App\Services\CashfreeSecureIdService::compareNames($enteredName, $verifiedName);

        if (!$isNameMatched) {
            Log::warning("Aadhaar Verification: Name mismatch detected for member {$maskedPhone}. Entered name does not match Cashfree verified name.");

            // Do NOT mark Aadhaar verified, do NOT save unverified name / identity
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Aadhaar number verified, but the name does not match Aadhaar records.',
            ], 200);
        }

        // 6. Name MATCHES: Build extra payload using authoritative Cashfree identity data
        $extraPayload = [
            'aadhaar_number' => $aadhaar,
        ];

        $cfData = $cfResult['data'] ?? [];

        if (!empty($cfData['dob'])) {
            $extraPayload['dob'] = $cfData['dob'];
        }
        if (!empty($cfData['gender'])) {
            $extraPayload['gender'] = $cfData['gender'];
        }
        if (!empty($cfData['father_or_husband_name'])) {
            $extraPayload['father_or_husband_name'] = $cfData['father_or_husband_name'];
        }
        if (!empty($cfData['permanent_address'])) {
            $extraPayload['permanent_address'] = $cfData['permanent_address'];
        }
        if (!empty($cfData['pincode'])) {
            $extraPayload['pincode'] = $cfData['pincode'];
        }
        if (!empty($cfData['district'])) {
            $extraPayload['district'] = $cfData['district'];
        }
        if (!empty($cfData['state'])) {
            $extraPayload['state'] = $cfData['state'];
        }

        // Perform atomic persistence
        $saveResult = $this->persistVerifiedIdentityIfUnverified(
            $member->id,
            'aadhaar',
            'cashfree',
            $cfResult['ref_id'] ?? $verificationId,
            $verifiedName,
            substr($aadhaar, -4),
            $verificationId,
            $extraPayload
        );

        if (!$saveResult['persisted']) {
            return response()->json([
                'status'              => 'success',
                'is_name_matched'     => true,
                'is_aadhaar_verified' => true,
                'message'             => 'Identity was already verified with another document.',
            ]);
        }

        Log::info("Aadhaar Verification: Successfully verified and persisted Aadhaar & Name for member {$maskedPhone}.", [
            'ref_id'              => $updatePayload['aadhaar_verification_ref'],
            'is_aadhaar_verified' => true,
            'is_name_matched'     => true,
        ]);

        // Format verified data response for auto-fill in registration form
        $responseData = [
            'full_name'              => $member->full_name,
            'dob'                    => $member->dob,
            'gender'                 => $member->gender,
            'father_or_husband_name' => $member->father_or_husband_name,
            'permanent_address'      => $member->permanent_address,
            'pincode'                => $member->pincode,
            'district'               => $member->district,
            'state'                  => $member->state,
        ];

        return response()->json([
            'status'              => 'success',
            'is_name_matched'     => true,
            'is_aadhaar_verified' => true,
            'message'             => 'Aadhaar & Name Verified Successfully!',
            'verified_name'       => $member->full_name,
            'data'                => array_filter($responseData, fn($v) => !is_null($v)),
            'masked_aadhaar'      => $maskedAadhaar,
        ]);
    }

    /**
     * Clear temporary DigiLocker verification session state.
     */
    private function clearDigiLockerSession(): void
    {
        session()->forget([
            'digilocker_verification_id',
            'digilocker_member_id',
            'digilocker_reference_id',
            'digilocker_aadhaar_encrypted',
            'digilocker_started_at',
        ]);
    }

    /**
     * Start DigiLocker Aadhaar verification process for membership application.
     */
    public function startAadhaarVerification(Request $request)
    {
        // Security Requirement 1: MUST use ONLY session('verified_membership_phone'). No request fallback.
        $phone = session('verified_membership_phone');

        if (!$phone) {
            Log::warning("DigiLocker Start: Missing verified membership phone session.");
            return response()->json([
                'status'  => 'error',
                'message' => 'Active membership session not found. Please verify your phone number first.',
            ], 401);
        }

        // Clear any previous/stale DigiLocker session state before starting fresh
        $this->clearDigiLockerSession();

        $validated = $request->validate([
            'aadhaar_number' => 'required|digits:12',
        ]);

        $aadhaar = (string) $validated['aadhaar_number'];

        // Strict format check: Aadhaar numbers cannot start with 0 or 1
        if ($aadhaar[0] === '0' || $aadhaar[0] === '1') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid Aadhaar number format. Aadhaar numbers cannot start with 0 or 1.',
            ], 422);
        }

        $maskedPhone = 'XXXXXX' . substr($phone, -4);
        $member = Membership::where('phone', $phone)->first();

        if (!$member) {
            Log::warning("DigiLocker Start: Member record not found for phone {$maskedPhone}.");
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership record not found for this phone number.',
            ], 404);
        }

        if (!self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Membership payment is required before Aadhaar verification.',
            ], 403);
        }

        // Security Requirement 4: Cryptographically strong, unpredictable verification ID (Str::uuid)
        $verificationId = 'ABV_' . (string) Str::uuid();

        // Security Requirement 5: Protect raw Aadhaar in session using Laravel Crypt
        try {
            $encryptedAadhaar = Crypt::encryptString($aadhaar);
        } catch (\Throwable $e) {
            Log::error("DigiLocker Start: Failed to encrypt session Aadhaar for phone {$maskedPhone}.");
            return response()->json([
                'status'  => 'error',
                'message' => 'Security error initializing verification session. Please retry.',
            ], 500);
        }

        $service = new \App\Services\CashfreeSecureIdService();

        // Security Requirement 3: FAIL CLOSED ON DIGILOCKER ACCOUNT CHECK
        $accountCheck = $service->verifyDigiLockerAccount($verificationId, $aadhaar, $phone);

        if (!$accountCheck['success']) {
            Log::warning("DigiLocker Start: Account check failed for phone {$maskedPhone}.", [
                'status'  => $accountCheck['status'] ?? 'FAILED',
                'message' => $accountCheck['message'] ?? 'Gateway error',
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => $accountCheck['message'] ?? 'DigiLocker account check failed. Please try again.',
            ], 422);
        }

        $accountStatus = strtoupper((string) ($accountCheck['status'] ?? ''));
        if ($accountStatus === 'ACCOUNT_EXISTS') {
            $userFlow = 'signin';
        } elseif ($accountStatus === 'ACCOUNT_NOT_FOUND') {
            $userFlow = 'signup';
        } else {
            Log::warning("DigiLocker Start: Unrecognized account status '{$accountStatus}' for phone {$maskedPhone}.");
            return response()->json([
                'status'  => 'error',
                'message' => 'DigiLocker account check returned unrecognized status.',
            ], 422);
        }

        $callbackUrl = route('membership.aadhaar.callback');
        $urlResult = $service->createDigiLockerUrl($verificationId, $callbackUrl, $userFlow);

        if (!$urlResult['success'] || empty($urlResult['url'])) {
            Log::error("DigiLocker Start: Failed to generate redirect URL for Ref {$verificationId}.", [
                'status'  => $urlResult['status'] ?? 'FAILED',
                'message' => $urlResult['message'] ?? 'Gateway error',
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => $urlResult['message'] ?? 'Unable to initialize DigiLocker verification. Please try again later.',
            ], 422);
        }

        // Security Requirement 6 & 7: Store server-side reference ID and started_at timestamp
        session([
            'digilocker_verification_id'   => $verificationId,
            'digilocker_member_id'         => $member->id,
            'digilocker_reference_id'       => $urlResult['reference_id'] ?? null,
            'digilocker_aadhaar_encrypted' => $encryptedAadhaar,
            'digilocker_started_at'        => time(),
        ]);

        Log::info("DigiLocker Start: Successfully generated URL for member ID {$member->id} (Flow: {$userFlow}).");

        return response()->json([
            'status'       => 'redirect',
            'redirect_url' => $urlResult['url'],
            'message'      => 'Redirecting to DigiLocker for Aadhaar verification...',
        ]);
    }

    /**
     * Handle user callback from DigiLocker verification gateway.
     */
    public function handleAadhaarCallback(Request $request)
    {
        // Security Requirement 1: Require verified phone session (no request fallback)
        $phone = session('verified_membership_phone');
        if (!$phone) {
            $this->clearDigiLockerSession();
            return redirect('/membership')->with('error', 'Active membership session not found. Please verify your phone number first.');
        }

        $member = Membership::where('phone', $phone)->first();
        if (!self::hasVerifiedMembershipPayment($member)) {
            $this->clearDigiLockerSession();
            return redirect('/membership/payment')->with('error', 'Membership payment is required before Aadhaar verification.');
        }

        // Security Requirement 2 & 6 & 7: Server session state validation
        $verificationId   = session('digilocker_verification_id');
        $sessionMemberId  = session('digilocker_member_id');
        $referenceId      = session('digilocker_reference_id');
        $encryptedAadhaar = session('digilocker_aadhaar_encrypted');
        $startedAt        = session('digilocker_started_at');

        if (!$verificationId || !$sessionMemberId || !$startedAt || !$encryptedAadhaar) {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Missing server session verification details.");
            return redirect('/membership/application')->with('error', 'DigiLocker verification session expired or invalid. Please try again.');
        }

        // Security Requirement 7: Enforce 15-minute flow expiry window
        if ((time() - (int) $startedAt) > 900) {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Flow session expired for Ref {$verificationId}.");
            return redirect('/membership/application')->with('error', 'Verification Expired: DigiLocker verification session expired. Please restart verification.');
        }

        // Security Requirement 2: Load member strictly using verified phone session AND require session member ID match
        if ((int) $member->id !== (int) $sessionMemberId) {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Session member mismatch.");
            return redirect('/membership/application')->with('error', 'Security Violation: Session member does not match verification request.');
        }

        $service = new \App\Services\CashfreeSecureIdService();
        // Security Requirement 6: Pass stored server-side reference ID
        $statusResult = $service->getDigiLockerStatus($verificationId, $referenceId);

        if (!$statusResult['success']) {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Status check returned failure for Ref {$verificationId}.", [
                'status'  => $statusResult['status'] ?? 'UNKNOWN',
                'message' => $statusResult['message'] ?? 'Gateway error',
            ]);
            return redirect('/membership/application')->with('error', 'Verification Failed: ' . ($statusResult['message'] ?? 'Unable to verify DigiLocker status.'));
        }

        $status = strtoupper((string) ($statusResult['status'] ?? 'UNKNOWN'));

        if ($status === 'PENDING') {
            // Flow still in progress — keep session state
            return redirect('/membership/application')->with('warning', 'Verification Pending: DigiLocker verification is still in progress.');
        }

        if ($status === 'EXPIRED') {
            $this->clearDigiLockerSession();
            return redirect('/membership/application')->with('error', 'Verification Expired: DigiLocker verification session expired. Please try again.');
        }

        if ($status === 'CONSENT_DENIED') {
            $this->clearDigiLockerSession();
            return redirect('/membership/application')->with('error', 'Verification Failed: DigiLocker consent was declined.');
        }

        if ($status !== 'AUTHENTICATED') {
            $this->clearDigiLockerSession();
            return redirect('/membership/application')->with('error', 'Verification Failed: Unrecognized DigiLocker verification status.');
        }

        // Security Requirement 12: Status is explicitly AUTHENTICATED -> fetch verified Aadhaar document server-side
        $docResult = $service->getDigiLockerAadhaarDocument($verificationId, $referenceId);

        if (($docResult['status'] ?? '') === 'AADHAAR_NOT_LINKED') {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Aadhaar document not linked for Ref {$verificationId}.");
            return redirect('/membership/application')->with('error', 'Verification Failed: Aadhaar document is not linked to this DigiLocker account.');
        }

        if (!$docResult['success'] || ($docResult['status'] ?? '') !== 'SUCCESS') {
            $this->clearDigiLockerSession();
            Log::error("DigiLocker Callback: Document fetch failed for Ref {$verificationId}.", [
                'status'  => $docResult['status'] ?? 'FAILED',
                'message' => $docResult['message'] ?? 'Document error',
            ]);
            return redirect('/membership/application')->with('error', 'Verification Failed: ' . ($docResult['message'] ?? 'Could not retrieve verified Aadhaar document.'));
        }

        $verifiedData = $docResult['data'] ?? [];
        $verifiedName = $docResult['verified_name'] ?? ($verifiedData['name'] ?? null);

        if (empty($verifiedName)) {
            $this->clearDigiLockerSession();
            Log::error("DigiLocker Callback: Verified name missing from document payload for Ref {$verificationId}.");
            return redirect('/membership/application')->with('error', 'Verification Failed: Verified Aadhaar name missing from provider records.');
        }

        // Security Requirement 5: Decrypt stored Aadhaar after successful AUTHENTICATED + document SUCCESS
        try {
            $decryptedAadhaar = Crypt::decryptString($encryptedAadhaar);
        } catch (\Throwable $e) {
            $this->clearDigiLockerSession();
            Log::error("DigiLocker Callback: Failed to decrypt session Aadhaar for Ref {$verificationId}.");
            return redirect('/membership/application')->with('error', 'Security Error: Failed to process verification data.');
        }

        // Authoritative server-side persistence of verified identity
        $extraPayload = [
            'aadhaar_number' => $decryptedAadhaar,
        ];

        if (!empty($verifiedData['dob'])) {
            $extraPayload['dob'] = $verifiedData['dob'];
        }
        if (!empty($verifiedData['gender'])) {
            $extraPayload['gender'] = $verifiedData['gender'];
        }
        if (!empty($verifiedData['care_of']) || !empty($verifiedData['father_or_husband_name'])) {
            $extraPayload['father_or_husband_name'] = $verifiedData['care_of'] ?? $verifiedData['father_or_husband_name'];
        }
        if (!empty($verifiedData['permanent_address']) || !empty($verifiedData['address'])) {
            $extraPayload['permanent_address'] = $verifiedData['permanent_address'] ?? $verifiedData['address'];
        }
        if (!empty($verifiedData['pincode'])) {
            $extraPayload['pincode'] = $verifiedData['pincode'];
        }
        if (!empty($verifiedData['district'])) {
            $extraPayload['district'] = $verifiedData['district'];
        }
        if (!empty($verifiedData['state'])) {
            $extraPayload['state'] = $verifiedData['state'];
        }

        try {
            $saveResult = $this->persistVerifiedIdentityIfUnverified(
                $member->id,
                'aadhaar',
                'cashfree',
                $docResult['reference_id'] ?? $referenceId ?? $verificationId,
                $verifiedName,
                substr($decryptedAadhaar, -4),
                $verificationId,
                $extraPayload
            );
        } catch (\Throwable $e) {
            $this->clearDigiLockerSession();
            Log::error("DigiLocker Callback: Database update failed for member ID {$member->id}: " . $e->getMessage());
            return redirect('/membership/application')->with('error', 'Failed to save verified identity data to database.');
        }

        // Security Requirement 8: Clear temporary DigiLocker session state after successful verification
        $this->clearDigiLockerSession();

        if (!$saveResult['persisted']) {
            Log::info("DigiLocker Callback: Member ID {$member->id} already has verified identity; preserved existing.");
            return redirect('/membership/application')->with('warning', 'Identity was already verified with another document.');
        }

        Log::info("DigiLocker Callback: Successfully verified and persisted Aadhaar & Name for member ID {$member->id}.");

        return redirect('/membership/application')->with('success', 'Aadhaar & Name Verified Successfully via DigiLocker!');
    }

    /**
     * AJAX endpoint to check current Aadhaar verification status for the active session member.
     */
    public function checkAadhaarStatus(Request $request)
    {
        // Security Requirement 1: MUST use ONLY session('verified_membership_phone')
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return response()->json([
                'is_verified' => false,
                'message'     => 'No active membership phone session found.',
            ], 401);
        }

        $member = Membership::where('phone', $phone)->first();
        if (!$member) {
            return response()->json([
                'is_verified' => false,
                'message'     => 'Membership record not found.',
            ], 404);
        }

        if (!self::hasVerifiedMembershipPayment($member)) {
            return response()->json([
                'is_verified' => false,
                'message'     => 'Membership payment is required before Aadhaar verification.',
            ], 403);
        }

        // Security Requirement 2: Match digilocker_member_id if present
        $sessionMemberId = session('digilocker_member_id');
        if ($sessionMemberId && (int) $member->id !== (int) $sessionMemberId) {
            return response()->json([
                'is_verified' => false,
                'message'     => 'Session member mismatch.',
            ], 403);
        }

        if ($member->hasVerifiedIdentity()) {
            // Security Requirement 5 & 13: Never return full Aadhaar in JSON
            $maskedAadhaar = $member->aadhaar_number ? ('XXXX-XXXX-' . substr($member->aadhaar_number, -4)) : null;

            return response()->json([
                'is_verified'    => true,
                'verified_name'  => $member->identity_verified_name ?? $member->full_name,
                'masked_aadhaar' => $maskedAadhaar,
                'data'           => array_filter([
                    'full_name'              => $member->identity_verified_name ?? $member->full_name,
                    'dob'                    => $member->dob,
                    'gender'                 => $member->gender,
                    'father_or_husband_name' => $member->father_or_husband_name,
                    'permanent_address'      => $member->permanent_address,
                    'pincode'                => $member->pincode,
                    'district'               => $member->district,
                    'state'                  => $member->state,
                ], fn($v) => !is_null($v)),
                'message'        => 'Identity Verified Successfully!',
            ]);
        }

        return response()->json([
            'is_verified'   => false,
            'verified_name' => null,
            'message'       => 'Identity not verified yet.',
        ]);
    }

    /**
     * Final Membership Form Submission Desk (Strict Verification Guard Enforced)
     */
    public function submitApplication(Request $request)
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Unauthenticated session.'], 401);
            }
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $memberRecord = Membership::where('phone', $phone)->first();

        if (!self::hasValidMembershipAccess($memberRecord)) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Membership payment not verified.'], 403);
            }
            return redirect('/membership/payment')->with('error', 'Please complete membership payment before submitting the application.');
        }

        // Security Guard: Gating on verified identity
        if (!self::hasVerifiedMembershipIdentity($memberRecord)) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Identity verification is required before submitting your membership application.'], 422);
            }
            return redirect('/membership/identity')->with('error', 'Identity verification is required before submitting your application.');
        }

        // Strict Pre-submission Validation
        $request->validate([
            'aadhaar_number'         => 'nullable|string|size:12',
            'full_name'              => 'nullable|string|max:255',
            'gender'                 => 'required|in:Male,Female,Other',
            'dob'                    => 'required|string|max:20',
            'father_or_husband_name' => 'required|string|max:255',
            'gotram'                 => 'required|string|max:255',
            'occupation'             => 'required|string|max:255',
            'blood_group'            => 'nullable|string|max:10',
            'permanent_address'      => 'nullable|string|max:1000',
            'present_address'        => 'nullable|string|max:1000',
            'pincode'                => 'required|string|max:10',
            'grama_panchayat'        => 'required|string|max:255',
            'mandal'                 => 'required|string|max:255',
            'district'               => 'required|string|max:255',
            'state'                  => 'required|string|max:255',
            'email'                  => 'nullable|email|max:255',
            'photo'                  => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('member_photos', 'public');
        }

        // Security Requirement: PROTECT VERIFIED IDENTITY FIELDS ON SUBMISSION
        // Resolve verified name exclusively from provider-controlled database values — never from browser input!
        if ($memberRecord->identity_verified && !empty($memberRecord->identity_verified_name)) {
            $finalFullName = $memberRecord->identity_verified_name;
        } elseif ($memberRecord->is_aadhaar_verified && !empty($memberRecord->full_name)) {
            $finalFullName = $memberRecord->full_name;
        } else {
            return redirect('/membership/identity')->with('error', 'Identity verification is required before submitting your application.');
        }

        // Security Rule 3: Never store unverified Aadhaar from browser application form.
        // aadhaar_number is ONLY populated if genuine Aadhaar verification occurred.
        $finalAadhaar     = $memberRecord->aadhaar_number;
        $finalDob         = $memberRecord->dob ?: $request->input('dob');
        $finalGender      = $memberRecord->gender ?: $request->input('gender');
        $finalCareOf      = $memberRecord->father_or_husband_name ?: $request->input('father_or_husband_name');
        $finalPermAddress = $memberRecord->permanent_address ?: $request->input('permanent_address');
        $finalPincode     = $memberRecord->pincode ?: $request->input('pincode');
        $finalDistrict    = $memberRecord->district ?: $request->input('district');
        $stateInput       = $request->input('state');
        $finalState       = $memberRecord->state ?: $stateInput;
        $emailInput       = $request->input('email');
        $addressToggle    = $request->input('address_toggle', 'same');
        $presentAddress   = ($addressToggle === 'different' && !empty($request->input('present_address')))
            ? $request->input('present_address')
            : $finalPermAddress;

        // Updating final record fields safely inside the database row tracking system
        $updatePayload = [
            'aadhaar_number'         => $finalAadhaar,
            'full_name'              => $finalFullName,
            'gender'                 => $finalGender,
            'dob'                    => $finalDob,
            'father_or_husband_name' => $finalCareOf,
            'permanent_address'      => $finalPermAddress,
            'present_address'        => $presentAddress,
            'gotram'                 => $request->input('gotram'),
            'occupation'             => $request->input('occupation'),
            'blood_group'            => $request->input('blood_group'),
            'pincode'                => $finalPincode,
            'grama_panchayat'        => $request->input('grama_panchayat'),
            'mandal'                 => $request->input('mandal'),
            'assembly_segment'       => $request->input('assembly_segment'),
            'district'               => $finalDistrict,
            'state'                  => $finalState,
            'email'                  => $emailInput,
            'is_completed'           => 1,
            'updated_at'             => \Carbon\Carbon::now()
        ];

        if ($photoPath) {
            $updatePayload['photo_path'] = $photoPath;
        }

        Membership::where('phone', $phone)->update($updatePayload);
        $memberRecord->refresh();

        // Send membership welcome email outside the database transaction
        $this->sendMembershipWelcomeEmail($memberRecord);

        // STATE LANGUAGE DETECTION LOGIC: Selecting language dynamically based on mapped input state
        $selectedLanguage = 'en'; 
        $lowercaseState = strtolower($stateInput);
        if (str_contains($lowercaseState, 'andhra') || str_contains($lowercaseState, 'telangana')) {
            $selectedLanguage = 'te'; 
        } elseif (str_contains($lowercaseState, 'karnataka')) {
            $selectedLanguage = 'kn'; 
        }

        if (!empty($emailInput)) {
            $mailLogMetrics = [
                'recipient_email' => $emailInput,
                'assigned_language' => $selectedLanguage,
                'status' => 'dispatched'
            ];
            session(['last_email_log' => $mailLogMetrics]);
        }

        // DUAL CHANNELS CONNECTIVITY RESPONSE: Supporting web views and mobile app endpoints simultaneously
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'message' => 'Registration completed successfully.',
                'membership_id' => $memberRecord->membership_id,
                'assigned_language_email' => $selectedLanguage,
                'card_preview_endpoint' => url('/membership/view-card')
            ], 200);
        }

        session(['verified_membership_phone' => $phone]);
        return redirect('/membership/view-card')->with('success', 'Registration completed successfully!');
    }

    /**
     * Dispatch membership welcome email with canonical NotificationLog idempotency claim.
     */
    private function sendMembershipWelcomeEmail(Membership $member): void
    {
        if (empty($member->email) || !filter_var($member->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        // Canonical idempotency claim via NotificationLog::claim
        $idempotencyKey = "membership_welcome:{$member->id}";
        $subject = 'Welcome to ABVHPS – Your Membership ID ' . ($member->membership_id ?? 'MEMBER');
        $message = 'Membership welcome confirmation sent for ID: ' . ($member->membership_id ?? 'MEMBER');

        $claim = \App\Models\NotificationLog::claim(
            $idempotencyKey,
            'membership_welcome',
            Membership::class,
            $member->id,
            'email',
            $member->email,
            $member->phone,
            $subject,
            $message
        );

        if (!$claim) {
            // Already claimed or successfully sent by another process
            return;
        }

        $formattedId = $member->membership_id ? implode(' ', str_split($member->membership_id, 4)) : 'MEMBER';
        $memberName = $member->identity_verified_name ?? $member->full_name;

        $memberData = [
            'member_name'       => $memberName,
            'full_name'         => $memberName,
            'membership_id'     => $member->membership_id,
            'formatted_id'      => $formattedId,
            'registration_date' => $member->created_at ? Carbon::parse($member->created_at)->timezone('Asia/Kolkata')->format('d-M-Y') : now('Asia/Kolkata')->format('d-M-Y'),
            'status'            => 'Active',
            'phone'             => $member->phone,
            'blood_group'       => $member->blood_group ?? 'N/A',
            'locality'          => $member->grama_panchayat ?? ($member->mandal ?? ($member->district ?? 'N/A')),
            'district'          => $member->district ?? 'N/A',
            'state'             => $member->state ?? 'India',
            'photo_path'        => $member->photo_path,
        ];

        $pdfContent = null;
        try {
            $pdf = Pdf::loadView('pdf.membership_card_pdf', compact('memberData'));
            $pdfContent = $pdf->output();
        } catch (\Throwable $e) {
            Log::warning('Membership PDF generation fallback: ' . $e->getMessage());
        }

        try {
            \Illuminate\Support\Facades\Mail::to($member->email)->send(new \App\Mail\MembershipWelcomeMail($memberData, $pdfContent));
            $claim->markSent($subject, $message);

            $member->welcome_email_sent_at = now();
            $member->save();
        } catch (\Throwable $e) {
            Log::error("MembershipWelcomeMail: Send failed for member {$member->id}: " . $e->getMessage());
            $claim->markFailed($e->getMessage());
        }
    }

    // 8. Render ID Card Screen showing mapped database values
    public function viewCard()
    {
        $phone = session('verified_membership_phone');
        if (!$phone) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $member = Membership::where('phone', $phone)->where('is_completed', true)->first();
        if (!$member) {
            return redirect('/membership/application')->with('error', 'Please complete your application form details first.');
        }

        // Formatting 12-digit key code pattern using standard gaps (e.g., 9224 9312 1520)
        $formattedId = implode(' ', str_split($member->membership_id, 4));

        $memberData = [
            'full_name' => $member->full_name,
            'formatted_id' => $formattedId,
            'phone' => $member->phone,
            'dob' => '15-08-1995', // Place-holder metric
            'blood_group' => $member->blood_group ?? 'A+',
            'grama_panchayat' => $member->grama_panchayat,
            'mandal' => $member->mandal,
            'assembly_segment' => $member->assembly_segment,
            'district' => $member->district,
            'state' => $member->state,
            'country' => $member->country ?? 'India',
            'pincode' => $member->pincode,
            'photo_path' => $member->photo_path
        ];

        return view('membership_card_view', compact('memberData'));
    }

    // 9. Central Administrative Panel Ledger Grid View for Approved Members
    public function adminIndex(Request $request)
    {
        // Fetching records from the memberships matrix with search query filtering capabilities
        $searchQuery = $request->input('search');
        
        $membersQuery = Membership::where('is_completed', true)
            ->where('payment_status', 'success');

        if (!empty($searchQuery)) {
            $membersQuery->where(function($query) use ($searchQuery) {
                $query->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('membership_id', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('phone', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('district', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $members = $membersQuery->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.membership_ledger_grid', compact('members', 'searchQuery'));
    }

    // Central Administrative Panel Ledger Grid View for Pending/Incomplete Members (Paid ₹100 but Details Not Yet Submitted)
    public function pendingIndex(Request $request)
    {
        $searchQuery = $request->input('search');

        $membersQuery = Membership::where('payment_status', 'success')
            ->where(function ($query) {
                $query->where('is_completed', false)
                      ->orWhere('is_completed', 0)
                      ->orWhereNull('is_completed');
            });

        if (!empty($searchQuery)) {
            $membersQuery->where(function($query) use ($searchQuery) {
                $query->where('full_name', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('membership_id', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('phone', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('payment_id', 'LIKE', '%' . $searchQuery . '%')
                      ->orWhere('district', 'LIKE', '%' . $searchQuery . '%');
            });
        }

        $members = $membersQuery->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.membership_pending_grid', compact('members', 'searchQuery'));
    }

    // 10. Admin: View Read-Only Member Profile Detail
    public function viewProfile($id)
    {
        $member = Membership::findOrFail($id);
        $formattedId = $member->membership_id ? implode(' ', str_split($member->membership_id, 4)) : 'PENDING';

        return view('admin.membership_profile_view', compact('member', 'formattedId'));
    }

    // 11. Admin: View & Print PVC ID Card by Member ID
    public function downloadIdCard($id)
    {
        $member = Membership::findOrFail($id);
        $formattedId = $member->membership_id ? implode(' ', str_split($member->membership_id, 4)) : 'PENDING';

        $memberData = [
            'full_name' => $member->full_name,
            'formatted_id' => $formattedId,
            'phone' => $member->phone,
            'dob' => '15-08-1995', // Placeholder / standard DOB format
            'blood_group' => $member->blood_group ?? 'A+',
            'grama_panchayat' => $member->grama_panchayat,
            'mandal' => $member->mandal,
            'assembly_segment' => $member->assembly_segment,
            'district' => $member->district,
            'state' => $member->state,
            'country' => $member->country ?? 'India',
            'pincode' => $member->pincode,
            'photo_path' => $member->photo_path
        ];

        return view('membership_card_view', compact('memberData'));
    }

    // 12. Admin: Show Member Edit Form
    public function editProfile($id)
    {
        $member = Membership::findOrFail($id);
        return view('admin.membership_edit', compact('member'));
    }

    // 13. Admin: Update Member Profile Data
    public function updateProfile(Request $request, $id)
    {
        $member = Membership::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'dob' => 'nullable|string|max:20',
            'phone' => 'required|digits:10|unique:memberships,phone,' . $member->id,
            'father_or_husband_name' => 'required|string|max:255',
            'gotram' => 'required|string|max:255',
            'occupation' => 'required|string|max:255',
            'blood_group' => 'nullable|string|max:5',
            'email' => 'nullable|email|max:255',
            'pincode' => 'required|digits:6',
            'grama_panchayat' => 'required|string|max:255',
            'mandal' => 'required|string|max:255',
            'assembly_segment' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string',
            'present_address' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $member->photo_path = $request->file('photo')->store('member_photos', 'public');
        }

        $member->full_name = strtoupper($request->input('full_name'));
        $member->gender = $request->input('gender');
        $member->dob = $request->input('dob');
        $member->phone = $request->input('phone');
        $member->father_or_husband_name = $request->input('father_or_husband_name');
        $member->gotram = $request->input('gotram');
        $member->occupation = $request->input('occupation');
        $member->blood_group = $request->input('blood_group');
        $member->email = $request->input('email');
        $member->pincode = $request->input('pincode');
        $member->grama_panchayat = $request->input('grama_panchayat');
        $member->mandal = $request->input('mandal');
        $member->assembly_segment = $request->input('assembly_segment');
        $member->district = $request->input('district');
        $member->state = $request->input('state');
        $member->country = $request->input('country') ?? ($member->country ?? 'India');
        $member->permanent_address = $request->input('permanent_address');
        $member->present_address = $request->input('present_address');

        $member->save();

        return redirect()
            ->route('admin.membership.ledger')
            ->with('success', '🎉 Membership record for ' . $member->full_name . ' updated successfully.');
    }

    // 14. Admin: Delete Member Record Permanently
    public function deleteProfile($id)
    {
        $member = Membership::findOrFail($id);

        if ($member->photo_path && Storage::disk('public')->exists($member->photo_path)) {
            Storage::disk('public')->delete($member->photo_path);
        }

        $memberName = $member->full_name;
        $member->delete();

        return redirect()
            ->route('admin.membership.ledger')
            ->with('success', '🗑️ Membership record for ' . $memberName . ' permanently deleted from matrix.');
    }
}
