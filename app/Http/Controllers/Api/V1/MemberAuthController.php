<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Http\Resources\Api\V1\MemberProfileResource;
use App\Services\MobileMemberOtpService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\AuditLogger;

class MemberAuthController extends Controller
{
    /**
     * Send OTP to a 10-digit mobile number for member authentication.
     * Uses a generic response to prevent account enumeration.
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|digits:10',
        ]);

        $phone = $request->input('phone');
        $throttleKey = 'api_member_send_otp:' . $phone . '|' . $request->ip();

        // Rate limit: 3 requests per 60 seconds
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many OTP requests. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($throttleKey, 60);

        $result = MobileMemberOtpService::createChallenge($phone);

        AuditLogger::log('API_MEMBER_SEND_OTP', 'Membership', 'Phone:' . substr($phone, -4), [
            'ip'           => $request->ip(),
            'challenge_id' => $result['challenge_id'],
        ], 'Anonymous', 'Phone:' . substr($phone, -4));

        return response()->json([
            'success'      => true,
            'challenge_id' => $result['challenge_id'],
            'message'      => $result['message'],
        ]);
    }

    /**
     * Verify OTP code with challenge ID and issue Sanctum token for authenticated Member.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone'        => 'required|digits:10',
            'challenge_id' => 'required|string|uuid',
            'otp'          => 'required|digits:6',
            'device_name'  => 'required|string|max:100',
        ]);

        $phone = $request->input('phone');
        $challengeId = $request->input('challenge_id');
        $otp = $request->input('otp');
        $deviceName = trim($request->input('device_name'));

        $throttleKey = 'api_member_verify_otp:' . $phone . '|' . $request->ip();

        // Rate limit: 5 attempts per 60 seconds
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => "Too many verification attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        $result = MobileMemberOtpService::verifyChallenge($phone, $challengeId, $otp);

        if (!$result['success']) {
            RateLimiter::hit($throttleKey, 60);

            $status = match ($result['error_code'] ?? '') {
                'MAX_ATTEMPTS_EXCEEDED' => 429,
                'INVALID_OR_EXPIRED_CHALLENGE', 'CHALLENGE_EXPIRED', 'CHALLENGE_ALREADY_USED' => 410,
                'MEMBERSHIP_NOT_FOUND' => 404,
                default => 422,
            };

            AuditLogger::log('API_MEMBER_VERIFY_FAILED', 'Membership', 'Phone:' . substr($phone, -4), [
                'error_code' => $result['error_code'] ?? 'UNKNOWN',
                'ip'         => $request->ip(),
            ], 'Anonymous', 'Phone:' . substr($phone, -4));

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $status);
        }

        RateLimiter::clear($throttleKey);

        /** @var Membership $member */
        $member = $result['membership'];

        // Issue member token with explicit abilities
        $abilities = [
            'mobile',
            'account:member',
            'member:profile',
            'member:card',
        ];

        $tokenResult = $member->createToken($deviceName, $abilities);

        AuditLogger::log('API_MEMBER_LOGIN_SUCCESS', 'Membership', $member->membership_id ?? ('Phone:' . substr($phone, -4)), [
            'device_name'   => $deviceName,
            'membership_id' => $member->membership_id,
        ], 'Membership', $member->membership_id ?? $member->id, $member->id);

        return response()->json([
            'success' => true,
            'data'    => [
                'account_type' => 'member',
                'token'        => $tokenResult->plainTextToken,
                'profile'      => new MemberProfileResource($member),
            ],
            'message' => 'Member verified and authenticated successfully.',
        ]);
    }
}
