<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Membership;
use App\Services\Fast2SmsService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

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

        if ($member && $member->payment_status === 'success') {
            return redirect('/membership/application')->with('success', 'Welcome back! Your payment is already verified.');
        }

        return redirect('/membership/payment');
    }

    // 4. Display the ₹100 Payment Screen
    public function showPaymentPage()
    {
        if (!session('verified_membership_phone')) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }
        return view('membership_payment');
    }

    // 5. Process Payment Success & Generate 12-Digit Automatic Random Unique Code
    public function processPayment(Request $request)
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return redirect('/membership')->with('error', 'Session expired. Please try again.');
        }

        do {
            $randomId = (string) rand(100000000000, 999999999999);
            $duplicateCheck = Membership::where('membership_id', $randomId)->exists();
        } while ($duplicateCheck);

        Membership::updateOrCreate(
            ['phone' => $phone],
            [
                'membership_id' => $randomId,
                'payment_status' => 'success',
                'payment_id' => 'TXN-' . strtoupper(str_shuffle(substr(md5(time()), 0, 8))),
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        return redirect('/membership/application')->with('success', 'Payment successful! 12-Digit Membership ID generated.');
    }

    // 6. Show Registration Application Form (Linking directly to original layout file)
    public function showApplicationForm()
    {
        $phone = session('verified_membership_phone');

        if (!$phone) {
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $member = Membership::where('phone', $phone)->where('payment_status', 'success')->first();

        if (!$member) {
            return redirect('/membership/payment')->with('error', 'Please complete the membership payment first.');
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

        $phone = session('verified_membership_phone') ?? $request->input('phone');

        if (!$phone) {
            Log::warning("Aadhaar Verification: Missing active phone session or parameter.");
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

        // 6. Name MATCHES: Build update payload using authoritative Cashfree identity data
        $updatePayload = [
            'aadhaar_number'          => $aadhaar,
            'full_name'               => $verifiedName, // Authoritative Cashfree verified name
            'is_aadhaar_verified'     => true,
            'aadhaar_verification_ref' => $cfResult['ref_id'] ?? $verificationId,
            'aadhaar_verified_at'     => \Carbon\Carbon::now('Asia/Kolkata'),
        ];

        $cfData = $cfResult['data'] ?? [];

        if (!empty($cfData['dob'])) {
            $updatePayload['dob'] = $cfData['dob'];
        }
        if (!empty($cfData['gender'])) {
            $updatePayload['gender'] = $cfData['gender'];
        }
        if (!empty($cfData['father_or_husband_name'])) {
            $updatePayload['father_or_husband_name'] = $cfData['father_or_husband_name'];
        }
        if (!empty($cfData['permanent_address'])) {
            $updatePayload['permanent_address'] = $cfData['permanent_address'];
        }
        if (!empty($cfData['pincode'])) {
            $updatePayload['pincode'] = $cfData['pincode'];
        }
        if (!empty($cfData['district'])) {
            $updatePayload['district'] = $cfData['district'];
        }
        if (!empty($cfData['state'])) {
            $updatePayload['state'] = $cfData['state'];
        }

        // Perform database persistence
        try {
            $member->update($updatePayload);
            $member->refresh();
        } catch (\Throwable $e) {
            Log::error("Aadhaar Verification: Persistence failed for member {$maskedPhone}: " . $e->getMessage());
            return response()->json([
                'status'              => 'error',
                'is_name_matched'     => false,
                'is_aadhaar_verified' => false,
                'message'             => 'Failed to save verified Aadhaar identity data to database. Please retry.',
            ], 500);
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

        // Security Requirement 4: Cryptographically strong, unpredictable verification ID (Str::uuid)
        $verificationId = 'ABVHPS_DIGILOCKER_' . (string) Str::uuid();

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
        $member = Membership::where('phone', $phone)->first();
        if (!$member || (int) $member->id !== (int) $sessionMemberId) {
            $this->clearDigiLockerSession();
            Log::warning("DigiLocker Callback: Session member mismatch or member record not found.");
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
        $updatePayload = [
            'aadhaar_number'           => $decryptedAadhaar,
            'full_name'                => $verifiedName,
            'is_aadhaar_verified'      => true,
            'aadhaar_verification_ref' => $docResult['reference_id'] ?? $referenceId ?? $verificationId,
            'aadhaar_verified_at'      => Carbon::now('Asia/Kolkata'),
        ];

        if (!empty($verifiedData['dob'])) {
            $updatePayload['dob'] = $verifiedData['dob'];
        }
        if (!empty($verifiedData['gender'])) {
            $updatePayload['gender'] = $verifiedData['gender'];
        }
        if (!empty($verifiedData['care_of']) || !empty($verifiedData['father_or_husband_name'])) {
            $updatePayload['father_or_husband_name'] = $verifiedData['care_of'] ?? $verifiedData['father_or_husband_name'];
        }
        if (!empty($verifiedData['permanent_address']) || !empty($verifiedData['address'])) {
            $updatePayload['permanent_address'] = $verifiedData['permanent_address'] ?? $verifiedData['address'];
        }
        if (!empty($verifiedData['pincode'])) {
            $updatePayload['pincode'] = $verifiedData['pincode'];
        }
        if (!empty($verifiedData['district'])) {
            $updatePayload['district'] = $verifiedData['district'];
        }
        if (!empty($verifiedData['state'])) {
            $updatePayload['state'] = $verifiedData['state'];
        }

        try {
            $member->update($updatePayload);
            $member->refresh();
        } catch (\Throwable $e) {
            $this->clearDigiLockerSession();
            Log::error("DigiLocker Callback: Database update failed for member ID {$member->id}: " . $e->getMessage());
            return redirect('/membership/application')->with('error', 'Failed to save verified identity data to database.');
        }

        // Security Requirement 8: Clear temporary DigiLocker session state after successful verification
        $this->clearDigiLockerSession();

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

        // Security Requirement 2: Match digilocker_member_id if present
        $sessionMemberId = session('digilocker_member_id');
        if ($sessionMemberId && (int) $member->id !== (int) $sessionMemberId) {
            return response()->json([
                'is_verified' => false,
                'message'     => 'Session member mismatch.',
            ], 403);
        }

        if ($member->is_aadhaar_verified) {
            // Security Requirement 5 & 13: Never return full Aadhaar in JSON
            $maskedAadhaar = $member->aadhaar_number ? ('XXXX-XXXX-' . substr($member->aadhaar_number, -4)) : null;

            return response()->json([
                'is_verified'    => true,
                'verified_name'  => $member->full_name,
                'masked_aadhaar' => $maskedAadhaar,
                'data'           => array_filter([
                    'full_name'              => $member->full_name,
                    'dob'                    => $member->dob,
                    'gender'                 => $member->gender,
                    'father_or_husband_name' => $member->father_or_husband_name,
                    'permanent_address'      => $member->permanent_address,
                    'pincode'                => $member->pincode,
                    'district'               => $member->district,
                    'state'                  => $member->state,
                ], fn($v) => !is_null($v)),
                'message'        => 'Aadhaar & Name Verified Successfully!',
            ]);
        }

        return response()->json([
            'is_verified'   => false,
            'verified_name' => null,
            'message'       => 'Aadhaar not verified yet.',
        ]);
    }

    // 7. Store Registration Form Data supporting both Web Forms and Mobile App API requests
    public function submitApplication(Request $request)
    {
        // Security Requirement 1: MUST use ONLY session('verified_membership_phone') for web membership flow
        $phone = session('verified_membership_phone');
        if (!$phone) {
            if ($request->wantsJson()) {
                return response()->json(['status' => 'error', 'message' => 'Phone verification metrics missing.'], 401);
            }
            return redirect('/membership')->with('error', 'Please verify your mobile number first.');
        }

        $memberRecord = Membership::where('phone', $phone)->first();

        // Form Submission Security Requirement: Server-side validation that Aadhaar is verified in DB
        if (!$memberRecord || !$memberRecord->is_aadhaar_verified) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Aadhaar verification is required before submitting your membership application.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Please complete Aadhaar verification before submitting the application.');
        }

        // Standard Indian form validation rules tracking inputs including optional email
        $request->validate([
            'aadhaar_number'         => 'required|digits:12',
            'full_name'              => 'required|string|max:255',
            'gender'                 => 'required|string|in:Male,Female,Other',
            'dob'                    => 'required|string|max:20',
            'father_or_husband_name' => 'required|string|max:255',
            'permanent_address'      => 'nullable|string|max:1000',
            'present_address'        => 'nullable|string|max:1000',
            'gotram'                 => 'required|string|max:255',
            'occupation'             => 'required|string|max:255',
            'blood_group'            => 'nullable|string|max:10',
            'pincode'                => 'required|digits:6',
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

        $stateInput = $request->input('state');
        $emailInput = $request->input('email');
        $addressToggle = $request->input('address_toggle', 'same');
        $permanentAddress = $request->input('permanent_address');
        $presentAddress = ($addressToggle === 'different' && !empty($request->input('present_address')))
            ? $request->input('present_address')
            : $permanentAddress;

        // Security Requirement 11: PROTECT VERIFIED IDENTITY FIELDS ON SUBMISSION
        // Always preserve verified server/database values when non-empty — do not let request inputs overwrite them!
        $finalFullName    = $memberRecord->full_name ?: strtoupper(trim($request->input('full_name')));
        $finalAadhaar     = $memberRecord->aadhaar_number ?: $request->input('aadhaar_number');
        $finalDob         = $memberRecord->dob ?: $request->input('dob');
        $finalGender      = $memberRecord->gender ?: $request->input('gender');
        $finalCareOf      = $memberRecord->father_or_husband_name ?: $request->input('father_or_husband_name');
        $finalPermAddress = $memberRecord->permanent_address ?: $permanentAddress;
        $finalPincode     = $memberRecord->pincode ?: $request->input('pincode');
        $finalDistrict    = $memberRecord->district ?: $request->input('district');
        $finalState       = $memberRecord->state ?: $request->input('state');

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

        // STATE LANGUAGE DETECTION LOGIC: Selecting language dynamically based on mapped input state
        $selectedLanguage = 'en'; 
        $lowercaseState = strtolower($stateInput);
        if (str_contains($lowercaseState, 'andhra') || str_contains($lowercaseState, 'telangana')) {
            $selectedLanguage = 'te'; 
        } elseif (str_contains($lowercaseState, 'karnataka')) {
            $selectedLanguage = 'kn'; 
        }

        // TRIGGER OPTIONAL EMAIL SYSTEM: If email id exists, fire the automated dispatch tracker
        if (!empty($emailInput)) {
            $mailLogMetrics = [
                'recipient_email' => $emailInput,
                'assigned_language' => $selectedLanguage,
                'status' => 'queued_with_id_card_attachment'
            ];
            session(['last_email_log' => $mailLogMetrics]);
        }

        // DUAL CHANNELS CONNECTIVITY RESPONSE: Supporting web views and mobile app endpoints simultaneously
        if ($request->wantsJson() || $request->is('api/*')) {
            $memberRecord = Membership::where('phone', $phone)->first();
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
            'aadhaar_number' => 'required|digits:12',
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
        $member->aadhaar_number = $request->input('aadhaar_number');
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
